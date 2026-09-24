<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\StudioAsset;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioPremiumSaveTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstance(User $owner): StudioTemplateInstance
    {
        $template = StudioTemplate::create([
            'name' => 'QA Premium',
            'slug' => 'qa-premium',
            'status' => 'published',
            'min_plan' => 'premium',
            'canvas' => ['pages' => []],
        ]);

        $invitation = Invitation::create([
            'user_id' => $owner->id,
            'slug' => 'qa-premium-invitation',
            'title' => 'QA Premium',
            'groom_name' => 'Awal',
            'bride_name' => 'Pasangan',
            'event_date' => '2026-11-22',
            'venue_name' => 'Lokasi Awal',
            'theme' => 'modern',
            'plan' => 'premium',
            'studio_template_id' => $template->id,
        ]);

        return StudioTemplateInstance::create([
            'studio_template_id' => $template->id,
            'invitation_id' => $invitation->id,
            'owner_id' => $owner->id,
            'public_token' => str_repeat('a', 48),
            'template_snapshot' => ['pages' => [['id' => 'canvas-1', 'layers' => []]]],
            'content' => [],
        ]);
    }

    public function test_premium_partial_data_save_and_clear_preserve_canvas_and_required_invitation_date(): void
    {
        $owner = User::factory()->create();
        $instance = $this->makeInstance($owner);
        $originalCanvas = $instance->template_snapshot;
        $originalDate = $instance->invitation_id
            ? Invitation::findOrFail($instance->invitation_id)->event_date->toDateString()
            : null;

        $this->actingAs($owner)
            ->putJson(route('studio.customer.full.update', $instance), [
                'content' => ['groom_name' => 'Nama Baru'],
            ])
            ->assertOk()
            ->assertJsonPath('content.groom_name', 'Nama Baru');

        $this->putJson(route('studio.customer.full.update', $instance), [
            'content' => ['groom_name' => '', 'event_date' => ''],
        ])
            ->assertOk()
            ->assertJsonPath('content.groom_name', '')
            ->assertJsonPath('content.event_date', '');

        $instance->refresh();
        $invitation = Invitation::findOrFail($instance->invitation_id);
        $this->assertSame($originalCanvas, $instance->template_snapshot);
        $this->assertSame('', $instance->content['groom_name']);
        $this->assertSame('', $invitation->groom_name);
        $this->assertSame($originalDate, $invitation->event_date->toDateString());
    }

    public function test_premium_gallery_rejects_another_users_asset(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $instance = $this->makeInstance($owner);
        $foreignAsset = StudioAsset::create([
            'type' => 'image',
            'name' => 'foreign.jpg',
            'path' => 'foreign.jpg',
            'size' => 1,
            'created_by' => $other->id,
        ]);

        $this->actingAs($owner)
            ->putJson(route('studio.customer.full.update', $instance), [
                'gallery_asset_ids' => [$foreignAsset->id],
            ])
            ->assertStatus(422);

        $this->assertSame([], $instance->fresh()->content);
    }
}
