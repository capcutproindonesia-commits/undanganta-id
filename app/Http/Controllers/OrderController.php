<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\StudioTemplate;
use App\Support\PlanCapabilities;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function checkout(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $order = Order::query()
            ->with('plan')
            ->where('user_id', $request->user()->id)
            ->where('invitation_id', $invitation->id)
            ->latest()
            ->firstOrFail();

        if ($order->status === 'paid') {
            return redirect()
                ->route('invitations.edit', $invitation)
                ->with('ok', 'Pembayaran sudah aktif. Silakan lanjutkan editor.');
        }

        abort_unless(
            $invitation->plan === 'pending',
            422,
            'Undangan ini sudah aktif.'
        );

        $this->ensureThemeMatchesPlan($invitation, $order);

        return view('invitations.checkout', [
            'invitation' => $invitation,
            'order' => $order,
            'plan' => $order->plan,
        ]);
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        abort_unless(
            $invitation->plan === 'pending',
            422,
            'Undangan ini sudah aktif.'
        );

        $order = Order::query()
            ->with('plan')
            ->where('user_id', $request->user()->id)
            ->where('invitation_id', $invitation->id)
            ->latest()
            ->firstOrFail();

        $this->ensureThemeMatchesPlan($invitation, $order);

        abort_if(
            $order->status === 'paid',
            422,
            'Order ini sudah dibayar.'
        );

        abort_unless(
            in_array($order->status, ['draft', 'rejected'], true),
            422,
            'Bukti pembayaran sedang diperiksa.'
        );

        $validated = $request->validate([
            'payment_method' => ['required','in:transfer_manual,e_wallet'],
            'payment_proof' => ['required','image','mimes:jpg,jpeg,png,webp','max:8192'],
        ]);

        $proof = $request->file('payment_proof')->store('payment-proofs', 'public');

        $order->update([
            'amount' => $order->plan->price,
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $proof,
            'verified_at' => null,
        ]);

        return redirect()
            ->route('orders.checkout', $invitation)
            ->with('ok', 'Bukti pembayaran terkirim. Admin akan memverifikasi order Anda.');
    }

    private function ensureThemeMatchesPlan(Invitation $invitation, Order $order): void
    {
        $template = StudioTemplate::query()->findOrFail($invitation->studio_template_id);

        if (PlanCapabilities::isSystemBlankTemplate($template)) {
            abort_unless(
                PlanCapabilities::blankAllowed((string) $order->plan->code),
                422,
                'Blank canvas tidak tersedia untuk paket ini.'
            );
            return;
        }

        abort_unless(
            $template->status === 'published'
            && PlanCapabilities::templateAllowed(
                (string) $order->plan->code,
                (string) $template->min_plan
            ),
            422,
            'Template Studio order tidak sesuai dengan paket.'
        );
    }
}