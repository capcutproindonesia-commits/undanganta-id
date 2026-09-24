<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Plan;
use App\Models\StudioTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_basic_invitation_and_publish_after_activation(): void
    {
        $user = User::factory()->create();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $template = StudioTemplate::create([
            'name' => 'Basic QA',
            'slug' => 'basic-qa',
            'status' => 'published',
            'min_plan' => 'basic',
            'canvas' => ['pages' => []],
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('invitations.store'),
                [
                    'selected_plan' => 'basic',
                    'studio_template_id' => $template->id,
                    'title' => 'A & B',
                    'slug' => 'a-b-basic',
                    'groom_name' => 'A',
                    'bride_name' => 'B',
                    'event_date' => '2026-12-20 10:00:00',
                    'venue_name' => 'Makassar',
                    'venue_address' => 'Makassar',
                ]
            );

        $response->assertRedirect();

        $invitation = Invitation::query()
            ->firstOrFail();

        $this->assertSame(
            'pending',
            $invitation->plan
        );
        $this->assertDatabaseHas('orders', [
            'invitation_id' => $invitation->id,
            'plan_id' => $plan->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->post(route('invitations.publish', $invitation))
            ->assertSessionHasErrors('publish');
        $this->assertFalse((bool) $invitation->fresh()->is_published);

        $invitation->forceFill(['plan' => 'basic'])->save();

        $this->actingAs($user)
            ->post(
                route(
                    'invitations.publish',
                    $invitation
                )
            )
            ->assertRedirect();

        $invitation->refresh();

        $this->assertTrue(
            (bool) $invitation->is_published
        );

        $this->get(
            route(
                'public.invitation',
                [
                    'slug' => $invitation->slug,
                ]
            )
        )
            ->assertOk()
            ->assertSee('A');
    }
}
