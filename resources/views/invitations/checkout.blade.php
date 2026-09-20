@extends('layouts.app')

@section('content')
    @include('invitations._wizard-styles')

    @php
        $bank = config('payment.bank', []);
        $wallet = config('payment.ewallet', []);

        $supportPhone = preg_replace(
            '/\D+/',
            '',
            (string) config('payment.support_whatsapp')
        );

        if (str_starts_with($supportPhone, '0')) {
            $supportPhone = '62' . substr($supportPhone, 1);
        }

        $bankReady =
            filled($bank['name'] ?? null)
            && filled($bank['account_number'] ?? null)
            && filled($bank['account_name'] ?? null);

        $walletReady =
            filled($wallet['name'] ?? null)
            && filled($wallet['number'] ?? null)
            && filled($wallet['account_name'] ?? null);

        $statusLabels = [
            'draft' => 'Belum dibayar',
            'pending' => 'Menunggu verifikasi',
            'rejected' => 'Bukti ditolak',
            'paid' => 'Aktif',
        ];

        $walletName = strtoupper((string) ($wallet['name'] ?? ''));

        $walletBrands = collect(
            preg_split('/[\/,|]+/', $walletName)
        )
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | GLASS NOTIFICATION
        |--------------------------------------------------------------------------
        */

        $notification = null;

        if ($order->status === 'draft') {
            $notification = [
                'type' => 'draft',
                'label' => 'Pembayaran belum selesai',
                'message' => 'Selesaikan pembayaran dan kirim bukti agar undangan dapat diverifikasi.',
            ];
        }

        if ($order->status === 'pending') {
            $notification = [
                'type' => 'pending',
                'label' => 'Menunggu verifikasi',
                'message' => 'Bukti pembayaran sudah diterima. Tunggu verifikasi admin sebelum membuka editor.',
            ];
        }

        if ($order->status === 'rejected') {
            $notification = [
                'type' => 'rejected',
                'label' => 'Pembayaran ditolak',
                'message' => 'Bukti pembayaran ditolak. Periksa pembayaran lalu kirim ulang bukti yang benar.',
            ];
        }

        if ($order->status === 'paid') {
            $notification = [
                'type' => 'paid',
                'label' => 'Pembayaran berhasil',
                'message' => 'Pembayaran terverifikasi. Undangan Anda sudah aktif.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FLASH MESSAGE OVERRIDE
        |--------------------------------------------------------------------------
        */

        if (session('ok')) {
            $notification['message'] = session('ok');
        }
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | PAYMENT CARDS
        |--------------------------------------------------------------------------
        */

        .payment-section {
            margin-top: 16px;
        }

        .payment-section-title {
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #7d7d7d;
        }

        .payment-method-card {
            position: relative;
            overflow: hidden;
            padding: 15px 16px;
            margin-bottom: 10px;
            border: 1px solid #e9e9e9;
            border-radius: 15px;
            background: #fff;
        }

        .payment-method-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: #181818;
        }

        .payment-method-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 13px;
        }

        .payment-method-type {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #999;
        }

        .payment-method-name {
            margin-top: 2px;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 850;
        }

        .bank-bca {
            color: #17315f;
        }

        .wallet-brands {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 4px;
        }

        .wallet-brand {
            display: inline-flex;
            align-items: center;
            padding: 4px 7px;
            border-radius: 999px;
            font-size: 10px;
            line-height: 1;
            font-weight: 850;
            background: #f5f5f5;
        }

        .wallet-dana {
            color: #118eea;
            background: #eef8ff;
        }

        .wallet-gopay {
            color: #00a5cf;
            background: #edfaff;
        }

        .wallet-shopeepay {
            color: #ee4d2d;
            background: #fff3ef;
        }

        .wallet-ovo {
            color: #4c2a86;
            background: #f5f0ff;
        }

        .payment-chip {
            padding: 5px 8px;
            border-radius: 999px;
            background: #f4f4f4;
            font-size: 9px;
            font-weight: 800;
            color: #666;
            white-space: nowrap;
        }

        .payment-number-label {
            margin-bottom: 3px;
            font-size: 9px;
            font-weight: 750;
            color: #999;
        }

        .payment-number-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .payment-number {
            font-size: 16px;
            line-height: 1.2;
            font-weight: 850;
            letter-spacing: .01em;
            color: #111;
            word-break: break-all;
        }

        .copy-payment {
            flex-shrink: 0;
            min-width: 60px;
            padding: 7px 10px;
            border: 1px solid #dedede;
            border-radius: 9px;
            background: #fff;
            color: #171717;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: .15s ease;
        }

        .copy-payment:hover {
            background: #171717;
            border-color: #171717;
            color: #fff;
        }

        .payment-holder {
            display: flex;
            gap: 6px;
            align-items: baseline;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #efefef;
            font-size: 10px;
            color: #777;
        }

        .payment-holder strong {
            color: #222;
            font-size: 10px;
            font-weight: 800;
        }

        .payment-warning {
            margin-top: 10px;
            padding: 11px 13px;
            border-radius: 12px;
            background: #f7f7f7;
            font-size: 10px;
            line-height: 1.55;
            color: #666;
        }

        .payment-support {
            margin-top: 9px;
        }

        .payment-support a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid #dedede;
            border-radius: 10px;
            background: #fff;
            color: #171717;
            font-size: 10px;
            font-weight: 800;
            text-decoration: none;
        }

        /*
        |--------------------------------------------------------------------------
        | GLASS NOTIFICATION
        |--------------------------------------------------------------------------
        */

        .glass-notification-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;

            background: rgba(0, 0, 0, .05);

            animation: glassOverlayIn .22s ease;
        }

        .glass-notification {
            position: relative;

            width: 100%;
            max-width: 420px;

            padding: 21px 48px 24px 21px;

            border-radius: 16px;

            box-shadow:
                0 4px 30px rgba(0, 0, 0, .1);

            backdrop-filter: blur(5.1px);
            -webkit-backdrop-filter: blur(5.1px);

            animation:
                glassNotificationIn .3s
                cubic-bezier(.22, 1, .36, 1);
        }

        /*
        |--------------------------------------------------------------------------
        | DRAFT
        |--------------------------------------------------------------------------
        */

        .glass-notification.is-draft {
            background: rgba(245, 158, 11, .12);
            border: 1px solid rgba(245, 158, 11, .18);
        }

        .glass-notification.is-draft .glass-notification-label {
            color: #8a5700;
        }

        .glass-notification.is-draft .glass-notification-message {
            color: #4d350d;
        }

        .glass-notification.is-draft
        .glass-notification-progress::after {
            background: rgba(202, 126, 0, .55);
        }

        /*
        |--------------------------------------------------------------------------
        | PENDING
        |--------------------------------------------------------------------------
        */

        .glass-notification.is-pending {
            background: rgba(0, 122, 255, .10);
            border: 1px solid rgba(0, 122, 255, .16);
        }

        .glass-notification.is-pending .glass-notification-label {
            color: #075fa8;
        }

        .glass-notification.is-pending .glass-notification-message {
            color: #15354f;
        }

        .glass-notification.is-pending
        .glass-notification-progress::after {
            background: rgba(0, 102, 204, .55);
        }

        /*
        |--------------------------------------------------------------------------
        | REJECTED
        |--------------------------------------------------------------------------
        */

        .glass-notification.is-rejected {
            background: rgba(255, 0, 0, .10);
            border: 1px solid rgba(255, 0, 0, .16);
        }

        .glass-notification.is-rejected .glass-notification-label {
            color: #a50d0d;
        }

        .glass-notification.is-rejected .glass-notification-message {
            color: #421616;
        }

        .glass-notification.is-rejected
        .glass-notification-progress::after {
            background: rgba(180, 10, 10, .55);
        }

        /*
        |--------------------------------------------------------------------------
        | PAID
        |--------------------------------------------------------------------------
        */

        .glass-notification.is-paid {
            background: rgba(34, 197, 94, .11);
            border: 1px solid rgba(34, 197, 94, .18);
        }

        .glass-notification.is-paid .glass-notification-label {
            color: #17753a;
        }

        .glass-notification.is-paid .glass-notification-message {
            color: #173b25;
        }

        .glass-notification.is-paid
        .glass-notification-progress::after {
            background: rgba(22, 140, 69, .55);
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .glass-notification-label {
            margin-bottom: 7px;

            font-size: 9px;
            line-height: 1;
            font-weight: 850;

            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .glass-notification-message {
            margin: 0;

            font-size: 12px;
            line-height: 1.6;
            font-weight: 650;
        }

        .glass-notification-close {
            position: absolute;
            top: 12px;
            right: 12px;

            width: 30px;
            height: 30px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid rgba(255, 255, 255, .30);
            border-radius: 999px;

            background: rgba(255, 255, 255, .30);

            color: rgba(20, 20, 20, .72);

            font-family: Arial, sans-serif;
            font-size: 18px;

            cursor: pointer;

            transition: .15s ease;
        }

        .glass-notification-close:hover {
            background: rgba(255, 255, 255, .58);
            color: #111;
        }

        .glass-notification-progress {
            position: absolute;

            left: 16px;
            right: 16px;
            bottom: 9px;

            height: 2px;

            overflow: hidden;

            border-radius: 999px;

            background: rgba(255, 255, 255, .30);
        }

        .glass-notification-progress::after {
            content: '';

            display: block;

            width: 100%;
            height: 100%;

            border-radius: inherit;

            transform-origin: left center;

            animation:
                glassProgress 5s
                linear
                forwards;
        }

        .glass-notification-overlay.is-hiding {
            pointer-events: none;

            animation:
                glassOverlayOut .3s
                ease
                forwards;
        }

        .glass-notification-overlay.is-hiding
        .glass-notification {
            animation:
                glassNotificationOut .3s
                ease
                forwards;
        }

        @keyframes glassNotificationIn {
            from {
                opacity: 0;
                transform: scale(.96);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes glassNotificationOut {
            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(.97);
            }
        }

        @keyframes glassOverlayIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes glassOverlayOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        @keyframes glassProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }


        .order-flow a:focus-visible,
        .order-flow button:focus-visible,
        .order-flow input:focus-visible,
        .order-flow select:focus-visible,
        .glass-notification-close:focus-visible {
            outline: 3px solid rgba(29,29,31,.14);
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .glass-notification-overlay,
            .glass-notification,
            .glass-notification-progress::after,
            .copy-payment {
                animation: none !important;
                transition: none !important;
            }
        }

        @media (max-width: 640px) {
            .payment-method-card {
                padding: 14px;
                border-radius: 14px;
            }

            .payment-number {
                font-size: 15px;
            }

            .payment-holder {
                flex-wrap: wrap;
            }

            .glass-notification-overlay {
                padding: 16px;
            }

            .glass-notification {
                max-width: 100%;
                padding: 18px 43px 21px 18px;
            }

            .glass-notification-message {
                font-size: 11px;
            }
        }
    </style>

    @if($notification)
        <div
            id="glassNotificationOverlay"
            class="glass-notification-overlay"
        >
            <div
                class="
                    glass-notification
                    is-{{ $notification['type'] }}
                "
                role="status"
                aria-live="polite"
            >
                <div class="glass-notification-label">
                    {{ $notification['label'] }}
                </div>

                <p class="glass-notification-message">
                    {{ $notification['message'] }}
                </p>

                <button
                    type="button"
                    class="glass-notification-close"
                    id="glassNotificationClose"
                    aria-label="Tutup pemberitahuan"
                >
                    ×
                </button>

                <div class="glass-notification-progress"></div>
            </div>
        </div>
    @endif

    <main class="order-flow">
        <div class="flow-top">
            <div>
                <div class="flow-eyebrow">
                    Ringkasan order
                </div>

                <h1 class="flow-title">
                    Aktifkan undangan Anda.
                </h1>

                <p class="flow-copy">
                    Paket dan tema sudah dikunci pada order ini.
                    Editor akan terbuka setelah bukti pembayaran
                    diverifikasi admin.
                </p>
            </div>

            <a
                class="flow-button-secondary"
                href="{{ route('invitations.index') }}"
            >
                Daftar undangan
            </a>
        </div>

        <ol
            class="flow-steps"
            aria-label="Tahapan pemesanan"
        >
            <li class="flow-step">
                1. Paket
            </li>

            <li class="flow-step">
                2. Tema
            </li>

            <li class="flow-step">
                3. Data awal
            </li>

            <li class="flow-step active">
                4. Aktivasi
            </li>
        </ol>

        @if($errors->any())
            <div class="flow-error">
                @foreach($errors->all() as $error)
                    <div>
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flow-summary">

            <section class="flow-panel">
                <div class="flow-card-name">
                    Detail pesanan
                </div>

                <dl class="summary-list">

                    <div class="summary-row">
                        <dt>Undangan</dt>
                        <dd>{{ $invitation->title }}</dd>
                    </div>

                    <div class="summary-row">
                        <dt>Paket</dt>
                        <dd>
                            {{ $plan?->name ?? strtoupper($invitation->plan) }}
                        </dd>
                    </div>

                    <div class="summary-row">
                        <dt>Tema</dt>
                        <dd>{{ ucfirst($invitation->theme) }}</dd>
                    </div>

                    <div class="summary-row">
                        <dt>Slug</dt>
                        <dd>{{ $invitation->slug }}</dd>
                    </div>

                    <div class="summary-row">
                        <dt>Total</dt>
                        <dd>
                            Rp{{ number_format(
                                $order->amount,
                                0,
                                ',',
                                '.'
                            ) }}
                        </dd>
                    </div>

                    <div class="summary-row">
                        <dt>Status</dt>
                        <dd>
                            {{ $statusLabels[$order->status]
                                ?? ucfirst($order->status)
                            }}
                        </dd>
                    </div>

                </dl>
            </section>

            <section class="flow-panel">

                @if($order->status === 'pending')

                    <div class="flow-card-name">
                        Menunggu verifikasi
                    </div>

                    <p
                        class="payment-note"
                        style="margin-top:14px;"
                    >
                        Bukti pembayaran sudah diterima.
                        Admin akan memeriksa order ini sebelum
                        editor diaktifkan.
                        Anda tidak perlu mengirim bukti lagi.
                    </p>

                    @if($order->payment_proof)

                        <a
                            class="flow-button-secondary"
                            href="{{ asset(
                                'storage/' . $order->payment_proof
                            ) }}"
                            target="_blank"
                            rel="noopener"
                            style="
                                width:100%;
                                margin-bottom:10px;
                            "
                        >
                            Lihat bukti terkirim
                        </a>

                    @endif

                    <button
                        class="flow-button"
                        type="button"
                        disabled
                    >
                        Sedang diverifikasi
                    </button>

                @else

                    <div class="flow-card-name">
                        {{
                            $order->status === 'rejected'
                                ? 'Kirim ulang bukti'
                                : 'Upload bukti pembayaran'
                        }}
                    </div>

                    @if($order->status === 'rejected')

                        <div
                            class="flow-error"
                            style="margin-top:14px;"
                        >
                            Bukti sebelumnya ditolak.
                            Periksa transaksi,
                            lalu kirim bukti yang benar.
                        </div>

                        @if($order->payment_proof)

                            <a
                                class="flow-button-secondary"
                                href="{{ asset(
                                    'storage/' . $order->payment_proof
                                ) }}"
                                target="_blank"
                                rel="noopener"
                                style="
                                    width:100%;
                                    margin-bottom:12px;
                                "
                            >
                                Lihat bukti sebelumnya
                            </a>

                        @endif

                    @endif

                    @if($bankReady || $walletReady)

                        <div class="payment-section">

                            <div class="payment-section-title">
                                Tujuan pembayaran
                            </div>

                            @if($bankReady)

                                <div class="payment-method-card">

                                    <div class="payment-method-top">

                                        <div>
                                            <div class="payment-method-type">
                                                Transfer bank
                                            </div>

                                            <div
                                                class="
                                                    payment-method-name
                                                    bank-bca
                                                "
                                            >
                                                {{ $bank['name'] }}
                                            </div>
                                        </div>

                                        <div class="payment-chip">
                                            BANK
                                        </div>

                                    </div>

                                    <div class="payment-number-label">
                                        Nomor rekening
                                    </div>

                                    <div class="payment-number-row">

                                        <div class="payment-number">
                                            {{ $bank['account_number'] }}
                                        </div>

                                        <button
                                            type="button"
                                            class="copy-payment"
                                            data-copy="{{ $bank['account_number'] }}"
                                            aria-label="Salin nomor rekening"
                                        >
                                            Salin
                                        </button>

                                    </div>

                                    <div class="payment-holder">
                                        <span>
                                            Atas nama
                                        </span>

                                        <strong>
                                            {{ $bank['account_name'] }}
                                        </strong>
                                    </div>

                                </div>

                            @endif

                            @if($walletReady)

                                <div class="payment-method-card">

                                    <div class="payment-method-top">

                                        <div>

                                            <div class="payment-method-type">
                                                E-Wallet
                                            </div>

                                            <div class="wallet-brands">

                                                @foreach(
                                                    $walletBrands
                                                    as $brand
                                                )

                                                    @php
                                                        $brandClass = match(
                                                            $brand
                                                        ) {
                                                            'DANA'
                                                                => 'wallet-dana',

                                                            'GOPAY'
                                                                => 'wallet-gopay',

                                                            'SHOPEEPAY'
                                                                => 'wallet-shopeepay',

                                                            'OVO'
                                                                => 'wallet-ovo',

                                                            default
                                                                => '',
                                                        };
                                                    @endphp

                                                    <span
                                                        class="
                                                            wallet-brand
                                                            {{ $brandClass }}
                                                        "
                                                    >
                                                        {{ $brand }}
                                                    </span>

                                                @endforeach

                                            </div>

                                        </div>

                                        <div class="payment-chip">
                                            DIGITAL
                                        </div>

                                    </div>

                                    <div class="payment-number-label">
                                        Nomor e-wallet
                                    </div>

                                    <div class="payment-number-row">

                                        <div class="payment-number">
                                            {{ $wallet['number'] }}
                                        </div>

                                        <button
                                            type="button"
                                            class="copy-payment"
                                            data-copy="{{ $wallet['number'] }}"
                                            aria-label="Salin nomor e-wallet"
                                        >
                                            Salin
                                        </button>

                                    </div>

                                    <div class="payment-holder">
                                        <span>
                                            Atas nama
                                        </span>

                                        <strong>
                                            {{ $wallet['account_name'] }}
                                        </strong>
                                    </div>

                                </div>

                            @endif

                            <div class="payment-warning">
                                Transfer sesuai nominal pada detail pesanan.
                                Pastikan nama dan nomor tujuan pembayaran
                                sesuai sebelum mengirim dana.
                            </div>

                        </div>

                    @else

                        <div
                            class="flow-error"
                            style="margin-top:14px;"
                        >
                            Tujuan pembayaran belum ditampilkan.
                            Jangan melakukan transfer sebelum menerima
                            rekening resmi dari admin UNDANGANTA.ID.

                            @if($supportPhone)

                                <div class="payment-support">
                                    <a
                                        href="https://wa.me/{{ $supportPhone }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Hubungi admin pembayaran
                                    </a>
                                </div>

                            @endif

                        </div>

                    @endif

                    <p class="payment-note">
                        Pastikan nominal sesuai total order.
                        Bukti pembayaran harus berupa
                        JPG, PNG, atau WebP,
                        maksimal 8 MB.
                    </p>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action="{{ route(
                            'orders.store',
                            $invitation
                        ) }}"
                    >
                        @csrf

                        <div class="field">

                            <label for="payment_method">
                                Metode pembayaran
                            </label>

                            <select
                                id="payment_method"
                                name="payment_method"
                                required
                            >

                                <option
                                    value="transfer_manual"
                                    @selected(
                                        old(
                                            'payment_method',
                                            'transfer_manual'
                                        )
                                        === 'transfer_manual'
                                    )
                                >
                                    Transfer bank
                                </option>

                                <option
                                    value="e_wallet"
                                    @selected(
                                        old(
                                            'payment_method'
                                        )
                                        === 'e_wallet'
                                    )
                                >
                                    E-wallet
                                </option>

                            </select>

                        </div>

                        <div
                            class="field"
                            style="margin-top:14px;"
                        >

                            <label for="payment_proof">
                                Bukti pembayaran
                            </label>

                            <input
                                id="payment_proof"
                                name="payment_proof"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                required
                            >

                        </div>

                        <button
                            class="flow-button"
                            type="submit"
                            style="
                                width:100%;
                                margin-top:18px;
                            "
                        >
                            Kirim untuk verifikasi
                        </button>

                    </form>

                @endif

            </section>

        </div>
    </main>

    <script>
        /*
        |--------------------------------------------------------------------------
        | COPY PAYMENT NUMBER
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            async function (event) {

                const button =
                    event.target.closest(
                        '.copy-payment'
                    );

                if (!button) {
                    return;
                }

                const value =
                    button.dataset.copy || '';

                if (!value) {
                    return;
                }

                const original =
                    button.textContent;

                try {

                    await navigator
                        .clipboard
                        .writeText(value);

                } catch (error) {

                    const input =
                        document.createElement(
                            'input'
                        );

                    input.value = value;

                    document.body
                        .appendChild(input);

                    input.select();

                    document.execCommand(
                        'copy'
                    );

                    input.remove();
                }

                button.textContent =
                    'Tersalin';

                setTimeout(() => {
                    button.textContent =
                        original;
                }, 1600);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | GLASS NOTIFICATION
        | AUTO HIDE 5 SECONDS
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const overlay =
                    document.getElementById(
                        'glassNotificationOverlay'
                    );

                const closeButton =
                    document.getElementById(
                        'glassNotificationClose'
                    );

                if (!overlay) {
                    return;
                }

                let closed = false;

                function closeGlassNotification() {

                    if (closed) {
                        return;
                    }

                    closed = true;

                    overlay.classList.add(
                        'is-hiding'
                    );

                    setTimeout(() => {
                        overlay.remove();
                    }, 300);
                }

                const timer =
                    setTimeout(
                        closeGlassNotification,
                        5000
                    );

                if (closeButton) {

                    closeButton.addEventListener(
                        'click',
                        function () {

                            clearTimeout(timer);

                            closeGlassNotification();
                        }
                    );
                }

                overlay.addEventListener(
                    'click',
                    function (event) {

                        if (event.target === overlay) {

                            clearTimeout(timer);

                            closeGlassNotification();
                        }
                    }
                );
            }
        );
    </script>
@endsection