<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateInstance;
use App\Support\StudioInvitationSync;
use App\Support\PlanCapabilities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudioInvitationController extends Controller
{
    public function manage(Invitation $invitation): View|RedirectResponse
    {
        $this->authorize('update', $invitation);

        if ($invitation->plan === 'pending') {
            return redirect()
                ->route('orders.checkout', $invitation)
                ->with('ok', 'Aktifkan paket sebelum memilih template Studio.');
        }

        $templates = StudioTemplate::query()
            ->where('status', 'published')
            ->where('slug', '!=', '__system-blank-canvas')
            ->orderBy('name')
            ->get()
            ->filter(
                fn (StudioTemplate $template) =>
                    $this->templateAllowed(
                        (string) $invitation->plan,
                        (string) $template->min_plan
                    )
            )
            ->values();

        $instance = StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->latest('id')
            ->first();

        return view('invitations.studio', [
            'invitation' => $invitation,
            'templates' => $templates,
            'instance' => $instance,
        ]);
    }

    public function attach(
        Invitation $invitation,
        StudioTemplate $template,
        StudioInvitationSync $sync
    ): RedirectResponse {
        $this->authorize('update', $invitation);

        abort_if($invitation->plan === 'pending', 403);

        abort_unless(
            $template->status === 'published'
            && $this->templateAllowed(
                (string) $invitation->plan,
                (string) $template->min_plan
            ),
            403,
            'Template tidak tersedia untuk paket ini.'
        );

        $oldTemplateId = (int) ($invitation->studio_template_id ?? 0);

        $instance = StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->latest('id')
            ->first();

        if (!$instance) {
            $instance = new StudioTemplateInstance();
            $instance->invitation_id = $invitation->id;
            $instance->owner_id = $invitation->user_id;
            $instance->public_token = Str::random(48);
        }

        $templateChanged = $oldTemplateId !== (int) $template->id;

        $instance->studio_template_id = $template->id;
        $instance->template_snapshot = $template->canvas ?: [];
        $instance->status = $invitation->is_published
            ? 'published'
            : 'draft';

        if ($templateChanged) {
            $instance->design_overrides = [];
        }

        $instance->meta = array_merge($instance->meta ?? [], [
            'purpose' => 'production_invitation',
            'attached_at' => now()->toIso8601String(),
        ]);
        $instance->save();

        $invitation->forceFill([
            'studio_template_id' => $template->id,
        ])->save();

        $sync->sync($invitation->fresh(), $instance->fresh());

        return redirect()
            ->route('studio.customer.edit', $instance)
            ->with('ok', 'Template Studio aktif untuk undangan ini.');
    }

    public function open(
        Invitation $invitation,
        StudioInvitationSync $sync
    ): RedirectResponse {
        $this->authorize('update', $invitation);

        if ($invitation->plan === 'pending') {
            return redirect()
                ->route('orders.checkout', $invitation)
                ->with('ok', 'Aktifkan paket sebelum membuka Studio.');
        }

        if (!$invitation->studio_template_id) {
            $template = StudioTemplate::query()
                ->where('status', 'published')
                ->orderBy('id')
                ->get()
                ->first(
                    fn (StudioTemplate $candidate) =>
                        $this->templateAllowed(
                            (string) $invitation->plan,
                            (string) $candidate->min_plan
                        )
                );

            if (!$template) {
                return redirect()
                    ->route('studio.invitation.manage', $invitation)
                    ->withErrors([
                        'studio' => 'Belum ada template Studio published yang tersedia untuk paket ini.',
                    ]);
            }

            $invitation->forceFill([
                'studio_template_id' => $template->id,
            ])->save();
        }

        $template = StudioTemplate::query()
            ->whereKey($invitation->studio_template_id)
            ->where('status', 'published')
            ->first();

        if (
            !$template
            || !$this->templateAllowed(
                (string) $invitation->plan,
                (string) $template->min_plan
            )
        ) {
            $invitation->forceFill([
                'studio_template_id' => null,
            ])->save();

            return redirect()
                ->route('studio.invitation.open', $invitation)
                ->withErrors([
                    'studio' => 'Template Studio sebelumnya tidak tersedia lagi. Sistem memilih template aktif lain.',
                ]);
        }

        $instance = StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->where('studio_template_id', $template->id)
            ->latest('id')
            ->first();

        if (!$instance) {
            $instance = StudioTemplateInstance::create([
                'studio_template_id' => $template->id,
                'invitation_id' => $invitation->id,
                'owner_id' => $invitation->user_id,
                'status' => $invitation->is_published
                    ? 'published'
                    : 'draft',
                'public_token' => Str::random(48),
                'template_snapshot' => $template->canvas ?: [],
                'content' => [],
                'design_overrides' => [],
                'meta' => [
                    'purpose' => 'production_invitation',
                    'attached_at' => now()->toIso8601String(),
                ],
            ]);
        }

        $instance = $sync->sync(
            $invitation->fresh(),
            $instance
        );

        return redirect()->route(
            'studio.customer.edit',
            $instance
        );
    }

    public function preview(
        Invitation $invitation,
        StudioInvitationSync $sync
    ): RedirectResponse {
        $this->authorize('update', $invitation);

        $instance = StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->latest('id')
            ->firstOrFail();

        $instance = $sync->sync(
            $invitation->fresh(),
            $instance
        );

        return redirect()->route(
            'studio.public.show',
            $instance->public_token
        );
    }

    public function detach(
        Invitation $invitation
    ): RedirectResponse {
        $this->authorize('update', $invitation);

        StudioTemplateInstance::query()
            ->where('invitation_id', $invitation->id)
            ->delete();

        $invitation->forceFill([
            'studio_template_id' => null,
        ])->save();

        return redirect()
            ->route('studio.invitation.manage', $invitation)
            ->with('ok', 'Template Studio dilepas. Tema lama tetap tersedia.');
    }

    private function templateAllowed(
        string $invitationPlan,
        string $minimumPlan
    ): bool {
        return PlanCapabilities::templateAllowed($invitationPlan, $minimumPlan);
    }
}
