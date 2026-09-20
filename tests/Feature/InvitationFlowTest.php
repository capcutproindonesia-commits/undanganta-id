<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_publish_free_invitation(): void
    {
        $user = User::factory()->create();

        Plan::query()->create([
            'name' => 'Free',
            'code' => 'free',
            'price' => 0,
            'duration_days' => 365,
            'features' => null,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('invitations.store'),
                [
                    'selected_plan' => 'free',
                    'theme' => 'modern',
                    'title' => 'A & B',
                    'slug' => 'a-b',
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
            'free',
            $invitation->plan
        );

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