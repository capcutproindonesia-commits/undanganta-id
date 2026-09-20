@extends('layouts.app')

@section('content')

@php
    $totalInvitations = $invitations->count();

    $publishedInvitations = $invitations
        ->where('is_published', true)
        ->count();

    $draftInvitations = $invitations
        ->where('is_published', false)
        ->count();

    $totalViews = $invitations->sum('views_count');
@endphp


<style>

    /* =========================================================
       DASHBOARD FINAL POLISH
       LOCAL ONLY — TIDAK MENGUBAH HALAMAN LAIN
    ========================================================= */

    .final-dashboard {
        width: 100%;
        max-width: 1160px;
        margin: 0 auto;
        padding: 34px 22px 52px;
    }


    /* =========================================================
       PAGE HEADER
    ========================================================= */

    .fd-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 26px;
    }

    .fd-overline {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-bottom: 7px;

        color: #86868b;

        font-size: 10px;
        font-weight: 650;

        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .fd-brand-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;
        background: #ff6d5a;
    }

    .fd-title {
        margin: 0;

        color: #1d1d1f;

        font-size: 34px;
        line-height: 1.08;

        font-weight: 700;

        letter-spacing: -.045em;
    }

    .fd-subtitle {
        max-width: 520px;

        margin: 8px 0 0;

        color: #86868b;

        font-size: 12px;
        line-height: 1.55;
    }


    /* =========================================================
       PRIMARY ACTION
    ========================================================= */

    .fd-primary {
        position: relative;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        min-height: 38px;
        padding: 0 15px;

        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;

        background:
            linear-gradient(
                180deg,
                #303033 0%,
                #111113 100%
            );

        color: #fff;

        font-size: 11px;
        font-weight: 650;

        line-height: 1;
        white-space: nowrap;

        cursor: pointer;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.16),
            0 4px 12px rgba(0,0,0,.12);

        transition:
            transform .16s ease,
            box-shadow .16s ease,
            background .16s ease;
    }

    .fd-primary::before {
        content: "";

        position: absolute;

        top: 2px;
        left: 15%;
        right: 15%;

        height: 7px;

        border-radius: 999px;

        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.16),
                rgba(255,255,255,0)
            );

        pointer-events: none;
    }

    .fd-primary:hover {
        transform: translateY(-1px);

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

    .fd-primary:active {
        transform: scale(.97);
    }

    .fd-primary svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        fill: none;
    }


    /* =========================================================
       SUMMARY
    ========================================================= */

    .fd-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 12px;

        margin-bottom: 24px;
    }

    .fd-stat {
        position: relative;

        min-width: 0;

        padding: 17px 18px 16px;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #fff;

        overflow: hidden;
    }

    .fd-stat::after {
        content: "";

        position: absolute;

        left: 0;
        right: 0;
        bottom: 0;

        height: 2px;

        background: transparent;
    }

    .fd-stat.live::after {
        background: #34c759;
    }

    .fd-stat-label {
        margin-bottom: 9px;

        color: #8e8e93;

        font-size: 10px;
        font-weight: 650;
    }

    .fd-stat-value {
        color: #1d1d1f;

        font-size: 26px;
        line-height: 1;

        font-weight: 700;

        letter-spacing: -.04em;
    }

    .fd-stat-note {
        margin-top: 7px;

        color: #aeaeb2;

        font-size: 9px;
    }


    /* =========================================================
       DASHBOARD LAYOUT
    ========================================================= */

    .fd-layout {
        display: grid;
        grid-template-columns: 210px minmax(0,1fr);

        gap: 20px;

        align-items: start;
    }


    /* =========================================================
       SIDEBAR
    ========================================================= */

    .fd-sidebar {
        padding: 8px;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #fff;
    }

    .fd-sidebar-label {
        padding: 8px 10px 6px;

        color: #aeaeb2;

        font-size: 9px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .fd-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;

        min-height: 40px;

        padding: 0 10px;

        border-radius: 9px;

        color: #626267;

        font-size: 11px;
        font-weight: 600;

        transition:
            background .15s ease,
            color .15s ease;
    }

    .fd-nav-link:hover {
        background: #f7f7f8;
        color: #1d1d1f;
    }

    .fd-nav-link.active {
        background: #f2f2f3;
        color: #1d1d1f;
    }

    .fd-nav-icon {
        width: 24px;
        height: 24px;

        display: grid;
        place-items: center;

        flex: 0 0 24px;

        border-radius: 7px;

        color: #8e8e93;
    }

    .fd-nav-link.active .fd-nav-icon {
        color: #1d1d1f;
        background: #fff;

        box-shadow:
            0 1px 3px rgba(0,0,0,.06);
    }

    .fd-nav-icon svg {
        width: 15px;
        height: 15px;

        stroke: currentColor;
        fill: none;

        stroke-width: 1.7;
        stroke-linecap: round;
        stroke-linejoin: round;
    }


    /* =========================================================
       PANEL
    ========================================================= */

    .fd-panel {
        overflow: hidden;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #fff;
    }

    .fd-panel-head {
        min-height: 60px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 16px;

        padding: 0 17px;

        border-bottom: 1px solid #eeeeef;
    }

    .fd-panel-title {
        color: #1d1d1f;

        font-size: 14px;
        font-weight: 700;
    }

    .fd-panel-subtitle {
        margin-top: 4px;

        color: #a1a1a6;

        font-size: 10px;
    }

    .fd-panel-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;

        color: #6e6e73;

        font-size: 10px;
        font-weight: 600;
    }

    .fd-panel-link:hover {
        color: #1d1d1f;
    }

    .fd-panel-link svg {
        width: 12px;
        height: 12px;

        stroke: currentColor;
        fill: none;
    }


    /* =========================================================
       INVITATION ROW
    ========================================================= */

    .fd-row {
        display: grid;

        grid-template-columns:
            52px
            minmax(0,1fr)
            92px
            86px
            auto;

        align-items: center;

        gap: 14px;

        min-height: 80px;

        padding: 13px 17px;

        border-bottom: 1px solid #f0f0f2;

        transition: background .14s ease;
    }

    .fd-row:last-child {
        border-bottom: 0;
    }

    .fd-row:hover {
        background: #fbfbfc;
    }

    .fd-cover {
        width: 52px;
        height: 52px;

        overflow: hidden;

        display: grid;
        place-items: center;

        border: 1px solid #e5e5e7;
        border-radius: 11px;

        background: #f5f5f7;

        color: #8e8e93;

        font-size: 10px;
        font-weight: 700;
    }

    .fd-cover img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .fd-info {
        min-width: 0;
    }

    .fd-name {
        overflow: hidden;

        margin-bottom: 4px;

        color: #1d1d1f;

        font-size: 13px;
        font-weight: 650;

        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .fd-meta {
        overflow: hidden;

        color: #8e8e93;

        font-size: 10px;

        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .fd-views {
        color: #6e6e73;

        font-size: 9px;
        line-height: 1.35;
    }

    .fd-views strong {
        display: block;

        color: #3a3a3c;

        font-size: 11px;
        font-weight: 650;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .fd-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;

        width: max-content;

        padding: 5px 8px;

        border-radius: 999px;

        background: #f2f2f3;

        color: #77777c;

        font-size: 8px;
        font-weight: 700;
    }

    .fd-status::before {
        content: "";

        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: #aeaeb2;
    }

    .fd-status.live {
        background: #edf7ef;
        color: #248a3d;
    }

    .fd-status.live::before {
        background: #34c759;
    }


    /* =========================================================
       ACTION BUTTONS
    ========================================================= */

    .fd-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        gap: 6px;

        flex-wrap: wrap;
    }

    .fd-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-height: 34px;
        padding: 0 12px;

        border: 1px solid rgba(0,0,0,.08);
        border-radius: 999px;

        background:
            linear-gradient(
                180deg,
                #fff,
                #f5f5f7
            );

        color: #3a3a3c;

        font-size: 10px;
        font-weight: 600;

        white-space: nowrap;

        box-shadow:
            inset 0 1px 0 #fff,
            0 2px 5px rgba(0,0,0,.035);

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    .fd-action:hover {
        transform: translateY(-1px);

        box-shadow:
            0 4px 9px rgba(0,0,0,.06);
    }

    .fd-action.primary {
        position: relative;

        border-color: rgba(255,255,255,.08);

        background:
            linear-gradient(
                180deg,
                #303033,
                #131315
            );

        color: #fff;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.14),
            0 4px 10px rgba(0,0,0,.10);
    }

    .fd-action.primary::before {
        content: "";

        position: absolute;

        top: 2px;
        left: 20%;
        right: 20%;

        height: 6px;

        border-radius: 999px;

        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.13),
                rgba(255,255,255,0)
            );
    }


    .fd-delete-form {
        margin: 0;
        display: inline-flex;
    }

    .fd-action.danger {
        border-color: #f0c9c5;

        background:
            linear-gradient(
                180deg,
                #fff,
                #fff7f6
            );

        color: #b42318;

        cursor: pointer;
    }

    .fd-action.danger:hover {
        border-color: #e5aaa4;

        background:
            linear-gradient(
                180deg,
                #fff8f7,
                #fff0ee
            );

        box-shadow:
            0 4px 9px rgba(180,35,24,.08);
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .fd-empty {
        padding: 52px 20px;

        text-align: center;
    }

    .fd-empty-icon {
        width: 48px;
        height: 48px;

        display: grid;
        place-items: center;

        margin: 0 auto 14px;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #f7f7f8;

        color: #8e8e93;
    }

    .fd-empty-icon svg {
        width: 20px;
        height: 20px;

        stroke: currentColor;
        fill: none;
    }

    .fd-empty-title {
        margin-bottom: 5px;

        color: #1d1d1f;

        font-size: 13px;
        font-weight: 700;
    }

    .fd-empty-copy {
        margin-bottom: 16px;

        color: #8e8e93;

        font-size: 10px;
    }



    .final-dashboard a:focus-visible,
    .final-dashboard button:focus-visible {
        outline: 3px solid rgba(29,29,31,.12);
        outline-offset: 3px;
    }

    @media(prefers-reduced-motion: reduce) {
        .fd-primary,
        .fd-action,
        .fd-nav-link,
        .fd-row {
            transition: none;
        }

        .fd-primary:hover,
        .fd-action:hover {
            transform: none;
        }
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media(max-width: 900px) {

        .fd-summary {
            grid-template-columns:
                repeat(2,minmax(0,1fr));
        }

        .fd-layout {
            grid-template-columns: 1fr;
        }

        .fd-sidebar {
            display: flex;
            align-items: center;

            gap: 5px;

            overflow-x: auto;

            scrollbar-width: none;
        }

        .fd-sidebar::-webkit-scrollbar {
            display: none;
        }

        .fd-sidebar-label {
            display: none;
        }

        .fd-nav-link {
            flex: 0 0 auto;

            border: 1px solid #e5e5e7;
        }

        .fd-row {
            grid-template-columns:
                52px
                minmax(0,1fr)
                auto;
        }

        .fd-views {
            display: none;
        }

        .fd-status-wrap {
            grid-column: 2;
        }

        .fd-actions {
            grid-column: 3;
            grid-row: 1 / span 2;
        }

    }


    @media(max-width: 640px) {

        .final-dashboard {
            padding: 22px 14px 38px;
        }

        .fd-header {
            align-items: flex-start;
            flex-direction: column;

            margin-bottom: 22px;
        }

        .fd-title {
            font-size: 28px;
        }

        .fd-subtitle {
            font-size: 12px;
        }

        .fd-summary {
            grid-template-columns:
                repeat(2,minmax(0,1fr));

            gap: 9px;
        }

        .fd-stat {
            padding: 14px;
        }

        .fd-stat-value {
            font-size: 22px;
        }

        .fd-row {
            grid-template-columns:
                46px
                minmax(0,1fr);

            gap: 12px;

            padding: 13px 14px;
        }

        .fd-cover {
            width: 46px;
            height: 46px;
        }

        .fd-status-wrap {
            grid-column: 2;
        }

        .fd-actions {
            grid-column: 1 / -1;
            grid-row: auto;

            justify-content: flex-start;

            padding-left: 58px;
        }

        .fd-panel-head {
            padding: 0 14px;
        }

    }

</style>


<div class="final-dashboard">


    {{-- PAGE HEADER --}}

    <header class="fd-header">

        <div>

            <div class="fd-overline">
                <span class="fd-brand-dot"></span>
                UNDANGANTA.ID
            </div>

            <h1 class="fd-title">
                Dashboard
            </h1>

            <p class="fd-subtitle">
                Kelola undangan, tamu, dan aktivitas terbaru dari satu tempat.
            </p>

        </div>


        <a
            href="{{ route('invitations.create') }}"
            class="fd-primary"
        >
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
            </svg>

            Buat Undangan
        </a>

    </header>


    {{-- SUMMARY --}}

    <section class="fd-summary">

        <div class="fd-stat">

            <div class="fd-stat-label">
                Total Undangan
            </div>

            <div class="fd-stat-value">
                {{ $totalInvitations }}
            </div>

            <div class="fd-stat-note">
                Semua undangan
            </div>

        </div>


        <div class="fd-stat live">

            <div class="fd-stat-label">
                Published
            </div>

            <div class="fd-stat-value">
                {{ $publishedInvitations }}
            </div>

            <div class="fd-stat-note">
                Sedang aktif
            </div>

        </div>


        <div class="fd-stat">

            <div class="fd-stat-label">
                Draft
            </div>

            <div class="fd-stat-value">
                {{ $draftInvitations }}
            </div>

            <div class="fd-stat-note">
                Belum dipublish
            </div>

        </div>


        <div class="fd-stat">

            <div class="fd-stat-label">
                Total Views
            </div>

            <div class="fd-stat-value">
                {{ number_format(
                    $totalViews,
                    0,
                    ',',
                    '.'
                ) }}
            </div>

            <div class="fd-stat-note">
                Semua kunjungan
            </div>

        </div>

    </section>


    {{-- MAIN --}}

    <div class="fd-layout">


        {{-- SIDEBAR --}}

        <aside class="fd-sidebar">

            <div class="fd-sidebar-label">
                Workspace
            </div>


            <a
                href="{{ route('dashboard') }}"
                class="fd-nav-link active"
            >
                <span class="fd-nav-icon">

                    <svg viewBox="0 0 24 24">
                        <path d="M4 10.5L12 4l8 6.5"></path>
                        <path d="M6 9.5V20h12V9.5"></path>
                    </svg>

                </span>

                Dashboard
            </a>


            <a
                href="{{ route('invitations.index') }}"
                class="fd-nav-link"
            >
                <span class="fd-nav-icon">

                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="5" width="16" height="14" rx="2"></rect>
                        <path d="M8 9h8"></path>
                        <path d="M8 13h5"></path>
                    </svg>

                </span>

                Undangan Saya
            </a>


            <a
                href="{{ route('invitations.create') }}"
                class="fd-nav-link"
            >
                <span class="fd-nav-icon">

                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14"></path>
                        <path d="M5 12h14"></path>
                    </svg>

                </span>

                Buat Undangan
            </a>

        </aside>


        {{-- INVITATIONS PANEL --}}

        <section class="fd-panel">

            <div class="fd-panel-head">

                <div>

                    <div class="fd-panel-title">
                        Undangan Saya
                    </div>

                    <div class="fd-panel-subtitle">
                        Undangan terbaru yang sedang kamu kelola.
                    </div>

                </div>


                <a
                    href="{{ route('invitations.index') }}"
                    class="fd-panel-link"
                >
                    Lihat semua

                    <svg viewBox="0 0 24 24">
                        <path d="M9 6l6 6-6 6"></path>
                    </svg>
                </a>

            </div>


            @forelse($invitations->take(5) as $i)

                <div class="fd-row">


                    {{-- COVER --}}

                    <div class="fd-cover">

                        @if($i->cover_path)

                            <img
                                src="{{ url('/storage/' . $i->cover_path) }}"
                                alt="{{ $i->title }}"
                            >

                        @else

                            {{ strtoupper(
                                substr(
                                    $i->groom_name ?? 'U',
                                    0,
                                    1
                                )
                            ) }}

                            {{ strtoupper(
                                substr(
                                    $i->bride_name ?? '',
                                    0,
                                    1
                                )
                            ) }}

                        @endif

                    </div>


                    {{-- INFO --}}

                    <div class="fd-info">

                        <div class="fd-name">
                            {{ $i->groom_name }}
                            &
                            {{ $i->bride_name }}
                        </div>

                        <div class="fd-meta">

                            {{ $i->title }}

                            @if($i->event_date)

                                ·
                                {{ $i->event_date->format('d M Y') }}

                            @endif

                        </div>

                    </div>


                    {{-- VIEWS --}}

                    <div class="fd-views">

                        <strong>
                            {{ number_format(
                                $i->views_count ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>

                        views

                    </div>


                    {{-- STATUS --}}

                    <div class="fd-status-wrap">

                        @if($i->is_published)

                            <span class="fd-status live">
                                Published
                            </span>

                        @else

                            <span class="fd-status">
                                Draft
                            </span>

                        @endif

                    </div>


                    {{-- ACTIONS --}}

                    <div class="fd-actions">

                        <a
                            href="{{ route(
                                'invitations.edit',
                                $i
                            ) }}"
                            class="fd-action primary"
                        >
                            Edit
                        </a>


                        <a
                            href="{{ route(
                                'guests.index',
                                $i
                            ) }}"
                            class="fd-action"
                        >
                            Tamu
                        </a>


                        @if($i->is_published)

                            <a
                                href="{{ route(
                                    'public.invitation',
                                    $i->slug
                                ) }}"
                                target="_blank"
                                class="fd-action"
                            >
                                Preview
                            </a>

                        @endif


                        <form
                            method="POST"
                            action="{{ route(
                                'invitations.destroy',
                                $i
                            ) }}"
                            class="fd-delete-form"
                            onsubmit="return confirm('Hapus undangan ini? Semua data dan media terkait akan dihapus permanen.');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="fd-action danger"
                            >
                                Hapus
                            </button>
                        </form>

                    </div>

                </div>


            @empty

                <div class="fd-empty">

                    <div class="fd-empty-icon">

                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>

                    </div>

                    <div class="fd-empty-title">
                        Belum ada undangan
                    </div>

                    <div class="fd-empty-copy">
                        Buat undangan pertamamu untuk mulai.
                    </div>

                    <a
                        href="{{ route('invitations.create') }}"
                        class="fd-primary"
                    >
                        Buat Undangan
                    </a>

                </div>

            @endforelse

        </section>

    </div>

</div>

@endsection