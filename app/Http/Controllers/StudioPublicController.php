<?php

namespace App\Http\Controllers;

use App\Models\CustomFont;
use App\Models\Invitation;
use App\Models\GuestPhoto;
use App\Models\Wish;
use App\Models\StudioAsset;
use App\Models\StudioTemplateInstance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudioPublicController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $instance = StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        $template = $instance->template;
        abort_unless($template, 404);

        if ($instance->invitation_id && $instance->status !== 'published') {
            abort_unless(
                auth()->check()
                && (int) auth()->id() === (int) $instance->owner_id,
                404
            );
        }

        $production = null;
        $guest = null;
        $wishes = collect();
        $photos = collect();

        if ($instance->invitation_id) {
            $invitation = Invitation::query()
                ->findOrFail($instance->invitation_id);

            if ($request->filled('g')) {
                $guest = $invitation
                    ->guests()
                    ->where('token', $request->query('g'))
                    ->first();
            }

            $wishes = $invitation
                ->wishes()
                ->latest()
                ->limit(20)
                ->get();

            $photos = $invitation
                ->photos()
                ->where(function ($query) {
                    $query
                        ->where('status', 'approved')
                        ->orWhere('is_approved', true);
                })
                ->latest()
                ->limit(12)
                ->get();

            $premium = in_array(
                strtolower((string) $invitation->plan),
                ['premium', 'pro'],
                true
            );

            $production = [
                'invitation_id' => $invitation->id,
                'slug' => $invitation->slug,
                'is_published' => (bool) $invitation->is_published,
                'plan' => (string) $invitation->plan,
                'event_date' => $invitation->event_date?->toIso8601String(),
                'venue_name' => (string) ($invitation->venue_name ?? ''),
                'venue_address' => (string) ($invitation->venue_address ?? ''),
                'maps_url' => (string) ($invitation->maps_url ?? ''),
                'music_url' => $premium ? (string) ($invitation->music_url ?? '') : '',
                'gift_accounts' => $premium ? ($invitation->gift_accounts ?? []) : [],
                'sections' => $invitation->sections ?? [],
                'guest' => $guest ? [
                    'name' => $guest->name,
                    'token' => $guest->token,
                    'rsvp_status' => $guest->rsvp_status,
                    'party_size' => $guest->party_size,
                ] : null,
                'wishes' => $wishes->map(fn ($wish) => [
                    'name' => $wish->guest_name,
                    'message' => $wish->message,
                ])->values()->all(),
                'photos' => $photos->map(fn ($photo) => [
                    'url' => url('/storage/' . $photo->path),
                    'caption' => $photo->caption,
                    'guest_name' => $photo->guest_name,
                ])->values()->all(),
            ];
        }

        return view('studio.public-renderer', [
            'instance' => $instance,
            'template' => $template,
            'snapshot' => $instance->template_snapshot ?: ($template->canvas ?: []),
            'content' => $this->withMediaUrls(
                $instance,
                $instance->content ?? [],
                $token
            ),
            'designOverrides' => $instance->design_overrides ?? [],
            'token' => $token,
            'production' => $production,
        ]);
    }

    public function rsvp(
        Request $request,
        string $token
    ): RedirectResponse {
        $instance = StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless(
            $instance->invitation_id
            && $instance->status === 'published',
            404
        );

        $invitation = Invitation::query()
            ->whereKey($instance->invitation_id)
            ->where('is_published', true)
            ->firstOrFail();

        $data = $request->validate([
            'guest_token' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:hadir,tidak_hadir,ragu'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $guest = null;

        if (!empty($data['guest_token'])) {
            $guest = $invitation
                ->guests()
                ->where('token', $data['guest_token'])
                ->first();

            if (!$guest) {
                throw ValidationException::withMessages([
                    'guest_token' => 'Link tamu tidak valid.',
                ]);
            }

            $guest->rsvp_status = $data['status'];
            $guest->party_size = $data['status'] === 'hadir'
                ? ($data['party_size'] ?? 1)
                : 0;
            $guest->save();
        }

        $guestName = $guest?->name ?? trim($data['name']);
        $message = trim((string) ($data['message'] ?? ''));

        if ($message !== '') {
            Wish::create([
                'invitation_id' => $invitation->id,
                'guest_name' => $guestName,
                'message' => $message,
                'is_approved' => true,
            ]);
        }

        return redirect()
            ->route('studio.public.show', array_filter([
                'token' => $token,
                'g' => $guest?->token,
            ]))
            ->with('ok', 'Konfirmasi kehadiran berhasil dikirim.');
    }

    public function photo(
        Request $request,
        string $token
    ): RedirectResponse {
        $instance = StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless(
            $instance->invitation_id
            && $instance->status === 'published',
            404
        );

        $invitation = Invitation::query()
            ->whereKey($instance->invitation_id)
            ->where('is_published', true)
            ->firstOrFail();

        abort_unless(
            in_array(
                strtolower((string) $invitation->plan),
                ['premium', 'pro'],
                true
            ),
            403
        );

        $data = $request->validate([
            'guest_token' => ['nullable', 'string', 'max:255'],
            'guest_name' => ['nullable', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:255'],
            'photo' => [
                'required', 'file', 'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],
        ]);

        $guest = null;

        if (!empty($data['guest_token'])) {
            $guest = $invitation
                ->guests()
                ->where('token', $data['guest_token'])
                ->first();

            if (!$guest) {
                throw ValidationException::withMessages([
                    'guest_token' => 'Link tamu tidak valid.',
                ]);
            }
        }

        $guestName = $guest?->name
            ?? trim((string) ($data['guest_name'] ?? ''));

        if ($guestName === '') {
            throw ValidationException::withMessages([
                'guest_name' => 'Nama tamu wajib diisi.',
            ]);
        }

        $path = $request
            ->file('photo')
            ->store(
                "invitations/{$invitation->id}/guest-photos",
                'public'
            );

        GuestPhoto::create([
            'invitation_id' => $invitation->id,
            'guest_name' => $guestName,
            'path' => $path,
            'caption' => $data['caption'] ?? null,
            'status' => 'pending',
            'is_approved' => false,
        ]);

        return redirect()
            ->route('studio.public.show', array_filter([
                'token' => $token,
                'g' => $guest?->token,
            ]))
            ->with('ok', 'Foto berhasil dikirim dan menunggu persetujuan.');
    }

    public function media(
        string $token,
        string $key
    ): Response {
        $allowed = [
            'groom_photo', 'bride_photo', 'couple_photo',
            'gallery_1', 'gallery_2', 'gallery_3',
            'opening_cover_media', 'desktop_cover_media',
        ];

        abort_unless(in_array($key, $allowed, true), 404);

        $instance = StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        $value = data_get($instance->content ?? [], $key);
        abort_unless(is_array($value) && !empty($value['path']), 404);

        $path = ltrim((string) $value['path'], '/');
        abort_unless(Storage::disk('public')->exists($path), 404);

        $mime = Storage::disk('public')->mimeType($path)
            ?: 'application/octet-stream';

        return response(
            Storage::disk('public')->get($path),
            200,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function withMediaUrls(
        StudioTemplateInstance $instance,
        array $content,
        string $token
    ): array {
        foreach ([
            'groom_photo', 'bride_photo', 'couple_photo',
            'gallery_1', 'gallery_2', 'gallery_3',
            'opening_cover_media', 'desktop_cover_media',
        ] as $key) {
            $value = $content[$key] ?? null;

            if (!is_array($value) || empty($value['path'])) {
                continue;
            }

            $value['url'] = route('studio.public.media', [
                'token' => $token,
                'key' => $key,
            ]);
            $content[$key] = $value;
        }

        return $content;
    }

    public function asset(string $token, StudioAsset $asset): Response
    {
        $instance = StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless(
            $asset->studio_template_id === null
            || (int) $asset->studio_template_id === (int) $instance->studio_template_id,
            404
        );

        abort_unless(Storage::disk('public')->exists($asset->path), 404);

        return response(
            Storage::disk('public')->get($asset->path),
            200,
            [
                'Content-Type' => $asset->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    public function font(string $token, CustomFont $font): Response
    {
        StudioTemplateInstance::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($font->is_active, 404);
        abort_unless(Storage::disk('public')->exists($font->path), 404);

        return response(
            Storage::disk('public')->get($font->path),
            200,
            [
                'Content-Type' => $font->mime_type ?: 'font/woff2',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
