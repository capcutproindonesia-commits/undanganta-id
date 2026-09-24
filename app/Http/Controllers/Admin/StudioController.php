<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomFont;
use App\Models\StudioAsset;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateInstance;
use App\Support\StudioBindingSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudioController extends Controller
{
    public function index(): View
    {
        return view('admin.studio.index', [
            'templates' => StudioTemplate::query()->latest()->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $template = StudioTemplate::create([
            'name' => 'Template Baru',
            'slug' => $this->uniqueSlug('template-baru'),
            'status' => 'draft',
            'min_plan' => 'free',
            'is_customer_editable' => true,
            'created_by' => auth()->id(),
            'canvas' => $this->defaultCanvas(),
            'settings' => [
                'version' => 21,
                'editor' => 'UNDANGANTA Studio V2.2 STEP 12',
            ],
        ]);

        $template->update(['name' => 'Template Baru #' . $template->id]);

        return redirect()->route('admin.studio.edit', $template);
    }

    public function edit(StudioTemplate $template): View
    {
        $previewInstance = StudioTemplateInstance::query()
            ->where('studio_template_id', $template->id)
            ->where('owner_id', auth()->id())
            ->where('status', 'preview')
            ->latest('id')
            ->first();

        return view('admin.studio.editor', [
            'template' => $template,
            'previewInstance' => $previewInstance,
            'assets' => StudioAsset::query()
                ->where(function ($query) use ($template) {
                    $query->whereNull('studio_template_id')
                        ->orWhere('studio_template_id', $template->id);
                })
                ->latest()
                ->get(),
            'fonts' => CustomFont::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, StudioTemplate $template): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('studio_templates', 'slug')->ignore($template->id),
            ],
            'min_plan' => ['required', Rule::in(['free', 'premium', 'pro'])],
            'is_customer_editable' => ['required', 'boolean'],
            'canvas' => ['required', 'array'],
        ]);

        $template->update([
            'name' => trim($data['name']),
            'slug' => $data['slug'],
            'min_plan' => $data['min_plan'],
            'is_customer_editable' => $data['is_customer_editable'],
            'canvas' => $data['canvas'],
            'settings' => array_merge($template->settings ?? [], [
                'version' => 4,
                'editor' => 'UNDANGANTA Studio V1.6',
            ]),
        ]);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
        ]);
    }

    public function duplicate(StudioTemplate $template): RedirectResponse
    {
        $copy = $template->replicate();
        $copy->name = $template->name . ' Copy';
        $copy->slug = $this->uniqueSlug($template->slug . '-copy');
        $copy->status = 'draft';
        $copy->created_by = auth()->id();
        $copy->save();

        return redirect()->route('admin.studio.edit', $copy)
            ->with('ok', 'Template berhasil diduplikasi.');
    }

    public function togglePublish(StudioTemplate $template): RedirectResponse
    {
        $template->update([
            'status' => $template->status === 'published' ? 'draft' : 'published',
        ]);

        return back()->with(
            'ok',
            $template->status === 'published'
                ? 'Template dipublikasikan.'
                : 'Template dikembalikan ke draft.'
        );
    }

    public function destroy(StudioTemplate $template): RedirectResponse
    {
        foreach ($template->assets as $asset) {
            Storage::disk('public')->delete($asset->path);
        }

        $template->delete();

        return redirect()->route('admin.studio.index')
            ->with('ok', 'Template dihapus.');
    }

    public function uploadAsset(Request $request, StudioTemplate $template): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:25600'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $imageExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        $videoExtensions = ['mp4', 'webm'];

        if (!in_array($ext, array_merge($imageExtensions, $videoExtensions), true)) {
            return response()->json([
                'message' => 'Format belum didukung. Gunakan PNG/JPG/WebP/GIF/MP4/WebM.',
            ], 422);
        }

        $type = in_array($ext, $videoExtensions, true) ? 'video' : 'image';
        $size = $file->getSize() ?: 0;
        $hash = hash_file('sha256', $file->getRealPath());

        $existing = StudioAsset::query()
            ->where('studio_template_id', $template->id)
            ->where('type', $type)
            ->where('size', $size)
            ->get()
            ->first(function (StudioAsset $candidate) use ($hash) {
                $storedHash = data_get($candidate->metadata, 'sha256');

                if (is_string($storedHash) && hash_equals($storedHash, $hash)) {
                    return true;
                }

                $candidatePath = Storage::disk('public')->path($candidate->path);

                return is_file($candidatePath)
                    && hash_equals(hash_file('sha256', $candidatePath), $hash);
            });

        if ($existing) {
            $metadata = is_array($existing->metadata) ? $existing->metadata : [];
            if (($metadata['sha256'] ?? null) !== $hash) {
                $metadata['sha256'] = $hash;
                $existing->forceFill(['metadata' => $metadata])->save();
            }

            return response()->json([
                'ok' => true,
                'duplicate' => true,
                'asset' => [
                    'id' => $existing->id,
                    'type' => $existing->type,
                    'name' => $existing->name,
                    'size' => (int) $existing->size,
                    'sha256' => $hash,
                    'url' => route('admin.studio.assets.file', $existing),
                ],
            ]);
        }

        $path = $file->store('studio/assets', 'public');

        $asset = StudioAsset::create([
            'studio_template_id' => $template->id,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $size,
            'metadata' => ['sha256' => $hash],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'duplicate' => false,
            'asset' => [
                'id' => $asset->id,
                'type' => $asset->type,
                'name' => $asset->name,
                'size' => (int) $asset->size,
                'sha256' => $hash,
                'url' => route('admin.studio.assets.file', $asset),
            ],
        ]);
    }


    public function assetFile(StudioAsset $asset)
    {
        $fullPath = Storage::disk('public')->path($asset->path);

        abort_unless(is_file($fullPath), 404);

        return response()->file($fullPath, [
            'Content-Type' => $asset->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function deleteAsset(StudioAsset $asset): JsonResponse
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return response()->json(['ok' => true]);
    }

    public function uploadFont(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'max:15360'],
            'weight' => ['nullable', 'integer', 'min:100', 'max:900'],
            'style' => ['nullable', Rule::in(['normal', 'italic'])],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['woff2', 'woff', 'ttf', 'otf'], true)) {
            return response()->json([
                'message' => 'Format font harus WOFF2, WOFF, TTF, atau OTF.',
            ], 422);
        }

        $baseFamily = Str::slug($data['name'], '-');
        $family = $baseFamily;
        $counter = 2;

        while (CustomFont::where('family', $family)->exists()) {
            $family = $baseFamily . '-' . $counter++;
        }

        $path = $file->store('studio/fonts', 'public');

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
                'url' => route('admin.studio.fonts.file', $font),
                'weight' => $font->weight,
                'style' => $font->style,
            ],
        ]);
    }


    public function fontFile(CustomFont $font)
    {
        $fullPath = Storage::disk('public')->path($font->path);

        abort_unless(is_file($fullPath), 404);

        return response()->file($fullPath, [
            'Content-Type' => $font->mime ?: 'font/woff2',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function deleteFont(CustomFont $font): JsonResponse
    {
        Storage::disk('public')->delete($font->path);
        $font->delete();

        return response()->json(['ok' => true]);
    }


    public function ensurePreviewInstance(StudioTemplate $template): JsonResponse
    {
        $instance = StudioTemplateInstance::query()
            ->where('studio_template_id', $template->id)
            ->where('owner_id', auth()->id())
            ->where('status', 'preview')
            ->latest('id')
            ->first();

        if (!$instance) {
            $instance = StudioTemplateInstance::create([
                'studio_template_id' => $template->id,
                'owner_id' => auth()->id(),
                'status' => 'preview',
                'public_token' => Str::random(48),
                'template_snapshot' => $template->canvas ?: [],
                'content' => $this->emptyCustomerContent(),
                'design_overrides' => [],
                'meta' => [
                    'purpose' => 'admin_customer_preview',
                    'template_version' => data_get($template->canvas, 'version', 4),
                ],
            ]);
        }

        if (!$instance->public_token) {
            $instance->forceFill(['public_token' => Str::random(48)])->save();
        }

        return response()->json([
            'ok' => true,
            'instance' => $this->instancePayload($instance->fresh()),
        ]);
    }

    public function resetPreviewInstance(StudioTemplate $template): JsonResponse
    {
        $instance = StudioTemplateInstance::query()
            ->where('studio_template_id', $template->id)
            ->where('owner_id', auth()->id())
            ->where('status', 'preview')
            ->latest('id')
            ->first();

        if (!$instance) {
            $instance = StudioTemplateInstance::create([
                'studio_template_id' => $template->id,
                'owner_id' => auth()->id(),
                'status' => 'preview',
                'public_token' => Str::random(48),
                'template_snapshot' => $template->canvas ?: [],
                'content' => $this->emptyCustomerContent(),
                'design_overrides' => [],
                'meta' => ['purpose' => 'admin_customer_preview'],
            ]);
        } else {
            $instance->update([
                'template_snapshot' => $template->canvas ?: [],
                'content' => $this->emptyCustomerContent(),
                'design_overrides' => [],
                'meta' => array_merge($instance->meta ?? [], [
                    'purpose' => 'admin_customer_preview',
                    'reset_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        if (!$instance->public_token) {
            $instance->forceFill(['public_token' => Str::random(48)])->save();
        }

        return response()->json([
            'ok' => true,
            'instance' => $this->instancePayload($instance->fresh()),
        ]);
    }

    public function updatePreviewInstance(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        abort_unless(
            $instance->status === 'preview'
            && (int) $instance->owner_id === (int) auth()->id(),
            403
        );

        $allowedText = [
            'groom_name', 'bride_name', 'couple_names', 'event_date',
            'venue_name', 'quote', 'prayer', 'opening_text', 'closing_text',
        ];
        $allowedMedia = array_merge(
            ['groom_photo', 'bride_photo', 'couple_photo'],
            StudioBindingSchema::galleryKeys()
        );

        $data = $request->validate([
            'content' => ['required', 'array'],
            'content.*' => ['nullable'],
        ]);

        $incoming = $data['content'] ?? [];
        $content = $this->emptyCustomerContent();

        foreach ($allowedText as $key) {
            $value = $incoming[$key] ?? null;
            $content[$key] = is_scalar($value) ? mb_substr(trim((string) $value), 0, 5000) : '';
        }

        foreach ($allowedMedia as $key) {
            $value = $incoming[$key] ?? null;

            if (is_array($value)) {
                $assetId = isset($value['asset_id']) ? (int) $value['asset_id'] : null;

                if ($assetId) {
                    $asset = StudioAsset::query()
                        ->whereKey($assetId)
                        ->where('type', 'image')
                        ->where(function ($query) use ($instance) {
                            $query->whereNull('studio_template_id')
                                ->orWhere('studio_template_id', $instance->studio_template_id);
                        })
                        ->first();

                    $content[$key] = $asset
                        ? [
                            'asset_id' => $asset->id,
                            'url' => route('admin.studio.assets.file', $asset),
                            'name' => $asset->name,
                        ]
                        : null;
                }
            }
        }

        $instance->update(['content' => $content]);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
            'instance' => $this->instancePayload($instance->fresh()),
        ]);
    }

    private function emptyCustomerContent(): array
    {
        return [
            'groom_name' => '',
            'bride_name' => '',
            'couple_names' => '',
            'event_date' => '',
            'venue_name' => '',
            'venue_address' => '',
            'maps_url' => '',
            'quote' => '',
            'prayer' => '',
            'opening_text' => '',
            'closing_text' => '',
            'story' => '',
            'music_url' => '',
            'gift_bank' => '',
            'gift_number' => '',
            'gift_name' => '',
            'groom_photo' => null,
            'bride_photo' => null,
            'couple_photo' => null,
            'gallery' => [],
            ...array_fill_keys(StudioBindingSchema::galleryKeys(), null),
            'opening_cover_media' => null,
            'desktop_cover_media' => null,
        ];
    }

    private function instancePayload(StudioTemplateInstance $instance): array
    {
        return [
            'id' => $instance->id,
            'template_id' => $instance->studio_template_id,
            'status' => $instance->status,
            'public_token' => $instance->public_token,
            'public_url' => $instance->public_token ? route('studio.public.show', $instance->public_token) : null,
            'content' => array_merge($this->emptyCustomerContent(), $instance->content ?? []),
            'design_overrides' => $instance->design_overrides ?? [],
            'updated_at' => optional($instance->updated_at)->toIso8601String(),
        ];
    }

    private function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'template';
        $slug = $base;
        $counter = 2;

        while (StudioTemplate::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function defaultCanvas(): array
    {
        return [
            'version' => 4,
            'width' => 390,
            'height' => 844,
            'settings' => [
                'desktopLayout' => 'cover-left',
                'desktopCoverWidth' => 56,
                'mobileBreakpoint' => 768,
            ],
            'desktopCover' => [
                'image' => '',
                'mediaType' => 'image',
                'fit' => 'cover',
                'scale' => 1,
                'posX' => 0,
                'posY' => 0,
                'background' => '#0f172a',
            ],
            'openingCover' => [
                'enabled' => true,
                'eyebrow' => 'The Wedding of',
                'names' => 'Nama & Nama',
                'bindNames' => true,
                'buttonText' => 'Buka Undangan',
                'textColor' => '#ffffff',
                'buttonColor' => '#2F6FED',
                'nameSize' => 46,
                'posX' => 50,
                'posY' => 50,
                'animation' => 'fade-up',
                'duration' => 0.8,
            ],
            'pages' => [
                [
                    'id' => 'canvas-' . Str::lower(Str::random(6)),
                    'name' => 'Hero Mobile',
                    'role' => 'hero',
                    'width' => 390,
                    'height' => 844,
                    'background' => '#ffffff',
                    'transition' => ['type' => 'fade', 'duration' => 0.8, 'easing' => 'ease-in-out'],
                    'layers' => [
                        [
                            'id' => 'title-' . Str::lower(Str::random(6)),
                            'type' => 'text',
                            'name' => 'Nama Pengantin',
                            'text' => 'Nama & Nama',
                            'x' => 45, 'y' => 330, 'width' => 300, 'height' => 86,
                            'rotation' => 0, 'opacity' => 1, 'zIndex' => 10,
                            'fontFamily' => 'Georgia', 'fontSize' => 48, 'fontWeight' => 400,
                            'fontStyle' => 'normal', 'color' => '#2d2926', 'textAlign' => 'center',
                            'background' => 'transparent', 'borderRadius' => 0,
                            'locked' => false, 'hidden' => false,
                            'animation' => 'fade-up', 'duration' => 0.8, 'delay' => 0, 'loop' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
    public function customerEdit(StudioTemplateInstance $instance): View
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);

        $template = $instance->template;
        abort_unless($template, 404);

        if (!$instance->public_token) {
            $instance->forceFill(['public_token' => Str::random(48)])->save();
        }

        return view('studio.customer-editor', [
            'instance' => $instance,
            'template' => $template,
            'snapshot' => $instance->template_snapshot ?: ($template->canvas ?: []),
            'content' => array_merge($this->emptyCustomerContent(), $instance->content ?? []),
            'designOverrides' => $instance->design_overrides ?? [],
        ]);
    }

    public function customerUpdate(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);

        $template = $instance->template;
        abort_unless($template, 404);

        $data = $request->validate([
            'content' => ['required', 'array'],
            'content.*' => ['nullable'],
            'design_overrides' => ['nullable', 'array'],
        ]);

        $allowedText = [
            'groom_name', 'bride_name', 'couple_names', 'event_date',
            'venue_name', 'venue_address', 'maps_url', 'quote', 'prayer',
            'opening_text', 'closing_text', 'story', 'music_url',
            'gift_bank', 'gift_number', 'gift_name',
        ];

        $content = array_merge($this->emptyCustomerContent(), $instance->content ?? []);

        foreach ($allowedText as $key) {
            $value = data_get($data, 'content.' . $key);
            if ($value !== null) {
                $content[$key] = is_scalar($value)
                    ? mb_substr(trim((string) $value), 0, 5000)
                    : '';
            }
        }

        $overrides = [];

        if ($template->is_customer_editable) {
            $snapshot = $instance->template_snapshot ?: ($template->canvas ?: []);
            $policies = [];

            foreach (($snapshot['pages'] ?? []) as $page) {
                foreach (($page['layers'] ?? []) as $layer) {
                    $id = (string) ($layer['id'] ?? '');
                    if ($id !== '') {
                        $policies[$id] = $layer['customerEditPolicy'] ?? 'full';
                    }
                }
            }

            foreach (($data['design_overrides'] ?? []) as $layerId => $incoming) {
                if (!is_array($incoming) || ($policies[$layerId] ?? 'locked') !== 'full') {
                    continue;
                }

                $clean = [];

                if (isset($incoming['color']) && is_string($incoming['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $incoming['color'])) {
                    $clean['color'] = $incoming['color'];
                }

                if (isset($incoming['background']) && is_string($incoming['background']) && preg_match('/^#[0-9a-fA-F]{6}$/', $incoming['background'])) {
                    $clean['background'] = $incoming['background'];
                }

                if (isset($incoming['fontSize']) && is_numeric($incoming['fontSize'])) {
                    $clean['fontSize'] = max(8, min(120, (float) $incoming['fontSize']));
                }

                if (isset($incoming['opacity']) && is_numeric($incoming['opacity'])) {
                    $clean['opacity'] = max(0.05, min(1, (float) $incoming['opacity']));
                }

                if (isset($incoming['textAlign']) && in_array($incoming['textAlign'], ['left', 'center', 'right'], true)) {
                    $clean['textAlign'] = $incoming['textAlign'];
                }

                if ($clean !== []) {
                    $overrides[$layerId] = $clean;
                }
            }
        }

        $instance->update([
            'content' => $content,
            'design_overrides' => $overrides,
            'meta' => array_merge($instance->meta ?? [], [
                'customer_editor_updated_at' => now()->toIso8601String(),
                'customer_editor_version' => '2.4',
            ]),
        ]);

        $this->syncCustomerContentToInvitation($instance->fresh(), $content);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->format('H:i:s'),
            'instance' => $this->instancePayload($instance->fresh()),
        ]);
    }

    public function customerMediaUpload(Request $request, StudioTemplateInstance $instance): JsonResponse
    {
        abort_unless((int) $instance->owner_id === (int) auth()->id(), 403);

        $data = $request->validate([
            'key' => ['required', Rule::in(array_merge(
                ['groom_photo', 'bride_photo', 'couple_photo'],
                StudioBindingSchema::galleryKeys(),
                ['opening_cover_media', 'desktop_cover_media'],
            ))],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ]);

        $invitationId = (int) ($instance->invitation_id ?? 0);
        $folder = $invitationId > 0
            ? "invitations/{$invitationId}/studio"
            : "studio/customer/{$instance->id}";

        $path = $request->file('file')->store($folder, 'public');
        $value = [
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
        ];

        $content = array_merge($this->emptyCustomerContent(), $instance->content ?? []);
        $content[$data['key']] = $value;

        if (preg_match('/^gallery_(\d+)$/', $data['key'], $match)) {
            $index = max(0, ((int) $match[1]) - 1);
            $gallery = is_array($content['gallery'] ?? null)
                ? array_values($content['gallery'])
                : [];

            if (count($gallery) <= $index) {
                $gallery = array_pad($gallery, $index + 1, null);
            }

            $gallery[$index] = $value;
            $content['gallery'] = $gallery;
        }

        $instance->update([
            'content' => $content,
            'meta' => array_merge($instance->meta ?? [], [
                'customer_media_updated_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->syncCustomerContentToInvitation($instance->fresh(), $content);

        return response()->json([
            'ok' => true,
            'key' => $data['key'],
            'media' => $value,
            'content' => $content,
        ]);
    }

    private function syncCustomerContentToInvitation(StudioTemplateInstance $instance, array $content): void
    {
        if (!$instance->invitation_id) {
            return;
        }

        $invitation = \App\Models\Invitation::query()->find($instance->invitation_id);

        if (!$invitation || (int) $invitation->user_id !== (int) $instance->owner_id) {
            return;
        }

        $update = [
            'groom_name' => $content['groom_name'] ?: $invitation->groom_name,
            'bride_name' => $content['bride_name'] ?: $invitation->bride_name,
            'venue_name' => $content['venue_name'] ?: $invitation->venue_name,
            'venue_address' => $content['venue_address'] ?: null,
            'maps_url' => $content['maps_url'] ?: null,
            'quote' => $content['quote'] ?: null,
            'story' => $content['story'] ?: null,
            'music_url' => $content['music_url'] ?: null,
        ];

        if (!empty($content['event_date'])) {
            $update['event_date'] = $content['event_date'];
        }

        if (!empty($content['groom_photo']['path'])) {
            $update['groom_photo_path'] = $content['groom_photo']['path'];
        }

        if (!empty($content['bride_photo']['path'])) {
            $update['bride_photo_path'] = $content['bride_photo']['path'];
        }

        $gallerySlots = array_fill(0, StudioBindingSchema::galleryCapacity(), null);

        if (is_array($content['gallery'] ?? null)) {
            foreach (array_slice($content['gallery'], 0, StudioBindingSchema::galleryCapacity()) as $index => $item) {
                if (is_array($item) && !empty($item['path'])) {
                    $gallerySlots[$index] = $item['path'];
                }
            }
        }

        foreach (StudioBindingSchema::galleryKeys() as $index => $key) {
            if (!empty($content[$key]['path'])) {
                $gallerySlots[$index] = $content[$key]['path'];
            }
        }

        $gallery = array_values(array_filter(
            $gallerySlots,
            static fn ($path) => is_string($path) && $path !== ''
        ));

        if ($gallery !== []) {
            $update['gallery'] = $gallery;
        }

        if (
            ($content['gift_bank'] ?? '') !== ''
            || ($content['gift_number'] ?? '') !== ''
            || ($content['gift_name'] ?? '') !== ''
        ) {
            $update['gift_accounts'] = [[
                'bank' => $content['gift_bank'] ?? '',
                'number' => $content['gift_number'] ?? '',
                'name' => $content['gift_name'] ?? '',
            ]];
        }

        $sections = $invitation->sections ?? [];
        $defaults = ['countdown', 'quote', 'groom', 'bride', 'story', 'gallery', 'event', 'rsvp', 'gift', 'guest_photo', 'wishes', 'music'];
        if (!isset($sections['order']) || !is_array($sections['order'])) {
            $sections['order'] = $defaults;
        }
        foreach ($defaults as $section) {
            if (!array_key_exists($section, $sections)) {
                $sections[$section] = true;
            }
        }
        $update['sections'] = $sections;

        $invitation->forceFill($update)->save();
    }


}
