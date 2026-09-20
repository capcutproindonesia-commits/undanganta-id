@php
    $isHome = request()->routeIs('home');

    $pageTitle = $title
        ?? ($isHome
            ? 'UNDANGANTA.ID — Undangan Digital Personal'
            : 'UNDANGANTA.ID');

    $pageDescription = $description
        ?? ($isHome
            ? 'Buat undangan digital personal dengan tema pilihan, RSVP, buku tamu, guest photo, amplop digital, dan QR check-in dalam satu tempat.'
            : 'UNDANGANTA.ID');

    $canonicalUrl = url()->current();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ $pageTitle }}</title>

    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="{{ $isHome ? 'index,follow' : 'noindex,nofollow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if($isHome)
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="UNDANGANTA.ID">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">

        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">

        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'UNDANGANTA.ID',
                'url' => url('/'),
                'description' => $pageDescription,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;

            background: #f5f5f7;
            color: #1d1d1f;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .nav {
            width: 100%;
            height: 64px;

            background: #ffffff;

            border-bottom:
                1px solid #dedee3;

            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-inner {
            width: 100%;
            max-width: 1120px;
            height: 100%;

            margin: 0 auto;
            padding: 0 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }

        .brand {
            flex-shrink: 0;

            font-size: 20px;
            font-weight: 800;

            letter-spacing: -.045em;

            color: #1d1d1f;
        }

        .brand b {
            color: #ff6d5a;
        }

        .nav-menu {
            display: flex;
            align-items: center;

            gap: 16px;

            font-size: 13px;
            font-weight: 500;
        }

        .nav-menu form {
            margin: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | HOME NAV — scoped, app pages stay unchanged
        |--------------------------------------------------------------------------
        */

        .nav.home-nav {
            background: rgba(255,255,255,.88);
            border-bottom-color: rgba(23,23,25,.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .home-nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .home-nav-links a {
            color: #55555b;
            font-size: 12px;
            font-weight: 600;
        }

        .home-nav-links a:hover {
            color: #1d1d1f;
        }

        .nav a:focus-visible,
        .nav button:focus-visible {
            outline: 3px solid rgba(255,109,90,.22);
            outline-offset: 3px;
        }

        /*
        |--------------------------------------------------------------------------
        | PAGE WRAPPER
        |--------------------------------------------------------------------------
        */

        .wrap {
            width: calc(100% - 40px);
            max-width: 1120px;

            margin: 0 auto;

            padding:
                30px 0 50px;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .card {
            width: 100%;
            min-width: 0;

            background: #ffffff;

            border:
                1px solid #dedee3;

            border-radius: 16px;

            padding: 20px;

            overflow: hidden;

            box-shadow:
                0 1px 2px rgba(0,0,0,.025);
        }

        /*
        |--------------------------------------------------------------------------
        | GRID
        |--------------------------------------------------------------------------
        */

        .grid {
            width: 100%;

            display: grid;

            gap: 16px;
        }

        .grid3 {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

        .grid > * {
            min-width: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 38px;

            padding:
                0 14px;

            border: 0;
            border-radius: 10px;

            background: #1d1d1f;
            color: #ffffff;

            font-size: 13px;
            font-weight: 600;

            line-height: 1;

            cursor: pointer;

            white-space: nowrap;
        }

        .btn:hover {
            background: #333336;
        }

        .btn.alt {
            background: #ffffff;
            color: #1d1d1f;

            border:
                1px solid #d6d6db;
        }

        .btn.alt:hover {
            background: #f7f7f8;
        }

        .btn.danger {
            background: #b42318;
            color: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | EDITOR BUTTONS
        |--------------------------------------------------------------------------
        */

        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 36px;

            padding:
                0 13px;

            border:
                1px solid #d6d6db;

            border-radius: 10px;

            background: #ffffff;
            color: #1d1d1f;

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
        }

        .secondary-btn:hover {
            background: #f7f7f8;
        }

        .danger-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 36px;

            padding:
                0 13px;

            border:
                1px solid #e5b7b3;

            border-radius: 10px;

            background: #ffffff;
            color: #b42318;

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .field {
            margin:
                0 0 16px;
        }

        .field label {
            display: block;

            margin-bottom: 7px;

            color: #3a3a3c;

            font-size: 12px;
            font-weight: 600;
        }

        .input,
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="url"],
        input[type="number"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            max-width: 100%;

            min-height: 42px;

            padding:
                10px 12px;

            border:
                1px solid #d6d6db;

            border-radius: 10px;

            outline: none;

            background: #ffffff;
            color: #1d1d1f;
        }

        textarea {
            min-height: 100px;

            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #99999f;

            box-shadow:
                0 0 0 3px
                rgba(0,0,0,.035);
        }

        /*
        |--------------------------------------------------------------------------
        | TEXT
        |--------------------------------------------------------------------------
        */

        h1,
        h2,
        h3,
        p {
            max-width: 100%;
        }

        h1,
        h2,
        h3 {
            overflow-wrap: break-word;
        }

        .muted {
            color: #77777d;
        }

        .eyebrow {
            margin-bottom: 6px;

            color: #86868b;

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .08em;

            text-transform: uppercase;
        }

        .page-title {
            margin: 0;

            font-size: 32px;
            line-height: 1.1;

            letter-spacing: -.035em;
        }

        .page-description {
            margin:
                8px 0 0;

            color: #77777d;

            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | PILL
        |--------------------------------------------------------------------------
        */

        .pill {
            display: inline-flex;
            align-items: center;

            padding:
                5px 8px;

            border:
                1px solid #e1e1e5;

            border-radius: 999px;

            background: #f5f5f7;

            font-size: 11px;
        }

        /*
        |--------------------------------------------------------------------------
        | FLASH
        |--------------------------------------------------------------------------
        */

        .flash {
            width: 100%;

            padding:
                11px 13px;

            border:
                1px solid #b9dfc5;

            border-radius: 10px;

            background: #effaf3;
            color: #176b35;

            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | ERRORS
        |--------------------------------------------------------------------------
        */

        .validation-errors {
            width: 100%;

            padding:
                13px;

            border:
                1px solid #efb8b4;

            border-radius: 10px;

            background: #fff3f2;
            color: #8a1c13;

            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .hero {
            width: 100%;

            padding:
                80px 20px;

            text-align: center;

            background: #ffffff;

            border-bottom:
                1px solid #e1e1e5;
        }

        .hero h1 {
            max-width: 900px;

            margin:
                0 auto 20px;

            font-size:
                clamp(42px, 7vw, 80px);

            line-height: .96;

            letter-spacing: -.06em;
        }

        .hero p {
            max-width: 650px;

            margin:
                0 auto 28px;

            color: #6e6e73;

            font-size: 17px;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE SAFETY
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;

            border-collapse: collapse;
        }

        img {
            max-width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLET
        |--------------------------------------------------------------------------
        */

        @media(max-width: 900px) {

            .grid3 {
                grid-template-columns:
                    repeat(2, minmax(0,1fr));
            }

        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media(max-width: 680px) {

            .nav {
                height: 60px;
            }

            .nav-inner {
                padding:
                    0 14px;
            }

            .brand {
                font-size: 18px;
            }

            .nav-menu {
                gap: 9px;

                font-size: 12px;
            }


            .home-nav-links {
                display: none;
            }


            .wrap {
                width:
                    calc(100% - 24px);

                padding:
                    20px 0 35px;
            }

            .grid3 {
                grid-template-columns:
                    1fr;
            }

            .card {
                padding: 16px;

                border-radius: 14px;
            }

            .page-title {
                font-size: 27px;
            }

        }

    </style>
</head>

<body>


<nav class="nav {{ $isHome ? 'home-nav' : '' }}">

    <div class="nav-inner">

        <a
            href="{{ route('home') }}"
            class="brand"
        >
            UNDANGANTA<b>.ID</b>
        </a>


        <div class="nav-menu">

            @if($isHome)
                <div class="home-nav-links" aria-label="Navigasi landing page">
                    <a href="#tema">Tema</a>
                    <a href="#harga">Harga</a>
                    <a href="#cara-kerja">Cara kerja</a>
                </div>
            @endif

            @auth

                <a href="{{ route('dashboard') }}">
                    Dashboard
                </a>


                @if(auth()->user()->is_admin)

                    <a href="{{ route('admin.index') }}">
                        Admin
                    </a>

                @endif


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn alt"
                    >
                        Keluar
                    </button>

                </form>


            @else

                <a href="{{ route('login') }}">
                    Masuk
                </a>


                <a
                    href="{{ route('register') }}"
                    class="btn"
                >
                    Mulai
                </a>

            @endauth

        </div>

    </div>

</nav>


@if(
    session('ok')
    && !request()->routeIs('orders.checkout')
)

    <div
        class="wrap"
        style="padding-bottom:0;"
    >

        <div class="flash">
            {{ session('ok') }}
        </div>

    </div>

@endif


@if(
    $errors->any()
    && !request()->routeIs('orders.checkout')
)

    <div
        class="wrap"
        style="padding-bottom:0;"
    >

        <div class="validation-errors">

            <strong>
                Ada data yang perlu diperbaiki.
            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    </div>

@endif


@yield('content')


@isset($slot)

    {{ $slot }}

@endisset


</body>
</html>