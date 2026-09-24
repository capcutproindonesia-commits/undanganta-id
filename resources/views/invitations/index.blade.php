@extends('layouts.app')

@section('content')

<style>

    /*
    |--------------------------------------------------------------------------
    | UNDANGAN INDEX ONLY
    |--------------------------------------------------------------------------
    */

    .inv-page {
        width:100%;
        max-width:1160px;
        margin:0 auto;
        padding:34px 22px 50px;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .inv-head {
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:20px;
        margin-bottom:24px;
    }

    .inv-kicker {
        margin-bottom:5px;
        color:#86868b;
        font-size:11px;
        font-weight:600;
        letter-spacing:.04em;
    }

    .inv-title {
        margin:0;
        color:#1d1d1f;
        font-size:34px;
        line-height:1.08;
        font-weight:700;
        letter-spacing:-.04em;
    }

    .inv-description {
        margin:8px 0 0;
        color:#86868b;
        font-size:13px;
        line-height:1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | DARK BUTTON
    |--------------------------------------------------------------------------
    */

    .inv-primary {
        position:relative;

        display:inline-flex;
        align-items:center;
        justify-content:center;

        gap:7px;

        min-height:38px;
        padding:0 15px;

        border:1px solid rgba(255,255,255,.09);
        border-radius:999px;

        background:
            linear-gradient(
                180deg,
                #303033 0%,
                #131315 100%
            );

        color:#fff;

        font-size:11px;
        font-weight:650;
        line-height:1;

        white-space:nowrap;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.16),
            inset 0 -1px 0 rgba(0,0,0,.25),
            0 4px 12px rgba(0,0,0,.12);

        transition:
            transform .16s ease,
            box-shadow .16s ease,
            background .16s ease;
    }

    .inv-primary::before {
        content:"";

        position:absolute;

        top:2px;
        left:15%;
        right:15%;

        height:7px;

        border-radius:999px;

        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.16),
                rgba(255,255,255,0)
            );

        pointer-events:none;
    }

    .inv-primary:hover {
        transform:translateY(-1px);

        background:
            linear-gradient(
                180deg,
                #3a3a3e,
                #19191b
            );

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.19),
            0 7px 17px rgba(0,0,0,.16);
    }

    .inv-primary:active {
        transform:scale(.97);
    }

    /*
    |--------------------------------------------------------------------------
    | LIGHT BUTTON
    |--------------------------------------------------------------------------
    */

    .inv-light {
        display:inline-flex;
        align-items:center;
        justify-content:center;

        min-height:34px;
        padding:0 12px;

        border:1px solid rgba(0,0,0,.08);
        border-radius:999px;

        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.98),
                rgba(244,244,246,.96)
            );

        color:#3a3a3c;

        font-size:10px;
        font-weight:600;
        line-height:1;

        white-space:nowrap;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,1),
            0 2px 5px rgba(0,0,0,.04);

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    .inv-light:hover {
        transform:translateY(-1px);

        background:#fff;

        box-shadow:
            0 4px 9px rgba(0,0,0,.06);
    }

    .inv-light:active {
        transform:scale(.97);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    .inv-tools {
        display:grid;
        grid-template-columns:minmax(0,1fr) 170px auto;

        align-items:center;

        gap:10px;

        margin-bottom:18px;
    }

    .inv-search,
    .inv-select {
        width:100%;
        min-height:40px;

        padding:0 13px;

        border:1px solid #dedee2;
        border-radius:10px;

        background:#fff;
        color:#1d1d1f;

        font-size:11px;

        outline:none;
    }

    .inv-search:focus,
    .inv-select:focus {
        border-color:#a5a5aa;

        box-shadow:
            0 0 0 3px rgba(0,0,0,.035);
    }

    .inv-filter-btn {
        min-height:40px;

        display:inline-flex;
        align-items:center;
        justify-content:center;

        padding:0 14px;

        border:1px solid #dedee2;
        border-radius:10px;

        background:#fff;
        color:#3a3a3c;

        font-size:11px;
        font-weight:600;

        cursor:pointer;
    }

    .inv-filter-btn:hover {
        background:#f7f7f8;
    }

    /*
    |--------------------------------------------------------------------------
    | PANEL
    |--------------------------------------------------------------------------
    */

    .inv-panel {
        overflow:hidden;

        border:1px solid #e5e5e7;
        border-radius:13px;

        background:#fff;
    }

    .inv-panel-head {
        min-height:58px;

        display:flex;
        align-items:center;
        justify-content:space-between;

        gap:16px;

        padding:0 17px;

        border-bottom:1px solid #eeeeef;
    }

    .inv-panel-title {
        color:#1d1d1f;
        font-size:14px;
        font-weight:700;
    }

    .inv-panel-copy {
        margin-top:4px;
        color:#a1a1a6;
        font-size:10px;
    }

    .inv-total {
        padding:5px 9px;

        border-radius:999px;

        background:#f2f2f3;
        color:#6e6e73;

        font-size:9px;
        font-weight:650;
    }

    /*
    |--------------------------------------------------------------------------
    | ROW
    |--------------------------------------------------------------------------
    */

    .inv-row {
        display:grid;

        grid-template-columns:
            58px
            minmax(0,1fr)
            95px
            90px
            auto;

        align-items:center;

        gap:15px;

        min-height:84px;

        padding:13px 17px;

        border-bottom:1px solid #f0f0f2;
    }

    .inv-row:last-child {
        border-bottom:0;
    }

    .inv-cover {
        width:58px;
        height:58px;

        overflow:hidden;

        display:grid;
        place-items:center;

        border:1px solid #e5e5e7;
        border-radius:12px;

        background:#f5f5f7;

        color:#8e8e93;

        font-size:10px;
        font-weight:700;
    }

    .inv-cover img {
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
    }

    .inv-info {
        min-width:0;
    }

    .inv-name {
        overflow:hidden;

        margin-bottom:4px;

        color:#1d1d1f;

        font-size:14px;
        font-weight:650;

        white-space:nowrap;
        text-overflow:ellipsis;
    }

    .inv-meta {
        overflow:hidden;

        margin-bottom:4px;

        color:#8e8e93;

        font-size:10px;

        white-space:nowrap;
        text-overflow:ellipsis;
    }

    .inv-slug {
        overflow:hidden;

        color:#aeaeb2;

        font-size:9px;

        white-space:nowrap;
        text-overflow:ellipsis;
    }

    .inv-views {
        color:#6e6e73;
        font-size:10px;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    .inv-status {
        display:inline-flex;
        align-items:center;
        justify-content:center;

        width:max-content;

        padding:5px 8px;

        border-radius:999px;

        background:#f2f2f3;
        color:#7a7a7f;

        font-size:8px;
        font-weight:700;
    }

    .inv-status.live {
        background:#edf7ef;
        color:#248a3d;
    }

    .inv-status.payment {
        background:#f2f2f3;
        color:#5f5f64;
    }

    .inv-status.waiting {
        background:#fff8e8;
        color:#8a6100;
    }

    .inv-status.rejected {
        background:#fff1f0;
        color:#b42318;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    .inv-actions {
        display:flex;
        align-items:center;
        justify-content:flex-end;

        gap:6px;

        flex-wrap:wrap;
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

    .inv-empty {
        padding:64px 20px;
        text-align:center;
    }

    .inv-empty-mark {
        width:52px;
        height:52px;

        display:grid;
        place-items:center;

        margin:0 auto 14px;

        border:1px solid #e5e5e7;
        border-radius:14px;

        background:#f7f7f8;

        color:#8e8e93;

        font-size:18px;
    }

    .inv-empty-title {
        margin-bottom:5px;

        color:#1d1d1f;

        font-size:14px;
        font-weight:700;
    }

    .inv-empty-copy {
        margin-bottom:17px;

        color:#8e8e93;

        font-size:10px;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media(max-width:900px) {

        .inv-row {
            grid-template-columns:
                58px
                minmax(0,1fr)
                auto;
        }

        .inv-views {
            display:none;
        }

        .inv-status-wrap {
            grid-column:2;
        }

        .inv-actions {
            grid-column:3;
            grid-row:1 / span 2;
        }

    }


    @media(max-width:700px) {

        .inv-page {
            padding:22px 14px 36px;
        }

        .inv-head {
            align-items:flex-start;
            flex-direction:column;
        }

        .inv-title {
            font-size:28px;
        }

        .inv-description {
            font-size:12px;
        }

        .inv-tools {
            grid-template-columns:1fr;
        }

        .inv-row {
            grid-template-columns:
                50px
                minmax(0,1fr);

            gap:12px;
        }

        .inv-cover {
            width:50px;
            height:50px;
        }

        .inv-name {
            font-size:13px;
        }

        .inv-status-wrap {
            grid-column:2;
        }

        .inv-actions {
            grid-column:1 / -1;
            grid-row:auto;

            justify-content:flex-start;

            padding-left:62px;
        }

        .inv-panel-head {
            padding:0 14px;
        }

        .inv-row {
            padding:13px 14px;
        }

    }

</style>


<div class="inv-page">


    {{-- HEADER --}}

    <div class="inv-head">

        <div>

            <div class="inv-kicker">
                UNDANGANTA.ID
            </div>

            <h1 class="inv-title">
                Undangan Saya
            </h1>

            <p class="inv-description">
                Kelola semua undangan dalam satu tempat.
            </p>

        </div>


        <a
            href="{{ route('invitations.create') }}"
            class="inv-primary"
        >
            + Buat Undangan
        </a>

    </div>


    {{-- SEARCH + FILTER --}}

    <form
        method="GET"
        action="{{ route('invitations.index') }}"
        class="inv-tools"
    >

        <input
            type="text"
            name="q"
            class="inv-search"
            placeholder="Cari nama, judul, atau venue..."
            value="{{ request('q') }}"
        >


        <select
            name="status"
            class="inv-select"
        >

            <option value="">
                Semua status
            </option>

            <option
                value="published"
                @selected(request('status') === 'published')
            >
                Published
            </option>

            <option
                value="draft"
                @selected(request('status') === 'draft')
            >
                Draft Aktif
            </option>

            <option
                value="payment"
                @selected(request('status') === 'payment')
            >
                Pembayaran
            </option>

        </select>


        <button
            type="submit"
            class="inv-filter-btn"
        >
            Cari
        </button>

    </form>


    {{-- LIST --}}

    <section class="inv-panel">


        <div class="inv-panel-head">

            <div>

                <div class="inv-panel-title">
                    Semua Undangan
                </div>

                <div class="inv-panel-copy">
                    Edit, kelola tamu, atau lihat undangan publik.
                </div>

            </div>


            <div class="inv-total">
                {{ $invitations->count() }}
                undangan
            </div>

        </div>


        @forelse($invitations as $invitation)

            @php
                $order = $invitation->latestOrder;
                $needsActivation = $invitation->plan === 'pending';
                $orderStatus = $order?->status ?? 'draft';
            @endphp

            <div class="inv-row">


                {{-- COVER --}}

                <div class="inv-cover">

                    @if($invitation->cover_path)

                        <img
                            src="{{ url('/storage/' . $invitation->cover_path) }}"
                            alt="{{ $invitation->title }}"
                        >

                    @else

                        {{ strtoupper(
                            substr(
                                $invitation->groom_name ?? 'U',
                                0,
                                1
                            )
                        ) }}

                        {{ strtoupper(
                            substr(
                                $invitation->bride_name ?? '',
                                0,
                                1
                            )
                        ) }}

                    @endif

                </div>


                {{-- INFO --}}

                <div class="inv-info">

                    <div class="inv-name">

                        {{ $invitation->groom_name ?? 'Mempelai' }}

                        &

                        {{ $invitation->bride_name ?? 'Mempelai' }}

                    </div>


                    <div class="inv-meta">

                        {{ $invitation->title ?? 'Undangan' }}

                        @if($invitation->event_date)

                            ·

                            {{ $invitation->event_date->format('d M Y') }}

                        @endif

                    </div>


                    <div class="inv-slug">

                        /u/{{ $invitation->slug }}

                    </div>

                </div>


                {{-- VIEWS --}}

                <div class="inv-views">

                    {{ number_format(
                        $invitation->views_count ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                    views

                </div>


                {{-- STATUS --}}

                <div class="inv-status-wrap">

                    @if($needsActivation)

                        @if($orderStatus === 'pending')

                            <span class="inv-status waiting">
                                Verifikasi
                            </span>

                        @elseif($orderStatus === 'rejected')

                            <span class="inv-status rejected">
                                Ditolak
                            </span>

                        @else

                            <span class="inv-status payment">
                                Belum Bayar
                            </span>

                        @endif

                    @elseif($invitation->is_published)

                        <span class="inv-status live">
                            Published
                        </span>

                    @else

                        <span class="inv-status">
                            Draft
                        </span>

                    @endif

                </div>


                {{-- ACTION --}}

                <div class="inv-actions">

                    @if($needsActivation)

                        <a
                            href="{{ route(
                                'orders.checkout',
                                $invitation
                            ) }}"
                            class="inv-primary"
                            style="
                                min-height:34px;
                                padding:0 13px;
                            "
                        >
                            {{ $orderStatus === 'pending'
                                ? 'Lihat Status'
                                : ($orderStatus === 'rejected'
                                    ? 'Kirim Ulang'
                                    : 'Lanjut Bayar')
                            }}
                        </a>

                    @else

                        @if($invitation->studio_template_id)
                            <a
                                href="{{ route('studio.invitation.open', $invitation) }}"
                                class="inv-primary"
                                style="min-height:34px;padding:0 13px;"
                            >
                                Edit Studio
                            </a>
                        @else
                            <a
                                href="{{ route('invitations.edit', $invitation) }}"
                                class="inv-primary"
                                style="min-height:34px;padding:0 13px;"
                            >
                                Edit
                            </a>
                        @endif


                        <a
                            href="{{ route('guests.index', $invitation) }}"
                            class="inv-light"
                        >
                            Tamu
                        </a>


                        @if($invitation->is_published)
                            <a
                                href="{{ $invitation->studio_template_id
                                    ? route('studio.invitation.preview', $invitation)
                                    : route('public.invitation', $invitation->slug)
                                }}"
                                target="_blank"
                                class="inv-light"
                            >
                                Preview
                            </a>
                        @endif

                    @endif

                </div>

            </div>


        @empty

            <div class="inv-empty">

                <div class="inv-empty-mark">
                    +
                </div>

                <div class="inv-empty-title">
                    Belum ada undangan
                </div>

                <div class="inv-empty-copy">
                    Buat undangan pertama untuk mulai.
                </div>

                <a
                    href="{{ route('invitations.create') }}"
                    class="inv-primary"
                >
                    Buat Undangan
                </a>

            </div>

        @endforelse

    </section>

</div>

@endsection
