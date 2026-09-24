<?php

namespace App\Http\Controllers;

use App\Models\CustomFont;
use App\Models\Invitation;
use App\Models\StudioAsset;
use App\Models\StudioTemplateInstance;
use App\Support\PlanCapabilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudioCustomerController extends Controller
{
    public function edit(StudioTemplateInstance $instance): View
    {
        $this->own($instance);

        $invitation = Invitation::query()->findOrFail($instance->invitation_id);
        abort_if($invitation->plan === 'pending', 403);

        if (!$instance->public_token) {
            $instance->forceFill(['public_token' => Str::random(48)])->save();
        }

        if (PlanCapabilities::fullEditor((string) $invitation->plan)) {
            return view('studio.full-editor', [
                'editorMode' => 'instance',
                'template' => $instance->template,
                'instance' => $instance,
                'invitation' => $invitation,
                'previewInstance' => null,
                'assets' => StudioAsset::query()
                    ->where(function ($query) use ($instance) {
                        // Keep customer assets persistent, but return real
                        // StudioAsset models because full-editor.blade.php
                        // expects object properties such as $asset->id.
                        $query->where(function ($customerAssets) {
                            $customerAssets
                                ->whereNull('studio_template_id')
                                ->where('created_by', auth()->id());
                        });

                        if ($instance->studio_template_id) {
                            $query->orWhere(
                                'studio_template_id',
                                $instance->studio_template_id
                            );
                        }
                    })
                    ->latest()
                    ->get(),
                'fonts' => CustomFont::query()
                    ->where('is_active', true)
                    ->orderBy('name')->get(),
            ]);
        }

        $template = $instance->template;
        abort_unless($template, 404);

        return view('studio.customer-editor', [
            'instance' => $instance,
            'template' => $template,
            'snapshot' => $instance->template_snapshot ?: ($template->canvas ?: []),
            'content' => array_merge($this->emptyContent(), $instance->content ?? []),
            'designOverrides' => $instance->design_overrides ?? [],
        ]);
    }

    public function quickUpdate(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        $this->own($instance);
        $invitation = Invitation::query()->findOrFail($instance->invitation_id);
        abort_unless(!PlanCapabilities::fullEditor((string) $invitation->plan), 403);

        $data = $request->validate([
            'content' => ['required', 'array'],
            'content.*' => ['nullable'],
            'design_overrides' => ['nullable', 'array'],
        ]);

        $content = array_merge($this->emptyContent(), $instance->content ?? []);
        foreach (array_keys($this->emptyContent()) as $key) {
            $value = data_get($data, 'content.' . $key);
            if ($value !== null && !str_contains($key, '_photo') && !str_starts_with($key, 'gallery_') && !str_ends_with($key, '_media')) {
                $content[$key] = is_scalar($value) ? mb_substr(trim((string) $value), 0, 5000) : '';
            }
        }

        $instance->update([
            'content' => $content,
            'design_overrides' => [],
            'meta' => array_merge($instance->meta ?? [], [
                'customer_editor_mode' => 'basic_quick',
                'customer_editor_updated_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->syncInvitation($instance->fresh(), $content);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
            'content' => $instance->fresh()->content ?? [],
            'instance' => [
                'id' => $instance->id,
                'content' => $instance->fresh()->content ?? [],
                'design_overrides' => $instance->fresh()->design_overrides ?? [],
            ],
        ]);
    }

    public function fullUpdate(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        $this->own($instance);
        $invitation = Invitation::query()->findOrFail($instance->invitation_id);
        abort_unless(PlanCapabilities::fullEditor((string) $invitation->plan), 403);

        /* UNDANGANTA_PREMIUM_GALLERY_V2 */
        $data = $request->validate([
            'canvas' => ['sometimes', 'array'],
            'content' => ['sometimes', 'array'],
            'content.*' => ['nullable'],
            'content.event_date' => ['nullable', 'date_format:Y-m-d'],
            'gallery_asset_ids' => ['sometimes', 'array', 'max:30'],
            'gallery_asset_ids.*' => ['integer', 'min:1'],
        ]);

        $content = array_merge(
            ['gallery' => []],
            $instance->content ?? []
        );

        
        /* UNDANGANTA_STUDIO_CUMULATIVE_REPAIR_V1_5_1_SAFE_CONTENT */
        if (array_key_exists('content', $data) && is_array($data['content'])) {
            $incomingContent = $data['content'];

            $textKeys = [
                'groom_name','bride_name','couple_names','event_date',
                'venue_name','venue_address','maps_url','quote','prayer',
                'opening_text','closing_text','story','music_url',
                'gift_bank','gift_number','gift_name',
            ];

            foreach ($textKeys as $key) {
                if (!array_key_exists($key, $incomingContent)) {
                    continue;
                }
                $value = $incomingContent[$key];
                $content[$key] = is_scalar($value)
                    ? mb_substr(trim((string) $value), 0, 5000)
                    : '';
            }

            foreach (['opening_cover_media','desktop_cover_media'] as $key) {
                if (!array_key_exists($key, $incomingContent)) {
                    continue;
                }

                $value = $incomingContent[$key];
                if ($value === null || $value === '') {
                    $content[$key] = null;
                    continue;
                }

                $assetId = is_array($value) ? (int) ($value['asset_id'] ?? 0) : 0;
                if ($assetId <= 0) {
                    $content[$key] = null;
                    continue;
                }

                $asset = StudioAsset::query()
                    ->whereKey($assetId)
                    ->whereIn('type', ['image', 'video'])
                    ->where(function ($query) use ($instance) {
                        $query->where(function ($owned) use ($instance) {
                            $owned->whereNull('studio_template_id')
                                ->where('created_by', $instance->owner_id);
                        })
                            ->orWhere('studio_template_id', $instance->studio_template_id);
                    })
                    ->first();

                if (!$asset) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "content.{$key}" => ['Foto tidak tersedia lagi di Media. Pilih ulang foto yang masih tersedia.'],
                    ]);
                }

                $content[$key] = [
                    'asset_id' => $asset->id,
                    'url' => route('studio.public.asset', [
                        'token' => $instance->public_token,
                        'asset' => $asset->id,
                    ]),
                    'path' => $asset->path,
                    'name' => $asset->name,
                ];
            }
        }
/* UNDANGANTA_DATA_BINDING_CORE_V1 */
        $incomingContent = array_key_exists('content', $data)
            && is_array($data['content'])
                ? $data['content']
                : null;

        if ($incomingContent !== null) {
            foreach (\App\Support\StudioBindingSchema::textKeys() as $key) {
                if (!array_key_exists($key, $incomingContent)) {
                    continue;
                }

                $value = $incomingContent[$key];
                $content[$key] = is_scalar($value)
                    ? mb_substr(trim((string) $value), 0, 5000)
                    : '';
            }

            if (
                array_key_exists('groom_name', $incomingContent)
                || array_key_exists('bride_name', $incomingContent)
            ) {
                $content['couple_names'] = trim(
                    trim((string) ($content['groom_name'] ?? ''))
                    . ' & '
                    . trim((string) ($content['bride_name'] ?? '')),
                    " &\t\n\r\0\x0B"
                );
            }

            foreach (\App\Support\StudioBindingSchema::directMediaKeys() as $key) {
                if (!array_key_exists($key, $incomingContent)) {
                    continue;
                }

                $raw = $incomingContent[$key];

                if ($raw === null || $raw === '' || $raw === []) {
                    $content[$key] = null;
                    continue;
                }

                $assetId = is_array($raw)
                    ? (int) ($raw['asset_id'] ?? $raw['assetId'] ?? $raw['id'] ?? 0)
                    : 0;

                if ($assetId <= 0) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Foto binding tidak valid.',
                    ], 422);
                }

                $asset = StudioAsset::query()
                    ->whereKey($assetId)
                    ->where('type', 'image')
                    ->where(function ($query) use ($instance) {
                        $query
                            ->where(function ($owned) use ($instance) {
                                $owned
                                    ->whereNull('studio_template_id')
                                    ->where('created_by', $instance->owner_id);
                            })
                            ->orWhere('studio_template_id', $instance->studio_template_id);
                    })
                    ->first();

                if (!$asset) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Foto binding tidak tersedia untuk undangan ini.',
                    ], 422);
                }

                $content[$key] = [
                    'asset_id' => $asset->id,
                    'url' => route('studio.public.asset', [
                        'token' => $instance->public_token,
                        'asset' => $asset->id,
                    ]),
                    'path' => $asset->path,
                    'name' => $asset->name,
                ];
            }
        }

        $galleryPayload = null;

        if (array_key_exists('gallery_asset_ids', $data)) {
            $requestedIds = array_values(array_unique(array_map(
                static fn ($id) => (int) $id,
                $data['gallery_asset_ids'] ?? []
            )));

            $assetMap = StudioAsset::query()
                ->whereIn('id', $requestedIds)
                ->where('type', 'image')
                ->where(function ($query) use ($instance) {
                    $query->where(function ($owned) use ($instance) {
                        $owned->whereNull('studio_template_id')
                            ->where('created_by', $instance->owner_id);
                    })
                        ->orWhere('studio_template_id', $instance->studio_template_id);
                })
                ->get()
                ->keyBy('id');

            if (count($requestedIds) !== $assetMap->count()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Satu atau lebih foto Galeri tidak valid atau tidak tersedia.',
                ], 422);
            }

            $galleryPayload = [];

            foreach ($requestedIds as $assetId) {
                $asset = $assetMap->get($assetId);

                $galleryPayload[] = [
                    'asset_id' => $asset->id,
                    'url' => route('studio.public.asset', [
                        'token' => $instance->public_token,
                        'asset' => $asset->id,
                    ]),
                    'path' => $asset->path,
                    'name' => $asset->name,
                ];
            }

            $content['gallery'] = $galleryPayload;

            $galleryCapacity = class_exists(\App\Support\StudioBindingSchema::class)
                ? \App\Support\StudioBindingSchema::galleryCapacity()
                : 30;

            for ($index = 1; $index <= $galleryCapacity; $index++) {
                $content['gallery_' . $index] = $galleryPayload[$index - 1] ?? null;
            }
        }

        $instanceUpdate = [
            'meta' => array_merge($instance->meta ?? [], [
                'customer_editor_mode' => 'full_studio',
                'customer_editor_updated_at' => now()->toIso8601String(),
            ]),
        ];

        if (array_key_exists('canvas', $data)) {
            $instanceUpdate['template_snapshot'] = $data['canvas'];
            $instanceUpdate['design_overrides'] = [];
        }

        if ($galleryPayload !== null || $incomingContent !== null) {
            $instanceUpdate['content'] = $content;
        }

        DB::transaction(function () use ($instance, $instanceUpdate, $incomingContent, $invitation, $content, $galleryPayload): void {
            $instance->update($instanceUpdate);

            if ($incomingContent !== null) {
                $this->syncInvitationFromFullStudio($invitation, $content, $incomingContent);
            }

            if ($galleryPayload !== null) {
                $invitationGallery = array_values(array_filter(array_map(
                    static fn (array $item) => $item['path'] ?? null,
                    $galleryPayload
                )));

                $invitation->update(['gallery' => $invitationGallery]);
            }
        });

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
            'gallery' => $galleryPayload ?? ($instance->fresh()->content['gallery'] ?? []),
            'content' => $instance->fresh()->content ?? [],
        ]);
    }

    public function mediaUpload(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        $this->own($instance);

        $data = $request->validate([
            'key' => ['required', Rule::in([
                'groom_photo', 'bride_photo', 'couple_photo',
                'gallery_1', 'gallery_2', 'gallery_3',
                'opening_cover_media', 'desktop_cover_media',
            ])],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ]);

        $path = $request->file('file')->store(
            "invitations/{$instance->invitation_id}/studio",
            'public'
        );

        $value = [
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
        ];

        $content = array_merge($this->emptyContent(), $instance->content ?? []);
        $content[$data['key']] = $value;
        $instance->update(['content' => $content]);
        $this->syncInvitation($instance->fresh(), $content);

        return response()->json(['ok' => true, 'media' => $value, 'content' => $content]);
    }

    public function assetUpload(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        $this->own($instance);
        $invitation = Invitation::query()->findOrFail($instance->invitation_id);
        abort_unless(PlanCapabilities::fullEditor((string) $invitation->plan), 403);

        $request->validate(['file' => ['required', 'file', 'max:25600']]);
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $images = ['png','jpg','jpeg','webp','gif'];
        $videos = ['mp4','webm'];

        abort_unless(in_array($ext, array_merge($images, $videos), true), 422);

        $type = in_array($ext, $videos, true) ? 'video' : 'image';
        $path = $file->store(
            "invitations/{$instance->invitation_id}/studio/assets",
            'public'
        );

        $asset = StudioAsset::create([
            // Customer media is intentionally not attached to the master
            // template. It belongs to the customer media library instead.
            'studio_template_id' => null,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'metadata' => [
                'studio_template_instance_id' => $instance->id,
                'invitation_id' => $instance->invitation_id,
                'source' => 'customer_full_studio',
            ],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'asset' => [
                'id' => $asset->id,
                'type' => $asset->type,
                'name' => $asset->name,
                'url' => route('studio.public.asset', [
                    'token' => $instance->public_token,
                    'asset' => $asset->id,
                ]),
            ],
        ]);
    }


    public function fontUpload(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        $this->own($instance);
        $invitation = Invitation::query()->findOrFail($instance->invitation_id);
        abort_unless(PlanCapabilities::fullEditor((string) $invitation->plan), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'max:15360'],
            'weight' => ['nullable', 'integer', 'min:100', 'max:900'],
            'style' => ['nullable', Rule::in(['normal', 'italic'])],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        abort_unless(in_array($ext, ['woff2','woff','ttf','otf'], true), 422);

        $base = Str::slug($data['name'], '-');
        $family = $base;
        $counter = 2;
        while (CustomFont::where('family', $family)->exists()) {
            $family = $base . '-' . $counter++;
        }

        $path = $file->store("invitations/{$instance->invitation_id}/studio/fonts", 'public');
        $font = CustomFont::create([
            'name' => trim($data['name']),
            'family' => $family,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'weight' => $data['weight'] ?? 400,
            'style' => $data['style'] ?? 'normal',
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'font' => [
                'id' => $font->id,
                'name' => $font->name,
                'family' => $font->family,
                'url' => route('studio.public.font', ['token' => $instance->public_token, 'font' => $font->id]),
                'weight' => $font->weight,
                'style' => $font->style,
            ],
        ]);
    }

    private function own(StudioTemplateInstance $instance): void
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);
    }


    /**
     * Full Studio only. Basic quickUpdate keeps its existing sync behavior.
     * Update only invitation-backed fields that were explicitly edited.
     */
    private function syncInvitationFromFullStudio(
        Invitation $invitation,
        array $content,
        array $incoming
    ): void {
        abort_unless(
            (int) $invitation->user_id === (int) auth()->id(),
            403
        );

        $update = [];

        foreach ([
            'groom_name',
            'bride_name',
            'venue_name',
            'venue_address',
            'maps_url',
            'quote',
            'story',
            'music_url',
        ] as $key) {
            if (array_key_exists($key, $incoming)) {
                $update[$key] = (string) ($content[$key] ?? '');
            }
        }

        if (array_key_exists('event_date', $incoming)) {
            $eventDate = trim((string) ($content['event_date'] ?? ''));
            // Invitations require a date. A cleared Studio field remains empty in
            // instance content while the invitation keeps its last valid date.
            if ($eventDate !== '') {
                $update['event_date'] = $eventDate;
            }
        }

        if (
            array_key_exists('gift_bank', $incoming)
            || array_key_exists('gift_number', $incoming)
            || array_key_exists('gift_name', $incoming)
        ) {
            $giftAccounts = is_array($invitation->gift_accounts)
                ? array_values($invitation->gift_accounts)
                : [];

            $first = is_array($giftAccounts[0] ?? null)
                ? $giftAccounts[0]
                : [];

            $first['bank'] = (string) ($content['gift_bank'] ?? '');
            $first['number'] = (string) ($content['gift_number'] ?? '');
            $first['name'] = (string) ($content['gift_name'] ?? '');

            if (
                trim($first['bank']) === ''
                && trim($first['number']) === ''
                && trim($first['name']) === ''
            ) {
                if (isset($giftAccounts[0])) {
                    array_shift($giftAccounts);
                }
            } else {
                if (isset($giftAccounts[0])) {
                    $giftAccounts[0] = $first;
                } else {
                    array_unshift($giftAccounts, $first);
                }
            }

            $update['gift_accounts'] = $giftAccounts;
        }

        if ($update !== []) {
            $invitation->forceFill($update)->save();
        }
    }

    private function emptyContent(): array
    {
        return [
            'groom_name'=>'','bride_name'=>'','couple_names'=>'','event_date'=>'',
            'venue_name'=>'','venue_address'=>'','maps_url'=>'','quote'=>'','prayer'=>'',
            'opening_text'=>'','closing_text'=>'','story'=>'','music_url'=>'',
            'gift_bank'=>'','gift_number'=>'','gift_name'=>'',
            'groom_photo'=>null,'bride_photo'=>null,'couple_photo'=>null,
            'gallery_1'=>null,'gallery_2'=>null,'gallery_3'=>null,
            'opening_cover_media'=>null,'desktop_cover_media'=>null,
        ];
    }

    private function syncInvitation(StudioTemplateInstance $instance, array $content): void
    {
        $invitation = Invitation::query()->find($instance->invitation_id);
        if (!$invitation || (int) $invitation->user_id !== (int) $instance->owner_id) {
            return;
        }

        $update = [];
        foreach (['groom_name','bride_name','venue_name','venue_address','maps_url','quote','story','music_url'] as $key) {
            if (array_key_exists($key, $content)) {
                $value = $content[$key];
                $update[$key] = is_scalar($value) ? trim((string) $value) : '';
            }
        }
        if (array_key_exists('event_date', $content)) {
            $eventDate = trim((string) ($content['event_date'] ?? ''));
            $update['event_date'] = $eventDate !== '' ? $eventDate : null;
        }
        if ($update) {
            $invitation->update($update);
        }
    }
    /* UNDANGANTA_CUSTOMER_MEDIA_ENDPOINTS_V1 */
    public function streamAsset(Request $request, StudioTemplateInstance $instance, StudioAsset $asset)
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);

        abort_unless(
            $asset->studio_template_id === null
            || (int) $asset->studio_template_id === (int) $instance->studio_template_id,
            404
        );

        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($asset->path);
        abort_unless(is_file($fullPath), 404);

        $size = filesize($fullPath);
        $start = 0;
        $end = max(0, $size - 1);
        $status = 200;

        $range = $request->header('Range');
        if (is_string($range) && preg_match('/bytes=(\d*)-(\d*)/i', $range, $m)) {
            if ($m[1] !== '') {
                $start = max(0, (int) $m[1]);
            }
            if ($m[2] !== '') {
                $end = min($end, (int) $m[2]);
            }

            if ($start > $end || $start >= $size) {
                return response('', 416, [
                    'Content-Range' => 'bytes */'.$size,
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $status = 206;
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $asset->mime ?: 'application/octet-stream',
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = 'bytes '.$start.'-'.$end.'/'.$size;
        }

        return response()->stream(function () use ($fullPath, $start, $length) {
            $handle = fopen($fullPath, 'rb');
            if ($handle === false) {
                return;
            }

            try {
                fseek($handle, $start);
                $remaining = $length;

                while ($remaining > 0 && !feof($handle)) {
                    $chunk = fread($handle, min(1024 * 1024, $remaining));
                    if ($chunk === false || $chunk === '') {
                        break;
                    }

                    echo $chunk;
                    $remaining -= strlen($chunk);

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();
                }
            } finally {
                fclose($handle);
            }
        }, $status, $headers);
    }

    public function destroyAsset(StudioTemplateInstance $instance, StudioAsset $asset)
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);

        abort_unless(
            (int) ($asset->created_by ?? 0) === (int) auth()->id(),
            403,
            'Hanya media yang Anda unggah sendiri yang dapat dihapus.'
        );

        abort_unless(
            $asset->studio_template_id === null
            || (int) $asset->studio_template_id === (int) $instance->studio_template_id,
            404
        );

        $snapshot = json_encode([
            $instance->template_snapshot ?? [],
            $instance->content ?? [],
        ], JSON_UNESCAPED_SLASHES);

        /* UNDANGANTA_DESTROY_ASSET_URL_GUARD_V1 */
        $assetIdNeedle = '"assetId":'.$asset->id;
        $assetIdSnakeNeedle = '"asset_id":'.$asset->id;
        $pathNeedle = (string) ($asset->path ?? '');
        $routeNeedle = '/assets/'.$asset->id.'/';

        if (
            (is_string($snapshot) && str_contains($snapshot, $assetIdNeedle))
            || (is_string($snapshot) && str_contains($snapshot, $assetIdSnakeNeedle))
            || ($pathNeedle !== '' && is_string($snapshot) && str_contains($snapshot, $pathNeedle))
            || (is_string($snapshot) && str_contains($snapshot, $routeNeedle))
        ) {
            return response()->json([
                'ok' => false,
                'message' => 'Media masih digunakan di canvas atau Galeri. Hapus dari desain terlebih dahulu.',
            ], 409);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return response()->json(['ok' => true]);
    }

}
