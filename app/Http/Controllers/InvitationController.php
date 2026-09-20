<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Plan;
use App\Support\ThemeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request
            ->user()
            ->invitations()
            ->with([
                'latestOrder.plan',
            ])
            ->latest();

        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('groom_name', 'like', "%{$search}%")
                    ->orWhere('bride_name', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'published') {
            $query->where('is_published', true);
        }

        if ($request->status === 'draft') {
            $query
                ->where('is_published', false)
                ->where('plan', '!=', 'pending');
        }

        if ($request->status === 'payment') {
            $query->where('plan', 'pending');
        }

        return view('invitations.index', [
            'invitations' => $query->get(),
        ]);
    }

    public function create()
    {
        return view('invitations.plans', [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price')
                ->get(),
            'themeAccess' => collect(config('themes.plans', []))
                ->mapWithKeys(fn ($rank, $code) => [
                    $code => ThemeCatalog::allowedForPlan($code),
                ])
                ->all(),
        ]);
    }

    public function themes(Request $request)
    {
        $plan = $this->selectedPlan(
            (string) $request->query('plan')
        );

        return view('invitations.themes', [
            'plan' => $plan,
            'themes' => ThemeCatalog::active(),
            'allowedThemes' => ThemeCatalog::allowedForPlan($plan->code),
        ]);
    }

    public function initial(Request $request)
    {
        $plan = $this->selectedPlan(
            (string) $request->query('plan')
        );

        $theme = strtolower(
            (string) $request->query('theme')
        );

        if (!$this->themeAllowed($plan->code, $theme)) {
            return redirect()
                ->route('invitations.themes', ['plan' => $plan->code])
                ->withErrors([
                    'theme' => 'Tema ini tidak tersedia untuk paket yang dipilih.',
                ]);
        }

        return view('invitations.initial', [
            'plan' => $plan,
            'theme' => $theme,
            'themeMeta' => ThemeCatalog::all()[$theme],
        ]);
    }

    public function previewTheme(
        Request $request,
        string $theme
    ) {
        $plan = $this->selectedPlan(
            (string) $request->query('plan')
        );

        abort_unless(
            array_key_exists($theme, ThemeCatalog::all()),
            404
        );

        $sections = [
            'order' => [
                'countdown', 'quote', 'groom', 'bride', 'story',
                'gallery', 'event', 'rsvp', 'gift', 'guest_photo',
                'wishes',
            ],
            'countdown' => true,
            'quote' => true,
            'groom' => true,
            'bride' => true,
            'story' => true,
            'gallery' => true,
            'event' => true,
            'rsvp' => true,
            'gift' => true,
            'guest_photo' => true,
            'wishes' => true,
        ];

        $invitation = new Invitation([
            'title' => 'Preview Undangan',
            'slug' => 'preview-theme',
            'groom_name' => 'Arga',
            'groom_parent_text' => 'Putra dari Bapak & Ibu',
            'bride_name' => 'Nara',
            'bride_parent_text' => 'Putri dari Bapak & Ibu',
            'event_date' => now()->addDays(90),
            'venue_name' => 'The Grand Ballroom',
            'venue_address' => 'Makassar, Sulawesi Selatan',
            'theme' => $theme,
            'quote' => 'Dan di antara tanda-tanda kebesaran-Nya, Dia menciptakan pasangan untukmu.',
            'story' => 'Sebuah pertemuan sederhana tumbuh menjadi perjalanan yang ingin kami rayakan bersama orang-orang terkasih.',
            'gallery' => [],
            'gift_accounts' => $plan->code === 'free' ? [] : [[
                'bank' => 'BANK',
                'number' => '0000000000',
                'name' => 'Arga & Nara',
            ]],
            'sections' => $sections,
            'is_published' => false,
            'plan' => $plan->code,
        ]);

        $invitation->id = 0;
        $invitation->exists = true;

        return view('public.invitation', [
            'invitation' => $invitation,
            'guest' => null,
            'wishes' => collect(),
            'photos' => collect(),
            'previewMode' => true,
            'previewPlan' => $plan->code,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'selected_plan' => ['required', 'string', 'max:30'],
            'theme' => ['required', Rule::in(ThemeCatalog::activeCodes())],
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('invitations', 'slug'),
            ],
            'groom_name' => ['required', 'string', 'max:100'],
            'bride_name' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'venue_name' => ['required', 'string', 'max:160'],
            'venue_address' => ['nullable', 'string', 'max:500'],
        ]);

        $plan = $this->selectedPlan($data['selected_plan']);

        abort_unless(
            $this->themeAllowed($plan->code, $data['theme']),
            422,
            'Tema ini tidak tersedia untuk paket yang dipilih.'
        );

        $slug = $data['slug']
            ?: Str::slug($data['title']) . '-' . Str::lower(Str::random(5));

        $sectionOrder = [
            'countdown', 'quote', 'groom', 'bride', 'story', 'gallery',
            'event', 'rsvp', 'gift', 'guest_photo', 'wishes',
        ];

        $invitation = $request
            ->user()
            ->invitations()
            ->create([
                'title' => $data['title'],
                'slug' => $slug,
                'groom_name' => $data['groom_name'],
                'bride_name' => $data['bride_name'],
                'event_date' => $data['event_date'],
                'venue_name' => $data['venue_name'],
                'venue_address' => $data['venue_address'] ?? null,
                'theme' => $data['theme'],
                'plan' => $plan->code === 'free' ? 'free' : 'pending',
                'gallery' => [],
                'gift_accounts' => [],
                'sections' => array_merge(
                    ['order' => $sectionOrder],
                    array_fill_keys($sectionOrder, true)
                ),
                'is_published' => false,
            ]);

        if ($plan->code === 'free') {
            return redirect()
                ->route('invitations.edit', $invitation)
                ->with('ok', 'Undangan gratis aktif. Lengkapi konten sebelum publish.');
        }

        $request->user()->orders()->create([
            'invitation_id' => $invitation->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'draft',
            'payment_method' => null,
            'payment_proof' => null,
        ]);

        return redirect()
            ->route('orders.checkout', $invitation)
            ->with('ok', 'Data awal tersimpan. Selesaikan pembayaran untuk mengaktifkan editor.');
    }

    public function edit(Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        if ($invitation->plan === 'pending') {
            return redirect()
                ->route('orders.checkout', $invitation)
                ->with('ok', 'Selesaikan pembayaran dan tunggu verifikasi sebelum membuka editor.');
        }

        return view('invitations.form', [
            'invitation' => $invitation,
            // Package changes are handled by admin after checkout. This also
            // hides the legacy purchase block from the content editor.
            'plans' => collect(),
        ]);
    }

    public function update(
        Request $request,
        Invitation $invitation
    ) {
        $this->authorize('update', $invitation);

        abort_if(
            $invitation->plan === 'pending',
            403,
            'Undangan belum aktif.'
        );

        // The editor never receives a mutable theme control. Supply the
        // stored theme server-side so validation cannot be bypassed by
        // changing or omitting client HTML.
        $request->merge([
            'theme' => $invitation->theme ?: 'modern',
        ]);

        $data = $this->validatedData(
            $request,
            $invitation
        );

        // Theme is selected before checkout and cannot be changed from the
        // client editor. Admin changes remain a separate workflow.
        $data['theme'] = $invitation->theme ?: 'modern';

        // Published personalized links must remain stable after distribution.
        if ($invitation->is_published) {
            $data['slug'] = $invitation->slug;
        }

        $data['sections'] = $this->sections($request);
        $data['gift_accounts'] = $this->giftAccounts($request);

        if (
            !in_array(
                strtolower((string) $invitation->plan),
                ['premium', 'pro'],
                true
            )
        ) {
            $data['gift_accounts'] = [];
            $data['music_url'] = null;
        }

        $data['groom_photo_x'] = (int) ($data['groom_photo_x'] ?? 50);
        $data['groom_photo_y'] = (int) ($data['groom_photo_y'] ?? 50);
        $data['bride_photo_x'] = (int) ($data['bride_photo_x'] ?? 50);
        $data['bride_photo_y'] = (int) ($data['bride_photo_y'] ?? 50);

        /*
        |--------------------------------------------------------------------------
        | REORDER EXISTING GALLERY
        |--------------------------------------------------------------------------
        */

        if ($request->filled('gallery_order')) {
            $requestedOrder = json_decode(
                $request->gallery_order,
                true
            );

            if (is_array($requestedOrder)) {
                $existing = $this->normalizeGallery(
                    $invitation->gallery ?? []
                );

                $ordered = [];

                foreach ($requestedOrder as $path) {
                    if (
                        is_string($path)
                        &&
                        in_array($path, $existing, true)
                        &&
                        !in_array($path, $ordered, true)
                    ) {
                        $ordered[] = $path;
                    }
                }

                foreach ($existing as $path) {
                    if (!in_array($path, $ordered, true)) {
                        $ordered[] = $path;
                    }
                }

                $data['gallery'] = array_values($ordered);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE NEW COUPLE PHOTOS DIRECTLY INTO UPDATE DATA
        |--------------------------------------------------------------------------
        */

        $disk = Storage::disk('public');

        if ($request->hasFile('groom_photo')) {
            if ($invitation->groom_photo_path) {
                $disk->delete($invitation->groom_photo_path);
            }

            $data['groom_photo_path'] = $request
                ->file('groom_photo')
                ->store(
                    "invitations/{$invitation->id}/couple",
                    'public'
                );
        }

        if ($request->hasFile('bride_photo')) {
            if ($invitation->bride_photo_path) {
                $disk->delete($invitation->bride_photo_path);
            }

            $data['bride_photo_path'] = $request
                ->file('bride_photo')
                ->store(
                    "invitations/{$invitation->id}/couple",
                    'public'
                );
        }

        $invitation->update($data);

        /*
        |--------------------------------------------------------------------------
        | COVER + NEW GALLERY FILES ONLY
        |--------------------------------------------------------------------------
        */

        $this->storeMedia(
            $request,
            $invitation,
            false
        );

        return redirect()
            ->route(
                'invitations.edit',
                $invitation
            )
            ->with(
                'ok',
                'Perubahan berhasil disimpan.'
            );
    }

    public function destroy(
        Invitation $invitation
    ) {
        $this->authorize('delete', $invitation);

        Storage::disk('public')
            ->deleteDirectory(
                "invitations/{$invitation->id}"
            );

        $invitation->delete();

        return redirect()
            ->route('invitations.index')
            ->with(
                'ok',
                'Undangan dan medianya berhasil dihapus.'
            );
    }

    public function publish(
        Invitation $invitation
    ) {
        $this->authorize('update', $invitation);

        if ($invitation->plan === 'pending') {
            return back()->withErrors([
                'publish' => 'Undangan belum aktif. Selesaikan pembayaran dan tunggu verifikasi admin.',
            ]);
        }

        $invitation->update([
            'is_published' => !$invitation->is_published,
        ]);

        return back()->with(
            'ok',
            $invitation->is_published
                ? 'Undangan dipublikasikan.'
                : 'Undangan dijadikan draft.'
        );
    }

    public function deleteCover(
        Invitation $invitation
    ) {
        $this->authorize('update', $invitation);

        if ($invitation->cover_path) {
            Storage::disk('public')
                ->delete($invitation->cover_path);
        }

        $invitation->update([
            'cover_path' => null,
        ]);

        return back()->with(
            'ok',
            'Cover berhasil dihapus.'
        );
    }

    public function deleteGallery(
        Invitation $invitation,
        int $index
    ) {
        $this->authorize('update', $invitation);

        $gallery = $this->normalizeGallery(
            $invitation->gallery ?? []
        );

        if (!array_key_exists($index, $gallery)) {
            abort(404);
        }

        $path = $gallery[$index];

        if ($path) {
            Storage::disk('public')
                ->delete($path);
        }

        unset($gallery[$index]);

        $invitation->update([
            'gallery' => array_values($gallery),
        ]);

        return back()->with(
            'ok',
            'Foto galeri berhasil dihapus.'
        );
    }

    private function validatedData(
        Request $request,
        ?Invitation $invitation = null
    ): array {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:160',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique(
                    'invitations',
                    'slug'
                )->ignore($invitation?->id),
            ],

            'groom_name' => [
                'required',
                'string',
                'max:100',
            ],

            'groom_parent_text' => [
                'nullable',
                'string',
                'max:300',
            ],

            'groom_origin' => [
                'nullable',
                'string',
                'max:150',
            ],

            'groom_instagram' => [
                'nullable',
                'string',
                'max:100',
            ],

            'groom_photo' => [
                'nullable',
                'file',
                'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],

            'groom_photo_x' => [
                'nullable',
                'integer',
                'between:0,100',
            ],

            'groom_photo_y' => [
                'nullable',
                'integer',
                'between:0,100',
            ],

            'bride_name' => [
                'required',
                'string',
                'max:100',
            ],

            'bride_parent_text' => [
                'nullable',
                'string',
                'max:300',
            ],

            'bride_origin' => [
                'nullable',
                'string',
                'max:150',
            ],

            'bride_instagram' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bride_photo' => [
                'nullable',
                'file',
                'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],

            'bride_photo_x' => [
                'nullable',
                'integer',
                'between:0,100',
            ],

            'bride_photo_y' => [
                'nullable',
                'integer',
                'between:0,100',
            ],

            'event_date' => [
                'required',
                'date',
            ],

            'venue_name' => [
                'required',
                'string',
                'max:160',
            ],

            'venue_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'maps_url' => [
                'nullable',
                'url',
                'max:1000',
            ],

            'theme' => [
                'required',
                Rule::in(ThemeCatalog::codes()),
            ],

            'music_url' => [
                'nullable',
                'url',
                'max:1000',
            ],

            'quote' => [
                'nullable',
                'string',
                'max:500',
            ],

            'story' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cover' => [
                'nullable',
                'file',
                'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],

            'gallery' => [
                'nullable',
                'array',
                'max:30',
            ],

            'gallery.*' => [
                'file',
                'max:12288',
                'mimes:jpg,jpeg,png,webp,heic,heif',
            ],

            'gift_bank' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gift_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gift_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gallery_order' => [
                'nullable',
                'string',
            ],

            'section_order' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }

    private function selectedPlan(string $code): Plan
    {
        return Plan::query()
            ->where('code', strtolower(trim($code)))
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function themeAllowed(
        string $planCode,
        string $theme
    ): bool {
        return ThemeCatalog::allows($planCode, $theme);
    }

    private function sections(
        Request $request
    ): array {
        $available = [
            'countdown',
            'quote',
            'groom',
            'bride',
            'story',
            'gallery',
            'event',
            'rsvp',
            'gift',
            'guest_photo',
            'wishes',
        ];

        $requestedOrder = json_decode(
            $request->input(
                'section_order',
                '[]'
            ),
            true
        );

        $order = [];

        if (is_array($requestedOrder)) {
            foreach ($requestedOrder as $key) {
                if (
                    is_string($key)
                    &&
                    in_array($key, $available, true)
                    &&
                    !in_array($key, $order, true)
                ) {
                    $order[] = $key;
                }
            }
        }

        foreach ($available as $key) {
            if (!in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        $sections = [
            'order' => $order,
        ];

        foreach ($available as $key) {
            $sections[$key] = $request->boolean(
                'section_' . $key
            );
        }

        return $sections;
    }

    private function giftAccounts(
        Request $request
    ): array {
        if (!$request->filled('gift_bank')) {
            return [];
        }

        return [
            [
                'bank' => $request->gift_bank,
                'number' => $request->gift_number,
                'name' => $request->gift_name,
            ],
        ];
    }

    private function storeMedia(
        Request $request,
        Invitation $invitation,
        bool $storeCouplePhotos = true
    ): void {
        $disk = Storage::disk('public');

        /*
        |--------------------------------------------------------------------------
        | COVER
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover')) {
            if ($invitation->cover_path) {
                $disk->delete($invitation->cover_path);
            }

            $invitation->cover_path = $request
                ->file('cover')
                ->store(
                    "invitations/{$invitation->id}/cover",
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | COUPLE PHOTOS — CREATE ONLY
        |--------------------------------------------------------------------------
        */

        if (
            $storeCouplePhotos
            &&
            $request->hasFile('groom_photo')
        ) {
            if ($invitation->groom_photo_path) {
                $disk->delete($invitation->groom_photo_path);
            }

            $invitation->groom_photo_path = $request
                ->file('groom_photo')
                ->store(
                    "invitations/{$invitation->id}/couple",
                    'public'
                );
        }

        if (
            $storeCouplePhotos
            &&
            $request->hasFile('bride_photo')
        ) {
            if ($invitation->bride_photo_path) {
                $disk->delete($invitation->bride_photo_path);
            }

            $invitation->bride_photo_path = $request
                ->file('bride_photo')
                ->store(
                    "invitations/{$invitation->id}/couple",
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | GALLERY
        |--------------------------------------------------------------------------
        */

        $gallery = $this->normalizeGallery(
            $invitation->gallery ?? []
        );

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store(
                    "invitations/{$invitation->id}/gallery",
                    'public'
                );
            }

            $invitation->gallery =
                array_values($gallery);
        }

        $invitation->save();
    }

    private function normalizeGallery(
        array $gallery
    ): array {
        $normalized = [];

        foreach ($gallery as $item) {
            $path = null;

            if (is_string($item)) {
                $path = $item;
            } elseif (is_array($item)) {
                $path =
                    $item['path']
                    ?? $item['url']
                    ?? $item['file']
                    ?? null;
            } elseif (is_object($item)) {
                $path =
                    $item->path
                    ?? $item->url
                    ?? $item->file
                    ?? null;
            }

            if (
                is_string($path)
                &&
                trim($path) !== ''
            ) {
                $normalized[] = $path;
            }
        }

        return array_values(
            array_unique($normalized)
        );
    }
}
