<?php

namespace Tests\Feature;

use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\StudioTemplate;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Plan $premiumPlan;
    private Plan $royalPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->premiumPlan = Plan::query()->updateOrCreate(['code' => 'premium'], [
            'name' => 'Premium',
            'code' => 'premium',
            'price' => 100000,
            'duration_days' => 365,
            'features' => null,
            'is_active' => true,
        ]);

        $this->royalPlan = Plan::query()->updateOrCreate(['code' => 'royal'], [
            'name' => 'Royal',
            'code' => 'royal',
            'price' => 200000,
            'duration_days' => 365,
            'features' => null,
            'is_active' => true,
        ]);
    }

    private function makeStudioTemplate(string $minimumPlan, bool $blank = false): StudioTemplate
    {
        return StudioTemplate::create([
            'name' => 'Studio ' . ucfirst($minimumPlan),
            'slug' => 'test-studio-' . uniqid(),
            'status' => 'published',
            'min_plan' => $minimumPlan,
            'canvas' => ['pages' => []],
            'settings' => $blank ? ['system_blank' => true] : [],
        ]);
    }

    private function studioOrderPayload(string $plan, StudioTemplate $template): array
    {
        return [
            'selected_plan' => $plan,
            'studio_template_id' => $template->id,
            'title' => 'Studio Test',
            'slug' => 'studio-test-' . uniqid(),
            'groom_name' => 'A',
            'bride_name' => 'B',
            'event_date' => now()->addMonth()->format('Y-m-d H:i:s'),
            'venue_name' => 'Makassar',
            'venue_address' => 'Makassar',
        ];
    }

    private function makeInvitation(array $overrides = []): Invitation
    {
        return Invitation::query()->create(array_merge([
            'user_id' => $this->user->id,
            'slug' => 'undangan-' . uniqid(),
            'title' => 'A & B',
            'groom_name' => 'A',
            'bride_name' => 'B',
            'event_date' => now()->addMonth(),
            'venue_name' => 'Makassar',
            'venue_address' => 'Makassar',
            'theme' => 'modern',
            'gallery' => [],
            'gift_accounts' => [],
            'sections' => [],
            'is_published' => true,
            'plan' => 'free',
        ], $overrides));
    }

    public function test_guest_gets_automatic_personalized_token(): void
    {
        $invitation = $this->makeInvitation();

        $guest = $invitation->guests()->create([
            'name' => 'Tamu Test',
        ]);

        $this->assertNotNull($guest->token);
        $this->assertSame(40, strlen($guest->token));
    }

    public function test_existing_guest_token_is_not_overwritten(): void
    {
        $invitation = $this->makeInvitation();

        $guest = $invitation->guests()->create([
            'name' => 'Tamu Manual',
            'token' => 'manual-token-123',
        ]);

        $this->assertSame(
            'manual-token-123',
            $guest->token
        );
    }

    public function test_personalized_guest_token_is_resolved_on_public_invitation(): void
    {
        $invitation = $this->makeInvitation([
            'slug' => 'personal-test',
        ]);

        $guest = $invitation->guests()->create([
            'name' => 'Budi',
        ]);

        $response = $this->get(
            route('public.invitation', [
                'slug' => $invitation->slug,
                'g' => $guest->token,
            ])
        );

        $response->assertOk();

        $response->assertViewHas(
            'guest',
            function ($resolvedGuest) use ($guest) {
                return $resolvedGuest?->id === $guest->id;
            }
        );
    }

    public function test_rsvp_updates_personalized_guest(): void
    {
        $invitation = $this->makeInvitation();

        $guest = $invitation->guests()->create([
            'name' => 'Tamu RSVP',
        ]);

        $response = $this->post(
            route('public.rsvp', $invitation),
            [
                'token' => $guest->token,
                'name' => 'Tamu RSVP',
                'status' => 'hadir',
                'party_size' => 3,
            ]
        );

        $response->assertRedirect();

        $guest->refresh();

        $this->assertSame(
            'hadir',
            $guest->rsvp_status
        );

        $this->assertSame(
            3,
            $guest->party_size
        );
    }

    public function test_non_attending_rsvp_sets_party_size_to_zero(): void
    {
        $invitation = $this->makeInvitation();

        $guest = $invitation->guests()->create([
            'name' => 'Tamu Tidak Hadir',
        ]);

        $this->post(
            route('public.rsvp', $invitation),
            [
                'token' => $guest->token,
                'name' => 'Tamu Tidak Hadir',
                'status' => 'tidak_hadir',
                'party_size' => 5,
            ]
        )->assertRedirect();

        $guest->refresh();

        $this->assertSame(
            'tidak_hadir',
            $guest->rsvp_status
        );

        $this->assertSame(
            0,
            $guest->party_size
        );
    }

    public function test_rsvp_rejects_token_from_other_invitation(): void
    {
        $invitationA = $this->makeInvitation([
            'slug' => 'undangan-a',
        ]);

        $invitationB = $this->makeInvitation([
            'slug' => 'undangan-b',
        ]);

        $guest = $invitationA->guests()->create([
            'name' => 'Tamu A',
        ]);

        $response = $this->post(
            route('public.rsvp', $invitationB),
            [
                'token' => $guest->token,
                'name' => 'Tamu A',
                'status' => 'hadir',
                'party_size' => 1,
            ]
        );

        $response->assertSessionHasErrors('token');
    }

    public function test_rsvp_message_creates_directly_approved_wish(): void
    {
        $invitation = $this->makeInvitation();

        $guest = $invitation->guests()->create([
            'name' => 'Tamu Wish',
        ]);

        $this->post(
            route('public.rsvp', $invitation),
            [
                'token' => $guest->token,
                'name' => 'Tamu Wish',
                'status' => 'hadir',
                'party_size' => 1,
                'message' => 'Selamat menempuh hidup baru.',
            ]
        )->assertRedirect();

        $wish = Wish::query()->first();

        $this->assertNotNull($wish);

        $this->assertSame(
            $invitation->id,
            $wish->invitation_id
        );

        $this->assertSame(
            'Tamu Wish',
            $wish->guest_name
        );

        $this->assertSame(
            'Selamat menempuh hidup baru.',
            $wish->message
        );

        $this->assertTrue(
            (bool) $wish->is_approved
        );
    }

    public function test_legacy_free_plan_is_rejected_for_new_invitations(): void
    {
        $template = $this->makeStudioTemplate('basic');
        $payload = $this->studioOrderPayload('free', $template);

        $this->actingAs($this->user)
            ->postJson(route('invitations.store'), $payload)
            ->assertUnprocessable();

        $this->assertDatabaseMissing('invitations', ['slug' => $payload['slug']]);
    }

    public function test_basic_plan_creates_pending_invitation_and_order_with_studio_template(): void
    {
        $template = $this->makeStudioTemplate('basic');
        $payload = $this->studioOrderPayload('basic', $template);

        $this->actingAs($this->user)
            ->post(route('invitations.store'), $payload)
            ->assertRedirect();

        $invitation = Invitation::where('slug', $payload['slug'])->firstOrFail();
        $this->assertSame('pending', $invitation->plan);
        $this->assertSame($template->id, $invitation->studio_template_id);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'invitation_id' => $invitation->id,
            'plan_id' => Plan::where('code', 'basic')->firstOrFail()->id,
            'status' => 'draft',
        ]);
    }

    public function test_basic_plan_cannot_select_blank_canvas(): void
    {
        $template = $this->makeStudioTemplate('basic', blank: true);
        $payload = $this->studioOrderPayload('basic', $template);

        $this->actingAs($this->user)
            ->post(route('invitations.store'), $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('invitations', ['slug' => $payload['slug']]);
    }

    public function test_premium_plan_can_select_premium_template(): void
    {
        $template = $this->makeStudioTemplate('premium');
        $payload = $this->studioOrderPayload('premium', $template);

        $this->actingAs($this->user)
            ->post(route('invitations.store'), $payload)
            ->assertRedirect();

        $invitation = Invitation::where('slug', $payload['slug'])->firstOrFail();
        $this->assertSame('pending', $invitation->plan);
        $this->assertSame($template->id, $invitation->studio_template_id);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'invitation_id' => $invitation->id,
            'plan_id' => $this->premiumPlan->id,
            'status' => 'draft',
        ]);
    }

    public function test_premium_plan_cannot_select_royal_template(): void
    {
        $template = $this->makeStudioTemplate('royal');
        $payload = $this->studioOrderPayload('premium', $template);

        $this->actingAs($this->user)
            ->post(route('invitations.store'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseMissing('invitations', ['slug' => $payload['slug']]);
    }

    public function test_royal_plan_can_select_royal_template(): void
    {
        $template = $this->makeStudioTemplate('royal');
        $payload = $this->studioOrderPayload('royal', $template);

        $this->actingAs($this->user)
            ->post(route('invitations.store'), $payload)
            ->assertRedirect();

        $invitation = Invitation::where('slug', $payload['slug'])->firstOrFail();
        $this->assertSame('pending', $invitation->plan);
        $this->assertSame($template->id, $invitation->studio_template_id);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'invitation_id' => $invitation->id,
            'plan_id' => $this->royalPlan->id,
            'status' => 'draft',
        ]);
    }

    public function test_published_invitation_slug_is_locked_during_update(): void
    {
        $invitation = $this->makeInvitation([
            'slug' => 'slug-lama',
            'is_published' => true,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->patch(
                route(
                    'invitations.update',
                    $invitation
                ),
                [
                    'title' => 'Judul Baru',
                    'slug' => 'slug-baru',
                    'groom_name' => 'A',
                    'bride_name' => 'B',
                    'event_date' => now()
                        ->addMonth()
                        ->format('Y-m-d H:i:s'),
                    'venue_name' => 'Makassar',
                    'theme' => 'modern',
                ]
            );

        $response->assertRedirect();

        $this->assertSame(
            'slug-lama',
            $invitation->fresh()->slug
        );
    }

    public function test_unpublished_invitation_slug_can_change(): void
    {
        $invitation = $this->makeInvitation([
            'slug' => 'slug-awal',
            'is_published' => false,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->patch(
                route(
                    'invitations.update',
                    $invitation
                ),
                [
                    'title' => 'Judul Baru',
                    'slug' => 'slug-baru',
                    'groom_name' => 'A',
                    'bride_name' => 'B',
                    'event_date' => now()
                        ->addMonth()
                        ->format('Y-m-d H:i:s'),
                    'venue_name' => 'Makassar',
                    'theme' => 'modern',
                ]
            );

        $response->assertRedirect();

        $this->assertSame(
            'slug-baru',
            $invitation->fresh()->slug
        );
    }

    public function test_unsigned_qr_checkin_is_rejected(): void
    {
        $invitation = $this->makeInvitation([
            'plan' => 'premium',
        ]);

        $guest = $invitation->guests()->create([
            'name' => 'Tamu QR',
        ]);

        $unsignedUrl = route(
            'public.checkin',
            [
                'invitation' => $invitation,
                'guest' => $guest,
            ]
        );

        $this->get($unsignedUrl)
            ->assertForbidden();

        $this->assertNull(
            $guest->fresh()->checked_in_at
        );
    }

    public function test_valid_signed_qr_url_checks_guest_in(): void
    {
        $invitation = $this->makeInvitation([
            'plan' => 'premium',
        ]);

        $guest = $invitation->guests()->create([
            'name' => 'Tamu QR',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'public.checkin',
            now()->addHour(),
            [
                'invitation' => $invitation,
                'guest' => $guest,
            ]
        );

        $this->get($signedUrl)
            ->assertOk();

        $this->assertNotNull(
            $guest->fresh()->checked_in_at
        );
    }

    public function test_signed_qr_checkin_rejects_guest_from_different_invitation(): void
    {
        $invitationA = $this->makeInvitation([
            'slug' => 'qr-a',
        ]);

        $invitationB = $this->makeInvitation([
            'slug' => 'qr-b',
        ]);

        $guest = $invitationA->guests()->create([
            'name' => 'Guest A',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'public.checkin',
            now()->addHour(),
            [
                'invitation' => $invitationB,
                'guest' => $guest,
            ]
        );

        $this->get($signedUrl)
            ->assertNotFound();

        $this->assertNull(
            $guest->fresh()->checked_in_at
        );
    }

    public function test_guest_photo_upload_is_forbidden_for_free_plan(): void
    {
        Storage::fake('public');

        $invitation = $this->makeInvitation([
            'plan' => 'free',
        ]);

        $response = $this->post(
            route('public.photo', $invitation),
            [
                'guest_name' => 'Tamu Free',
                'photo' => UploadedFile::fake()
                    ->image('foto.jpg'),
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount(
            'guest_photos',
            0
        );
    }

    public function test_guest_photo_upload_is_pending_for_premium_plan(): void
    {
        Storage::fake('public');

        $invitation = $this->makeInvitation([
            'plan' => 'premium',
        ]);

        $response = $this->post(
            route('public.photo', $invitation),
            [
                'guest_name' => 'Tamu Premium',
                'caption' => 'Foto acara',
                'photo' => UploadedFile::fake()
                    ->image('foto.jpg'),
            ]
        );

        $response->assertRedirect();

        $photo = GuestPhoto::query()->first();

        $this->assertNotNull($photo);

        $this->assertSame(
            'pending',
            $photo->status
        );

        $this->assertFalse(
            (bool) $photo->is_approved
        );

        Storage::disk('public')->assertExists(
            $photo->path
        );
    }

    public function test_only_approved_guest_photos_are_visible_publicly(): void
    {
        $invitation = $this->makeInvitation([
            'slug' => 'photo-moderation',
            'plan' => 'premium',
        ]);

        GuestPhoto::query()->create([
            'invitation_id' => $invitation->id,
            'guest_name' => 'Pending Guest',
            'path' => 'pending.jpg',
            'caption' => 'pending',
            'status' => 'pending',
            'is_approved' => false,
        ]);

        GuestPhoto::query()->create([
            'invitation_id' => $invitation->id,
            'guest_name' => 'Approved Guest',
            'path' => 'approved.jpg',
            'caption' => 'approved',
            'status' => 'approved',
            'is_approved' => true,
        ]);

        $response = $this->get(
            route(
                'public.invitation',
                [
                    'slug' => $invitation->slug,
                ]
            )
        );

        $response->assertOk();

        $response->assertViewHas(
            'photos',
            function ($photos) {
                return $photos->count() === 1
                    && $photos->first()->guest_name === 'Approved Guest';
            }
        );
    }
}
