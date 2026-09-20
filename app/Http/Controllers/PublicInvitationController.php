<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PublicInvitationController extends Controller
{
    public function show(
        Request $request,
        string $slug
    ) {
        $invitation = Invitation::where(
            'slug',
            $slug
        )
            ->where(
                'is_published',
                true
            )
            ->firstOrFail();

        $invitation->increment(
            'views_count'
        );

        $guest = null;

        if ($request->filled('g')) {
            $guest = $invitation
                ->guests()
                ->where(
                    'token',
                    $request->g
                )
                ->first();
        }

        $guestSessionKey = "invitation_guest_tokens.{$invitation->id}";

        if ($guest) {
            $request->session()->put(
                $guestSessionKey,
                $guest->token
            );
        } else {
            $request->session()->forget($guestSessionKey);
        }

        /*
        |--------------------------------------------------------------------------
        | WISHES
        |--------------------------------------------------------------------------
        | Moderation dinonaktifkan. Semua ucapan yang sudah tersimpan langsung
        | ditampilkan.
        */

        $wishes = $invitation
            ->wishes()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GUEST PHOTOS
        |--------------------------------------------------------------------------
        | Moderasi foto tamu tetap dipertahankan.
        */

        $photos = $invitation
            ->photos()
            ->where(function ($query) {
                $query
                    ->where('status', 'approved')
                    ->orWhere('is_approved', true);
            })
            ->latest()
            ->get();

        $hasPremiumFeatures = in_array(
            strtolower((string) $invitation->plan),
            ['premium', 'pro'],
            true
        );

        if (!$hasPremiumFeatures) {
            $invitation->setAttribute('gift_accounts', []);
            $invitation->setAttribute('music_url', null);
        }

        return view(
            'public.invitation',
            [
                'invitation' => $invitation,
                'guest' => $guest,
                'wishes' => $wishes,
                'photos' => $photos,
            ]
        );
    }

    public function rsvp(
        Request $request,
        Invitation $invitation
    ) {
        abort_unless(
            $invitation->is_published,
            404
        );

        $data = $request->validate([
            'token' => [
                'nullable',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'status' => [
                'required',
                'in:hadir,tidak_hadir,ragu',
            ],

            'party_size' => [
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],

            'message' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $guest = null;
        $token = $this->guestToken(
            $request,
            $invitation,
            $data['token'] ?? null
        );

        if ($token !== null) {
            $guest = $invitation
                ->guests()
                ->where(
                    'token',
                    $token
                )
                ->first();

            if (!$guest) {
                throw ValidationException::withMessages([
                    'token' => 'Link tamu tidak valid untuk undangan ini.',
                ]);
            }
        }

        if ($guest) {
            $guest->rsvp_status = $data['status'];
            $guest->party_size = $data['status'] === 'hadir'
                ? ($data['party_size'] ?? 1)
                : 0;
            $guest->save();
        }

        $guestName = $guest?->name ?? trim($data['name']);

        /*
        |--------------------------------------------------------------------------
        | DIRECT-PUBLISH WISH
        |--------------------------------------------------------------------------
        | Tidak ada antrian moderasi. Begitu tamu menekan kirim, ucapan langsung
        | dianggap approved dan tampil di undangan.
        */

        $message = trim(
            (string) (
                $data['message']
                ?? ''
            )
        );

        if ($message !== '') {
            Wish::create([
                'invitation_id' => $invitation->id,
                'guest_name' => $guestName,
                'message' => $message,
                'is_approved' => true,
            ]);
        }

        return redirect()
            ->route('public.invitation', array_filter([
                'slug' => $invitation->slug,
                'g' => $guest?->token,
            ]))
            ->with(
                'ok',
                'Konfirmasi kehadiran dan ucapan berhasil dikirim.'
            );
    }

    public function photo(
        Request $request,
        Invitation $invitation
    ) {
        abort_unless(
            $invitation->is_published,
            404
        );

        if (
            !in_array(
                strtolower(
                    (string) $invitation->plan
                ),
                [
                    'premium',
                    'pro',
                ],
                true
            )
        ) {
            abort(403);
        }

        $data = $request->validate([
            'token' => [
                'nullable',
                'string',
                'max:255',
            ],

            'guest_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'photo' => [
                'required',
                'file',
                'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],
        ]);

        $guest = null;
        $token = $this->guestToken(
            $request,
            $invitation,
            $data['token'] ?? null
        );

        if ($token !== null) {
            $guest = $invitation
                ->guests()
                ->where('token', $token)
                ->first();

            if (!$guest) {
                throw ValidationException::withMessages([
                    'token' => 'Link tamu tidak valid untuk undangan ini.',
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

        return back()->with(
            'ok',
            'Foto berhasil dikirim dan menunggu persetujuan.'
        );
    }

    public function checkin(
        Invitation $invitation,
        Guest $guest
    ) {
        abort_unless(
            $guest->invitation_id
                ===
                $invitation->id,
            404
        );

        $alreadyCheckedIn = $guest->checked_in_at !== null;

        if (!$alreadyCheckedIn) {
            $guest->update([
                'checked_in_at' => now(),
            ]);
        }

        return view(
            'public.checkin',
            [
                'invitation' => $invitation,
                'guest' => $guest->fresh(),
                'alreadyCheckedIn' => $alreadyCheckedIn,
            ]
        );
    }

    private function guestToken(
        Request $request,
        Invitation $invitation,
        mixed $submittedToken
    ): ?string {
        $token = trim((string) $submittedToken);

        if ($token !== '') {
            return $token;
        }

        $referer = $request->headers->get('referer');

        if (
            is_string($referer)
            && $referer !== ''
            && parse_url($referer, PHP_URL_HOST) === $request->getHost()
        ) {
            $queryString = parse_url($referer, PHP_URL_QUERY);

            if (is_string($queryString) && $queryString !== '') {
                parse_str($queryString, $query);
                $token = trim((string) ($query['g'] ?? ''));

                if ($token !== '') {
                    return $token;
                }
            }
        }

        $sessionToken = $request->session()->get(
            "invitation_guest_tokens.{$invitation->id}"
        );

        $token = trim((string) $sessionToken);

        return $token !== '' ? $token : null;
    }
}