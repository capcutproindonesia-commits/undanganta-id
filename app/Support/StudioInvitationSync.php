<?php

namespace App\Support;

use App\Models\Invitation;
use App\Models\StudioTemplateInstance;
use Illuminate\Support\Str;

class StudioInvitationSync
{
    public function syncIfAttached(Invitation $invitation): ?StudioTemplateInstance
    {
        $templateId = (int) ($invitation->studio_template_id ?? 0);

        if (!$templateId) {
            return null;
        }

        $instance = StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->where('studio_template_id', $templateId)
            ->latest('id')
            ->first();

        if (!$instance) {
            return null;
        }

        return $this->sync($invitation, $instance);
    }

    public function sync(
        Invitation $invitation,
        StudioTemplateInstance $instance
    ): StudioTemplateInstance {
        $existing = $instance->content ?? [];
        $gallery = $this->normalizeGallery($invitation->gallery ?? []);

        $content = array_merge($existing, [
            'groom_name' => (string) ($invitation->groom_name ?? ''),
            'bride_name' => (string) ($invitation->bride_name ?? ''),
            'couple_names' => trim(
                (string) ($invitation->groom_name ?? '')
                . ' & '
                . (string) ($invitation->bride_name ?? '')
            ),
            'event_date' => $invitation->event_date
                ? $invitation->event_date->format('Y-m-d H:i')
                : '',
            'venue_name' => (string) ($invitation->venue_name ?? ''),
            'quote' => (string) ($invitation->quote ?? ''),
            'groom_photo' => $this->media(
                $invitation->groom_photo_path ?? null
            ),
            'bride_photo' => $this->media(
                $invitation->bride_photo_path ?? null
            ),
            'couple_photo' => $this->media(
                $invitation->cover_path ?? null
            ),
            'gallery_1' => $this->media($gallery[0] ?? null),
            'gallery_2' => $this->media($gallery[1] ?? null),
            'gallery_3' => $this->media($gallery[2] ?? null),
        ]);

        $instance->forceFill([
            'owner_id' => $invitation->user_id,
            'status' => $invitation->is_published ? 'published' : 'draft',
            'public_token' => $instance->public_token ?: Str::random(48),
            'content' => $content,
            'meta' => array_merge($instance->meta ?? [], [
                'purpose' => 'production_invitation',
                'synced_from_invitation_at' => now()->toIso8601String(),
                'invitation_slug' => $invitation->slug,
            ]),
        ])->save();

        return $instance->fresh();
    }

    public function syncStatus(Invitation $invitation): void
    {
        StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->update([
                'status' => $invitation->is_published
                    ? 'published'
                    : 'draft',
                'updated_at' => now(),
            ]);
    }

    public function deleteForInvitation(Invitation $invitation): void
    {
        StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->delete();
    }

    private function media(?string $path): ?array
    {
        if (!$path) {
            return null;
        }

        return [
            'url' => url('/storage/' . ltrim($path, '/')),
            'name' => basename($path),
        ];
    }

    private function normalizeGallery(array $gallery): array
    {
        $normalized = [];

        foreach ($gallery as $item) {
            $path = null;

            if (is_string($item)) {
                $path = $item;
            } elseif (is_array($item)) {
                $path = $item['path']
                    ?? $item['url']
                    ?? $item['file']
                    ?? null;
            } elseif (is_object($item)) {
                $path = $item->path
                    ?? $item->url
                    ?? $item->file
                    ?? null;
            }

            if (is_string($path) && trim($path) !== '') {
                $normalized[] = $path;
            }
        }

        return array_values(array_unique($normalized));
    }
}
