@php
    $sections = $invitation->sections ?? [];

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE GALLERY
    |--------------------------------------------------------------------------
    | Mendukung format gallery lama berupa string path dan format baru
    | berupa array/object-like entry. Hasil akhirnya selalu string path.
    */
    $gallery = collect($invitation->gallery ?? [])
        ->map(function ($item) {
            if (is_string($item)) {
                return $item;
            }

            if (is_array($item)) {
                return $item['path']
                    ?? $item['url']
                    ?? $item['file']
                    ?? null;
            }

            if (is_object($item)) {
                return $item->path
                    ?? $item->url
                    ?? $item->file
                    ?? null;
            }

            return null;
        })
        ->filter(function ($path) {
            return is_string($path) && trim($path) !== '';
        })
        ->values()
        ->all();

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE GIFT ACCOUNTS
    |--------------------------------------------------------------------------
    | Pastikan section gift selalu berbentuk array agar aman di-loop.
    */
    $giftAccounts = collect($invitation->gift_accounts ?? [])
        ->map(function ($gift) {
            if (is_object($gift)) {
                $gift = (array) $gift;
            }

            return is_array($gift) ? $gift : null;
        })
        ->filter()
        ->values()
        ->all();

    $galleryOne = $gallery[0] ?? null;
    $galleryTwo = $gallery[1] ?? null;

    $defaultSectionOrder = [
        'countdown',
        'quote',
        'groom',
        'bride',
        'story',
        'gallery',
        'event',
        'rsvp',
        'gift',
        'guest_photo',
        'wishes',
    ];

    $sectionOrder = collect(
        $sections['order']
        ?? $defaultSectionOrder
    )
        ->filter(
            fn ($key) =>
                in_array(
                    $key,
                    $defaultSectionOrder,
                    true
                )
        )
        ->unique()
        ->values()
        ->all();

    foreach ($defaultSectionOrder as $key) {
        if (!in_array($key, $sectionOrder, true)) {
            $sectionOrder[] = $key;
        }
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <title>
        {{ $invitation->groom_name }}
        &
        {{ $invitation->bride_name }}
    </title>

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        *,
        *::before,
        *::after {
            box-sizing:border-box;
        }

        html {
            scroll-behavior:smooth;
            background:#18130f;
        }

        body {
            margin:0;

            overflow-x:hidden;

            background:#e9dfd2;
            color:#241b15;

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Helvetica,
                Arial,
                sans-serif;

            -webkit-font-smoothing:antialiased;
        }

        body.locked {
            overflow:hidden;
        }

        a {
            color:inherit;
            text-decoration:none;
        }

        button,
        input,
        select,
        textarea {
            font:inherit;
        }

        img {
            max-width:100%;
        }


        /*
        |--------------------------------------------------------------------------
        | VARIABLES
        |--------------------------------------------------------------------------
        */

        :root {
            --dark:#201711;
            --dark-soft:#2c2119;

            --brown:#5a3518;
            --brown-deep:#3c2413;

            --cream:#e9dfd2;
            --cream-2:#f2ebe1;
            --paper:#f8f3eb;

            --muted:#887568;

            --line:
                rgba(
                    63,
                    43,
                    31,
                    .16
                );

            --ease:
                cubic-bezier(
                    .22,
                    1,
                    .36,
                    1
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GLOBAL
        |--------------------------------------------------------------------------
        */

        .modern-shell {
            width:100%;
            overflow:hidden;
        }

        .modern-section {
            position:relative;

            width:100%;
            max-width:880px;

            margin:0 auto;

            padding:84px 26px;
        }

        .section-number {
            display:flex;
            align-items:center;
            justify-content:center;

            gap:10px;

            margin-bottom:17px;

            color:#8b7464;

            font-size:9px;
            font-weight:600;

            letter-spacing:.18em;
            text-transform:uppercase;
        }

        .section-number::before,
        .section-number::after {
            content:"";

            width:30px;
            height:1px;

            background:currentColor;

            opacity:.45;
        }

        .section-title {
            margin:0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    38px,
                    8vw,
                    68px
                );

            line-height:.94;

            font-weight:400;

            letter-spacing:-.055em;

            text-align:center;
        }

        .section-copy {
            max-width:600px;

            margin:22px auto 0;

            color:#6f5d50;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:14px;
            line-height:1.85;

            text-align:center;
        }


        /*
        |--------------------------------------------------------------------------
        | REVEAL SYSTEM
        |--------------------------------------------------------------------------
        */

        [data-reveal] {
            opacity:0;

            transform:
                translate3d(
                    0,
                    34px,
                    0
                );

            transition:
                opacity .9s var(--ease),
                transform .9s var(--ease);
        }

        [data-reveal="fade"] {
            transform:none;
        }

        [data-reveal="left"] {
            transform:
                translate3d(
                    -45px,
                    0,
                    0
                );
        }

        [data-reveal="right"] {
            transform:
                translate3d(
                    45px,
                    0,
                    0
                );
        }

        [data-reveal="scale"] {
            transform:
                scale(.94);
        }

        [data-reveal].is-visible {
            opacity:1;
            transform:none;
        }

        .reveal-delay-1 {
            transition-delay:.08s;
        }

        .reveal-delay-2 {
            transition-delay:.16s;
        }

        .reveal-delay-3 {
            transition-delay:.24s;
        }

        .reveal-delay-4 {
            transition-delay:.32s;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        .modern-button {
            position:relative;

            display:inline-flex;
            align-items:center;
            justify-content:center;

            min-height:43px;

            padding:0 20px;

            border:1px solid transparent;
            border-radius:999px;

            background:#542f13;
            color:#fff;

            font-size:9px;
            font-weight:700;

            letter-spacing:.08em;
            text-transform:uppercase;

            cursor:pointer;

            transition:
                transform .18s ease,
                background .18s ease,
                box-shadow .18s ease;
        }

        .modern-button:hover {
            transform:
                translateY(-2px);

            background:#3e220e;

            box-shadow:
                0 10px 22px
                rgba(47,26,12,.18);
        }

        .modern-button.dark {
            background:#1d1713;
        }

        .modern-button.light {
            border-color:
                rgba(
                    255,
                    255,
                    255,
                    .36
                );

            background:
                rgba(
                    255,
                    255,
                    255,
                    .08
                );

            color:#fff;

            backdrop-filter:
                blur(7px);
        }


        /*
        |--------------------------------------------------------------------------
        | OPENING
        |--------------------------------------------------------------------------
        */

        .opening {
            position:fixed;

            inset:0;

            z-index:9999;

            display:grid;
            place-items:center;

            min-height:100svh;

            background:#18130f;

            transition:
                opacity .8s var(--ease),
                visibility .8s;
        }

        .opening.hidden {
            opacity:0;
            visibility:hidden;
            pointer-events:none;
        }

        .opening-bg {
            position:absolute;
            inset:0;

            overflow:hidden;
        }

        .opening-bg img {
            width:100%;
            height:100%;

            display:block;

            object-fit:cover;

            filter:
                grayscale(1)
                contrast(1.05)
                brightness(.64);

            animation:
                openingZoom
                11s
                ease-out
                forwards;
        }

        .opening-bg::after {
            content:"";

            position:absolute;
            inset:0;

            background:
                linear-gradient(
                    180deg,
                    rgba(15,10,7,.28),
                    rgba(18,12,8,.54) 55%,
                    rgba(16,10,7,.80)
                );
        }

        .opening-no-cover {
            position:absolute;
            inset:0;

            background:
                radial-gradient(
                    circle at 50% 30%,
                    #493323,
                    #17110d 70%
                );
        }

        .opening-content {
            position:relative;

            z-index:2;

            width:100%;
            max-width:520px;

            padding:40px 24px;

            color:#fff;

            text-align:center;
        }

        .opening-kicker {
            margin-bottom:23px;

            font-size:9px;
            font-weight:600;

            letter-spacing:.32em;
            text-transform:uppercase;

            opacity:0;

            animation:
                openingFadeUp
                .8s
                .15s
                var(--ease)
                forwards;
        }

        .opening-title {
            margin:0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    52px,
                    15vw,
                    84px
                );

            line-height:.78;

            font-weight:400;

            letter-spacing:-.075em;
        }

        .opening-name {
            display:block;

            opacity:0;

            transform:
                translateY(34px);

            animation:
                openingName
                1s
                var(--ease)
                forwards;
        }

        .opening-name.first {
            animation-delay:.28s;
        }

        .opening-name.second {
            animation-delay:.62s;
        }

        .opening-and {
            display:block;

            margin:12px 0;

            font-family:
                Georgia,
                serif;

            font-size:.33em;
            line-height:1;

            font-style:italic;

            opacity:0;

            animation:
                openingFadeUp
                .7s
                .48s
                var(--ease)
                forwards;
        }

        .opening-date {
            margin-top:27px;

            font-size:9px;
            font-weight:650;

            letter-spacing:.22em;
            text-transform:uppercase;

            opacity:0;

            animation:
                openingFadeUp
                .8s
                .82s
                var(--ease)
                forwards;
        }

        .opening-guest {
            margin:28px auto 0;

            max-width:320px;

            color:
                rgba(
                    255,
                    255,
                    255,
                    .76
                );

            font-family:Georgia,serif;

            font-size:12px;
            line-height:1.7;

            opacity:0;

            animation:
                openingFadeUp
                .8s
                .95s
                var(--ease)
                forwards;
        }

        .opening-action {
            margin-top:25px;

            opacity:0;

            animation:
                openingFadeUp
                .8s
                1.08s
                var(--ease)
                forwards;
        }


        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .hero {
            position:relative;

            min-height:100svh;

            display:grid;
            align-items:end;

            overflow:hidden;

            background:#201711;
            color:#fff;
        }

        .hero-image {
            position:absolute;
            inset:-6%;

            will-change:transform;
        }

        .hero-image img {
            width:100%;
            height:100%;

            display:block;

            object-fit:cover;

            filter:
                grayscale(.78)
                sepia(.12)
                contrast(1.05)
                brightness(.72);

            transform:
                scale(1.06);

            animation:
                heroBreath
                18s
                ease-in-out
                infinite
                alternate;
        }

        .hero-image::after {
            content:"";

            position:absolute;
            inset:0;

            background:
                linear-gradient(
                    180deg,
                    rgba(16,11,8,.14),
                    rgba(18,12,8,.16) 40%,
                    rgba(19,13,9,.88)
                );
        }

        .hero-empty {
            position:absolute;
            inset:0;

            background:
                radial-gradient(
                    circle at 50% 20%,
                    #55402e,
                    #1c1510 68%
                );
        }

        .hero-content {
            position:relative;

            z-index:3;

            width:100%;
            max-width:880px;

            margin:0 auto;

            padding:
                120px 27px
                66px;
        }

        .hero-topline {
            margin-bottom:25px;

            font-size:9px;
            font-weight:600;

            letter-spacing:.28em;
            text-transform:uppercase;

            opacity:0;

            transform:
                translateY(20px);
        }

        body.invitation-opened
        .hero-topline {
            animation:
                heroTextIn
                .8s
                .2s
                var(--ease)
                forwards;
        }

        .hero-title {
            max-width:680px;

            margin:0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    60px,
                    14vw,
                    114px
                );

            line-height:.76;

            font-weight:400;

            letter-spacing:-.075em;
        }

        .hero-title span {
            display:block;
        }

        .hero-word {
            opacity:0;

            transform:
                translateY(50px);
        }

        body.invitation-opened
        .hero-word.word-1 {
            animation:
                heroTextIn
                1s
                .30s
                var(--ease)
                forwards;
        }

        body.invitation-opened
        .hero-word.word-2 {
            animation:
                heroTextIn
                .8s
                .48s
                var(--ease)
                forwards;
        }

        body.invitation-opened
        .hero-word.word-3 {
            animation:
                heroTextIn
                1s
                .63s
                var(--ease)
                forwards;
        }

        .hero-amp {
            margin:
                13px 0 10px
                12%;

            font-size:.36em;
            font-style:italic;

            opacity:0;
        }

        .hero-bottom {
            display:flex;
            align-items:flex-end;
            justify-content:space-between;

            gap:24px;

            margin-top:42px;

            opacity:0;

            transform:
                translateY(20px);
        }

        body.invitation-opened
        .hero-bottom {
            animation:
                heroTextIn
                .8s
                .9s
                var(--ease)
                forwards;
        }

        .hero-date {
            font-size:10px;
            font-weight:600;

            letter-spacing:.16em;
            text-transform:uppercase;

            opacity:.82;
        }

        .hero-scroll {
            display:flex;
            align-items:center;

            gap:8px;

            font-size:8px;
            font-weight:600;

            letter-spacing:.14em;
            text-transform:uppercase;

            opacity:.66;
        }

        .hero-scroll::before {
            content:"";

            width:30px;
            height:1px;

            background:currentColor;
        }


        /*
        |--------------------------------------------------------------------------
        | COUNTDOWN
        |--------------------------------------------------------------------------
        */

        .countdown-section {
            background:var(--cream);
        }

        .date-display {
            margin-top:24px;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:16px;

            letter-spacing:.18em;

            text-align:center;
        }

        .countdown {
            display:grid;

            grid-template-columns:
                repeat(
                    4,
                    minmax(0,1fr)
                );

            max-width:640px;

            margin:40px auto 0;

            border-top:
                1px solid
                var(--line);

            border-bottom:
                1px solid
                var(--line);
        }

        .count-item {
            position:relative;

            padding:25px 8px;

            text-align:center;
        }

        .count-item:not(:last-child)::after {
            content:"";

            position:absolute;

            top:23%;
            right:0;
            bottom:23%;

            width:1px;

            background:var(--line);
        }

        .count-value {
            display:block;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    28px,
                    7vw,
                    46px
                );

            line-height:1;

            letter-spacing:-.04em;
        }

        .count-label {
            display:block;

            margin-top:8px;

            color:#887568;

            font-size:8px;
            font-weight:650;

            letter-spacing:.12em;
            text-transform:uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | QUOTE
        |--------------------------------------------------------------------------
        */

        .quote-section {
            position:relative;

            overflow:hidden;

            background:#201711;
            color:#efe5d9;
        }

        .quote-section .modern-section {
            max-width:760px;

            padding-top:86px;
            padding-bottom:86px;
        }

        .quote-mark {
            margin-bottom:22px;

            color:#98704d;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:70px;
            line-height:.55;

            text-align:center;
        }

        .quote-text {
            max-width:650px;

            margin:0 auto;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:
                clamp(
                    18px,
                    4vw,
                    26px
                );

            line-height:1.7;

            text-align:center;
            font-style:italic;

            color:#eadfd4;
        }


        /*
        |--------------------------------------------------------------------------
        | COUPLE
        |--------------------------------------------------------------------------
        */

        .couple-section {
            background:#f1e8dc;
        }

        .couple-layout {
            display:grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(0,1fr)
                );

            gap:10px;

            max-width:720px;

            margin:34px auto 0;
        }

        .couple-small {
            display:flex;
            flex-direction:column;
            justify-content:flex-end;

            padding:24px;

            min-height:0;
            aspect-ratio:4/5;

            background:#5a3518;
            color:#fff;
        }

        .couple-small-label {
            margin-bottom:auto;

            font-size:8px;
            font-weight:650;

            letter-spacing:.2em;
            text-transform:uppercase;

            opacity:.72;
        }

        .couple-initial {
            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    94px,
                    22vw,
                    155px
                );

            line-height:.72;

            letter-spacing:-.08em;
        }

        .couple-names {
            margin-top:18px;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:15px;
            line-height:1.5;
        }

        .couple-photo {
            position:relative;

            min-height:0;
            aspect-ratio:4/5;

            overflow:hidden;

            background:#33251c;
        }

        .couple-photo img {
            width:100%;
            height:100%;

            display:block;

            object-fit:cover;

            filter:
                grayscale(.82)
                contrast(1.06);

            transform:
                scale(1.04);

            transition:
                transform
                1.8s
                var(--ease);
        }

        .couple-photo.is-visible img {
            transform:
                scale(1);
        }

        .couple-photo-overlay {
            position:absolute;

            inset:auto 0 0;

            padding:
                60px 24px
                24px;

            color:#fff;

            background:
                linear-gradient(
                    transparent,
                    rgba(20,13,9,.82)
                );
        }

        .couple-photo-title {
            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:36px;

            letter-spacing:-.045em;
        }


        .couple-profile-info {
            margin-top:24px;
        }

        .couple-profile-name {
            margin:0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    34px,
                    6vw,
                    52px
                );

            line-height:.98;
            font-weight:400;
            letter-spacing:-.045em;
            overflow-wrap:anywhere;
        }

        .couple-profile-parent {
            margin-top:15px;
            color:#eadfd4;
            font-family:Georgia,"Times New Roman",serif;
            font-size:12px;
            line-height:1.68;
        }

        .couple-profile-origin {
            margin-top:10px;
            color:#c4ad9c;
            font-size:9px;
            font-weight:600;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .couple-profile-instagram {
            display:inline-flex;
            margin-top:16px;
            padding-bottom:3px;
            border-bottom:1px solid rgba(255,255,255,.34);
            color:#fff;
            font-size:10px;
            font-weight:700;
        }


        /*
        |--------------------------------------------------------------------------
        | STORY
        |--------------------------------------------------------------------------
        */

        .story-section {
            background:#d8c5ad;
        }

        .story-text {
            max-width:600px;

            margin:30px auto 0;

            color:#423228;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:14px;
            line-height:1.85;

            white-space:pre-line;

            text-align:center;
        }


        /*
        |--------------------------------------------------------------------------
        | GALLERY
        |--------------------------------------------------------------------------
        */

        .gallery-section {
            background:#201711;
            color:#efe5d9;
        }

        .gallery-section .section-number {
            color:#ad927e;
        }

        .gallery-grid {
            display:grid;

            grid-template-columns:
                repeat(
                    3,
                    minmax(0,1fr)
                );

            gap:6px;

            max-width:690px;

            margin:30px auto 0;
        }

        .gallery-item {
            position:relative;

            overflow:hidden;

            background:#30241c;
        }

        .gallery-item img {
            width:100%;

            aspect-ratio:4/5;

            display:block;

            object-fit:cover;

            filter:
                saturate(.78)
                contrast(1.03);

            transform:scale(1.05);

            transition:
                transform
                1.2s
                var(--ease);
        }

        .gallery-item.is-visible img {
            transform:scale(1);
        }


        /*
        |--------------------------------------------------------------------------
        | EVENT
        |--------------------------------------------------------------------------
        */

        .event-section {
            background:#e9dfd2;
        }

        .event-editorial {
            display:grid;

            grid-template-columns:
                .82fr
                1.18fr;

            gap:10px;

            max-width:720px;

            margin:34px auto 0;
        }

        .event-date-card {
            display:flex;
            flex-direction:column;
            justify-content:space-between;

            min-height:330px;

            padding:24px;

            background:#201711;
            color:#fff;
        }

        .event-mini-label {
            font-size:8px;
            font-weight:650;

            letter-spacing:.18em;
            text-transform:uppercase;

            opacity:.66;
        }

        .event-day-number {
            margin-top:auto;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    84px,
                    20vw,
                    140px
                );

            line-height:.7;

            letter-spacing:-.08em;
        }

        .event-date-bottom {
            margin-top:28px;

            font-family:
                Georgia,
                serif;

            font-size:14px;
            line-height:1.55;
        }

        .event-info-card {
            min-height:330px;

            display:flex;
            flex-direction:column;

            padding:26px;

            border:
                1px solid
                rgba(71,48,32,.20);

            background:#f6f0e7;
        }

        .event-title {
            margin:40px 0 0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    37px,
                    8vw,
                    62px
                );

            line-height:.94;

            font-weight:400;

            letter-spacing:-.055em;
        }

        .event-address {
            max-width:380px;

            margin-top:24px;

            color:#715f52;

            font-size:11px;
            line-height:1.7;
        }

        .event-actions {
            margin-top:auto;
            padding-top:30px;
        }


        /*
        |--------------------------------------------------------------------------
        | RSVP
        |--------------------------------------------------------------------------
        */

        .rsvp-section {
            background:#cbb79d;
        }

        .rsvp-card {
            width:100%;
            max-width:540px;

            margin:32px auto 0;
            padding:22px;

            background:#efe5d8;
        }

        .field-label {
            display:block;

            margin:
                16px 0
                7px;

            color:#665447;

            font-size:9px;
            font-weight:650;

            letter-spacing:.05em;

            text-transform:uppercase;
        }

        .field-label:first-child {
            margin-top:0;
        }

        .field {
            width:100%;
            min-height:45px;

            padding:11px 12px;

            border:0;

            border-bottom:
                1px solid
                rgba(61,42,29,.28);

            border-radius:0;

            background:transparent;
            color:#261b14;

            outline:none;

            font-size:12px;

            transition:
                border-color .2s ease,
                background .2s ease;
        }

        textarea.field {
            min-height:96px;
            resize:vertical;
        }

        .field:focus {
            border-bottom-color:#301e12;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .16
                );
        }

        .rsvp-action {
            margin-top:25px;
        }

        .success-message {
            max-width:570px;

            margin:30px auto 0;

            padding:13px 16px;

            border:
                1px solid
                rgba(58,108,64,.28);

            background:
                rgba(255,255,255,.35);

            color:#355c39;

            font-size:10px;

            text-align:center;
        }


        /*
        |--------------------------------------------------------------------------
        | GIFTS
        |--------------------------------------------------------------------------
        */

        .gift-section {
            background:#201711;
            color:#efe5d9;
        }

        .gift-section .section-number {
            color:#a78d79;
        }

        .gift-copy {
            color:#baa99c;
        }

        .gift-grid {
            display:grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(0,1fr)
                );

            gap:8px;

            max-width:600px;

            margin:32px auto 0;
        }

        .gift-card {
            padding:22px;

            border:
                1px solid
                rgba(
                    255,
                    255,
                    255,
                    .13
                );

            background:#2a1f18;
        }

        .gift-bank {
            margin-bottom:22px;

            color:#bda792;

            font-size:8px;
            font-weight:650;

            letter-spacing:.14em;
            text-transform:uppercase;
        }

        .gift-number {
            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:20px;

            letter-spacing:.02em;
        }

        .gift-name {
            margin-top:8px;

            color:#a99687;

            font-size:9px;
        }


        /*
        |--------------------------------------------------------------------------
        | GUEST PHOTO
        |--------------------------------------------------------------------------
        */

        .guest-photo-section {
            background:#eee4d7;
        }

        .guest-photo-grid {
            display:grid;

            grid-template-columns:
                repeat(
                    3,
                    minmax(0,1fr)
                );

            gap:5px;

            max-width:680px;

            margin:30px auto 0;
        }

        .guest-photo-item {
            overflow:hidden;
            background:#ddd0c0;
        }

        .guest-photo-item img {
            width:100%;

            aspect-ratio:1;

            display:block;

            object-fit:cover;

            transform:
                scale(1.07);

            transition:
                transform
                1.5s
                var(--ease);
        }

        .guest-photo-item.is-visible img {
            transform:scale(1);
        }


        /*
        |--------------------------------------------------------------------------
        | WISHES
        |--------------------------------------------------------------------------
        */

        .wishes-section {
            background:#efe8de;
        }

        .wish-list {
            max-width:620px;

            margin:32px auto 0;

            border-top:
                1px solid
                var(--line);
        }

        .wish-card {
            padding:22px 4px;

            border-bottom:
                1px solid
                var(--line);
        }

        .wish-name {
            margin-bottom:9px;

            color:#3a2b21;

            font-size:9px;
            font-weight:700;

            letter-spacing:.09em;
            text-transform:uppercase;
        }

        .wish-message {
            color:#625247;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:14px;
            line-height:1.75;
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSING
        |--------------------------------------------------------------------------
        */

        .closing {
            position:relative;

            min-height:76svh;

            display:grid;
            place-items:center;

            overflow:hidden;

            background:#201711;
            color:#fff;
        }

        .closing-image {
            position:absolute;
            inset:-7%;

            will-change:transform;
        }

        .closing-image img {
            width:100%;
            height:100%;

            object-fit:cover;

            filter:
                grayscale(.9)
                brightness(.48);

            transform:
                scale(1.06);
        }

        .closing-image::after {
            content:"";

            position:absolute;
            inset:0;

            background:
                rgba(
                    21,
                    14,
                    10,
                    .44
                );
        }

        .closing-content {
            position:relative;

            z-index:2;

            width:100%;
            max-width:620px;

            padding:70px 22px;

            text-align:center;
        }

        .closing-small {
            margin-bottom:20px;

            font-size:8px;
            font-weight:650;

            letter-spacing:.22em;
            text-transform:uppercase;

            opacity:.7;
        }

        .closing-title {
            margin:0;

            font-family:
                "Times New Roman",
                Georgia,
                serif;

            font-size:
                clamp(
                    52px,
                    14vw,
                    90px
                );

            line-height:.82;

            font-weight:400;

            letter-spacing:-.065em;
        }

        .closing-text {
            max-width:480px;

            margin:28px auto 0;

            color:
                rgba(
                    255,
                    255,
                    255,
                    .7
                );

            font-family:
                Georgia,
                serif;

            font-size:12px;
            line-height:1.8;
        }

        .footer {
            padding:22px;

            background:#18120e;
            color:#75665c;

            font-size:8px;
            font-weight:600;

            letter-spacing:.14em;

            text-align:center;
            text-transform:uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | MUSIC
        |--------------------------------------------------------------------------
        */

        .music-button {
            position:fixed;

            right:
                max(
                    16px,
                    env(
                        safe-area-inset-right
                    )
                );

            bottom:
                max(
                    16px,
                    env(
                        safe-area-inset-bottom
                    )
                );

            z-index:500;

            width:43px;
            height:43px;

            display:grid;
            place-items:center;

            border:
                1px solid
                rgba(
                    255,
                    255,
                    255,
                    .15
                );

            border-radius:50%;

            background:#2a1b11;
            color:#fff;

            font-size:13px;

            cursor:pointer;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,.22);

            transition:
                transform .2s ease;
        }

        .music-button.playing {
            animation:
                musicPulse
                2.8s
                ease-in-out
                infinite;
        }


        /*
        |--------------------------------------------------------------------------
        | MINI NAV
        |--------------------------------------------------------------------------
        */

        .mini-nav {
            position:fixed;

            left:50%;

            bottom:
                max(
                    16px,
                    env(
                        safe-area-inset-bottom
                    )
                );

            z-index:450;

            display:flex;

            transform:
                translateX(-50%);

            padding:4px;

            border:
                1px solid
                rgba(
                    255,
                    255,
                    255,
                    .16
                );

            border-radius:999px;

            background:
                rgba(
                    26,
                    18,
                    13,
                    .88
                );

            backdrop-filter:
                blur(12px);

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.18);

            opacity:0;

            transition:
                opacity .4s ease,
                transform .4s var(--ease);
        }

        body.invitation-opened
        .mini-nav {
            opacity:1;
        }

        .mini-nav a {
            min-width:52px;

            padding:9px 9px;

            color:#d8c9bd;

            font-size:7px;
            font-weight:650;

            letter-spacing:.06em;

            text-align:center;
            text-transform:uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | KEYFRAMES
        |--------------------------------------------------------------------------
        */

        @keyframes openingZoom {

            from {
                transform:scale(1.03);
            }

            to {
                transform:scale(1.12);
            }

        }

        @keyframes openingFadeUp {

            from {
                opacity:0;
                transform:
                    translateY(20px);
            }

            to {
                opacity:1;
                transform:none;
            }

        }

        @keyframes openingName {

            from {
                opacity:0;

                transform:
                    translateY(34px);
            }

            to {
                opacity:1;
                transform:none;
            }

        }

        @keyframes heroTextIn {

            from {
                opacity:0;

                transform:
                    translateY(42px);
            }

            to {
                opacity:1;
                transform:none;
            }

        }

        @keyframes heroBreath {

            from {
                transform:
                    scale(1.06);
            }

            to {
                transform:
                    scale(1.12);
            }

        }

        @keyframes musicPulse {

            0%,
            100% {
                transform:scale(1);
            }

            50% {
                transform:scale(1.06);
            }

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media(max-width:680px) {

            .modern-section {
                padding:
                    58px 14px;
            }

            .hero-content {
                padding:
                    88px 16px
                    72px;
            }

            .hero-bottom {
                align-items:flex-start;
                flex-direction:column;
                gap:12px;
            }

            .countdown {
                margin-top:24px;
            }

            .count-item {
                padding:16px 3px;
            }

            .quote-section .modern-section {
                padding-top:62px;
                padding-bottom:62px;
            }

            .quote-mark {
                margin-bottom:16px;
                font-size:58px;
            }

            .quote-text {
                font-size:
                    clamp(
                        16px,
                        5vw,
                        21px
                    );

                line-height:1.6;
            }

            .couple-layout,
            .event-editorial {
                grid-template-columns:1fr;
                gap:7px;
                margin-top:26px;
            }

            .couple-layout {
                max-width:430px;
            }

            .couple-small,
            .couple-photo {
                min-height:0;
                aspect-ratio:4/5;
            }

            .couple-small {
                padding:20px;
            }

            .couple-profile-name {
                font-size:
                    clamp(
                        34px,
                        11vw,
                        46px
                    );
            }

            .couple-profile-parent {
                margin-top:12px;
                font-size:11px;
            }

            .couple-profile-instagram {
                margin-top:12px;
            }

            .story-text {
                margin-top:24px;
                font-size:13px;
                line-height:1.78;
            }

            .gallery-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0,1fr)
                    );

                gap:4px;
                max-width:430px;
                margin-top:24px;
            }

            .gallery-item:nth-child(n) {
                grid-column:auto;
            }

            .gallery-item:nth-child(n) img {
                aspect-ratio:4/5;
            }

            .event-editorial {
                max-width:430px;
            }

            .event-date-card,
            .event-info-card {
                min-height:250px;
                padding:20px;
            }

            .event-day-number {
                font-size:
                    clamp(
                        72px,
                        25vw,
                        104px
                    );
            }

            .event-title {
                margin-top:26px;

                font-size:
                    clamp(
                        34px,
                        11vw,
                        48px
                    );
            }

            .event-address {
                margin-top:18px;
                font-size:10px;
            }

            .rsvp-card {
                max-width:430px;
                margin-top:26px;
                padding:18px;
            }

            .gift-grid {
                grid-template-columns:1fr;
                max-width:430px;
                margin-top:26px;
            }

            .gift-card {
                padding:18px;
            }

            .guest-photo-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0,1fr)
                    );

                gap:4px;
                max-width:430px;
                margin-top:24px;
            }

            .wish-list {
                max-width:430px;
                margin-top:26px;
            }

            .wish-card {
                padding:18px 2px;
            }

            .wish-message {
                font-size:13px;
                line-height:1.68;
            }

            .music-button {
                right:10px;
                bottom:62px;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | REDUCED MOTION
        |--------------------------------------------------------------------------
        */

        @media(
            prefers-reduced-motion:
            reduce
        ) {

            html {
                scroll-behavior:auto;
            }

            *,
            *::before,
            *::after {
                animation-duration:
                    .001ms
                    !important;

                animation-iteration-count:
                    1
                    !important;

                transition-duration:
                    .001ms
                    !important;

                scroll-behavior:auto
                    !important;
            }

            [data-reveal] {
                opacity:1;
                transform:none;
            }

        }

    
        /*
        |--------------------------------------------------------------------------
        | MINIMAL — CLEAN REFERENCE BUILD
        |--------------------------------------------------------------------------
        | Restrained, monochrome, no decorative blobs, no exaggerated motion.
        */

        :root {
            --dark:#080808;
            --dark-soft:#141414;
            --brown:#080808;
            --brown-deep:#000;
            --cream:#f3f3f0;
            --cream-2:#fafaf8;
            --paper:#fff;
            --muted:#8a8a86;
            --line:rgba(0,0,0,.14);

            --min-sans:"DM Sans",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            --min-serif:"Playfair Display",Georgia,serif;
            --min-hand:"Caveat",cursive;
        }

        html, body {
            background:#000;
        }

        body {
            font-family:var(--min-sans);
            color:#111;
        }

        .modern-shell {
            background:#000;
        }

        /* Kill the over-designed motion from Modern. */
        [data-reveal],
        .reveal-ready [data-reveal],
        .reveal-ready [data-reveal].is-visible,
        .hero-image img,
        .closing-image img {
            opacity:1 !important;
            transform:none !important;
            transition:none !important;
            animation:none !important;
        }

        .music-button,
        .music-button *,
        .opening *,
        .hero *,
        .couple-section *,
        .gallery-section *,
        .story-section * {
            animation:none !important;
        }

        .modern-section {
            max-width:760px;
            padding:58px 18px;
        }

        .section-number {
            margin-bottom:10px;
            color:#90908b;
            font:600 7px/1 var(--min-sans);
            letter-spacing:.16em;
        }

        .section-number::before,
        .section-number::after {
            width:16px;
            background:currentColor;
            opacity:.45;
        }

        .section-title {
            color:#111;
            font-family:var(--min-hand);
            font-size:clamp(38px,7vw,58px);
            font-weight:700;
            line-height:.9;
            letter-spacing:-.03em;
        }

        .section-copy {
            color:#777;
            font:400 11px/1.7 var(--min-sans);
        }

        .modern-button {
            min-height:42px;
            padding:0 16px;
            border:1px solid currentColor;
            border-radius:0;
            background:transparent;
            color:#111;
            font:700 8px/1 var(--min-sans);
            letter-spacing:.1em;
            text-transform:uppercase;
            box-shadow:none;
            transition:none;
        }

        .modern-button:hover {
            transform:none;
        }

        .modern-button.dark {
            background:#000;
            color:#fff;
        }

        .modern-button.light {
            border-color:#fff;
            background:transparent;
            color:#fff;
        }


        /*
        |--------------------------------------------------------------------------
        | MINIMAL OPENING VISIBILITY FIX
        |--------------------------------------------------------------------------
        | The base Modern opening starts several elements at opacity:0 and
        | reveals them through animation. Minimal intentionally disables those
        | animations, so these elements must be explicitly visible.
        */

        .opening .opening-kicker,
        .opening .opening-name,
        .opening .opening-and,
        .opening .opening-date,
        .opening .opening-guest,
        .opening .opening-action {
            opacity:1 !important;
            visibility:visible !important;
            transform:none !important;
            animation:none !important;
        }

        .opening .opening-content {
            opacity:1 !important;
            visibility:visible !important;
        }

        .opening .opening-action {
            display:block !important;
        }

        .opening .modern-button {
            opacity:1 !important;
            visibility:visible !important;
            pointer-events:auto !important;
        }


        /* Opening */
        .opening {
            min-height:100svh;
            background:#dce2e3;
            color:#fff;
        }

        .opening-bg img {
            filter:saturate(.78) contrast(.94) brightness(.92);
        }

        .opening-bg::after {
            background:
                linear-gradient(90deg,rgba(0,0,0,.32),rgba(0,0,0,.04) 70%),
                linear-gradient(180deg,rgba(0,0,0,.02) 45%,rgba(0,0,0,.36));
        }

        .opening-no-cover {
            background:#dfe4e5;
        }

        .opening-content {
            width:100%;
            max-width:600px;
            margin:0 auto 0 0;
            padding:54px 22px 30px;
            align-items:flex-start;
            text-align:left;
        }

        .opening-kicker {
            margin-bottom:12px;
            color:#fff;
            font:500 9px/1.2 var(--min-sans);
            letter-spacing:0;
            text-transform:none;
        }

        .opening-title {
            margin:0 0 4px;
            color:#fff;
            font:500 17px/1.1 var(--min-sans);
            letter-spacing:-.03em;
            text-transform:none;
        }

        .opening-name {
            display:inline;
            color:#fff;
            font-family:var(--min-hand);
            font-size:clamp(44px,9vw,68px);
            font-weight:700;
            line-height:.82;
            letter-spacing:-.03em;
        }

        .opening-name.first,
        .opening-name.second {
            transform:none;
        }

        .opening-and {
            display:inline;
            margin:0 .08em;
            color:#fff;
            font:700 .78em/1 var(--min-hand);
        }

        .opening-date {
            margin-top:12px;
            color:#fff;
            font:600 8px/1.2 var(--min-sans);
            letter-spacing:.22em;
        }

        .opening-guest {
            margin-top:auto;
            padding-top:24px;
            border:0;
            color:#fff;
            font:400 9px/1.55 var(--min-sans);
        }

        .opening-action {
            width:100%;
            max-width:420px;
            margin-top:14px;
        }

        .opening-action .modern-button {
            width:100%;
            justify-content:flex-start;
            border-color:#fff;
            color:#fff;
        }

        /* Hero */
        .hero {
            min-height:92svh;
            background:#000;
        }

        .hero-image::after {
            background:linear-gradient(
                180deg,
                rgba(0,0,0,.12),
                rgba(0,0,0,.18) 48%,
                rgba(0,0,0,.68)
            );
        }

        .hero-content {
            max-width:760px;
            padding:72px 18px 58px;
        }

        .hero-topline {
            font:600 7px/1 var(--min-sans);
            letter-spacing:.12em;
        }

        .hero-title {
            max-width:620px;
            font-family:var(--min-hand);
            font-size:clamp(58px,12vw,96px);
            font-weight:700;
            line-height:.76;
            letter-spacing:-.04em;
        }

        .hero-word.word-1,
        .hero-word.word-2,
        .hero-word.word-3 {
            transform:none;
        }

        .hero-amp {
            margin:0 .08em;
            font-family:var(--min-hand);
            font-size:.55em;
            font-style:normal;
        }

        .hero-bottom {
            border-top:1px solid rgba(255,255,255,.34);
        }

        /* Quote / Countdown */
        .countdown-section {
            background:#f2f2ef;
        }

        .countdown {
            max-width:540px;
            border-top:1px solid #bbb;
            border-bottom:1px solid #bbb;
            background:transparent;
        }

        .count-item {
            padding:18px 4px;
        }

        .count-item:not(:last-child)::after {
            background:#c1c1bd;
        }

        .count-value {
            color:#111;
            font-family:var(--min-serif);
            font-size:31px;
            font-weight:400;
        }

        .count-label {
            color:#777;
            font-size:6px;
        }

        .quote-section {
            background:#080808;
            color:#fff;
        }

        .quote-section .modern-section {
            max-width:650px;
            padding-top:66px;
            padding-bottom:66px;
        }

        .quote-mark {
            margin-bottom:16px;
            color:#fff;
            font:700 52px/1 var(--min-hand);
        }

        .quote-text {
            color:#fff;
            font:500 clamp(15px,3vw,22px)/1.55 var(--min-sans);
        }

        /* Groom / Bride: clean 2x2 editorial blocks like reference */
        .couple-section {
            background:#f1f1ee !important;
        }

        .couple-section .modern-section {
            max-width:680px;
        }

        .couple-layout {
            grid-template-columns:1fr 1fr;
            gap:0;
            max-width:620px;
            margin:26px auto 0;
            border:1px solid #d8d8d4;
            background:#fff;
        }

        .couple-photo,
        .couple-small {
            aspect-ratio:1/1;
            min-height:0;
        }

        .couple-photo {
            background:#e4e4e1;
        }

        .couple-photo img {
            filter:saturate(.78) contrast(.94);
        }

        .couple-small {
            justify-content:center;
            padding:22px;
            background:#fff !important;
            color:#111 !important;
        }

        .couple-small-label {
            margin-bottom:10px;
            color:#a07152 !important;
            font:600 6px/1.2 var(--min-sans);
            letter-spacing:.05em;
            text-transform:none;
        }

        .couple-profile-name {
            color:#111;
            font:700 clamp(24px,5vw,38px)/.98 var(--min-sans);
            letter-spacing:-.055em;
        }

        .couple-profile-parent {
            margin-top:11px;
            color:#686864;
            font:400 9px/1.52 var(--min-sans);
        }

        .couple-profile-origin {
            margin-top:8px;
            color:#8b8b87;
            font:500 7px/1.4 var(--min-sans);
            letter-spacing:.04em;
        }

        .couple-profile-instagram {
            margin-top:11px;
            color:#111;
            font:700 13px/1 var(--min-hand);
        }

        /* Story */
        .story-section {
            background:#000;
            color:#fff;
        }

        .story-section .section-number {
            color:#8b8b87;
        }

        .story-section .section-title {
            color:#fff;
        }

        .story-text {
            max-width:520px;
            margin:24px auto 0;
            padding:28px 24px;
            border-radius:0;
            background:#f3f3f0;
            color:#111;
            font:400 11px/1.72 var(--min-sans);
            text-align:left;
            box-shadow:none;
        }

        /* Gallery: one proper hero photo + real thumbnails */
        .gallery-section {
            background:#f2f2ef;
        }

        .gallery-section .modern-section {
            max-width:680px;
        }

        .minimal-gallery {
            max-width:580px;
            margin:26px auto 0;
        }

        .minimal-gallery-stage {
            position:relative;
            overflow:hidden;
            width:100%;
            aspect-ratio:4/5;
            background:#ddd;
        }

        .minimal-gallery-stage img {
            width:100%;
            height:100%;
            display:block;
            object-fit:cover;
            filter:saturate(.80) contrast(.95);
        }

        .minimal-gallery-count {
            position:absolute;
            right:10px;
            bottom:10px;
            padding:5px 8px;
            background:rgba(0,0,0,.68);
            color:#fff;
            font:600 7px/1 var(--min-sans);
            letter-spacing:.08em;
        }

        .minimal-gallery-thumbs {
            display:flex;
            gap:4px;
            overflow-x:auto;
            margin-top:5px;
            padding-bottom:2px;
            scrollbar-width:none;
        }

        .minimal-gallery-thumbs::-webkit-scrollbar {
            display:none;
        }

        .minimal-gallery-thumb {
            flex:0 0 62px;
            width:62px;
            height:48px;
            padding:0;
            border:1px solid transparent;
            background:#ddd;
            cursor:pointer;
        }

        .minimal-gallery-thumb.is-active {
            border-color:#111;
        }

        .minimal-gallery-thumb img {
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
            filter:saturate(.75);
        }

        /* Event */
        .event-section {
            background:#000;
            color:#fff;
        }

        .event-section .section-number,
        .event-section .section-copy {
            color:#999;
        }

        .event-section .section-title {
            color:#fff;
        }

        .event-editorial {
            grid-template-columns:1fr;
            gap:8px;
            max-width:560px;
            margin:26px auto 0;
        }

        .event-date-card {
            min-height:0;
            padding:20px;
            border:1px solid #fff;
            background:#000;
            color:#fff;
        }

        .event-info-card {
            min-height:0;
            padding:24px 22px;
            border:0;
            background:#f3f3f0;
            color:#111;
        }

        .event-title {
            color:#111;
            font:700 clamp(36px,7vw,52px)/.92 var(--min-hand);
            letter-spacing:-.03em;
        }

        .event-address {
            color:#666;
            font-size:10px;
        }

        /* Forms / gift / wishes */
        .rsvp-section,
        .guest-photo-section,
        .wishes-section {
            background:#080808;
            color:#fff;
        }

        .rsvp-section .section-title,
        .guest-photo-section .section-title,
        .wishes-section .section-title {
            color:#fff;
        }

        .rsvp-section .section-number,
        .guest-photo-section .section-number,
        .wishes-section .section-number,
        .rsvp-section .section-copy,
        .guest-photo-section .section-copy,
        .wishes-section .section-copy {
            color:#999;
        }

        .rsvp-card {
            max-width:520px;
            padding:0;
            background:transparent;
        }

        .field-label {
            color:#bbb;
            font-size:6px;
        }

        .field {
            border:1px solid #fff;
            border-radius:0;
            background:#f5f5f2;
            color:#111;
            box-shadow:none;
        }

        .gift-section {
            background:#f2f2ef;
        }

        .gift-grid {
            grid-template-columns:1fr;
            max-width:520px;
            gap:6px;
        }

        .gift-card {
            padding:18px 20px;
            border:1px solid #bbb;
            border-radius:0;
            background:#fff;
            color:#111;
            box-shadow:none;
        }

        .guest-photo-grid {
            max-width:600px;
            gap:3px;
        }

        .guest-photo-item {
            border-radius:0;
        }

        .wish-list {
            max-width:560px;
            border:0;
        }

        .wish-card {
            margin-bottom:7px;
            padding:13px 15px;
            border:0;
            border-radius:7px;
            background:#f2f2ef;
            color:#111;
        }

        .wish-name {
            color:#111;
            font-size:8px;
        }

        .wish-message {
            color:#555;
            font-size:11px;
            line-height:1.5;
        }

        /* Closing */
        .closing {
            min-height:62svh;
            background:#f0f0ed;
            color:#111;
        }

        .closing-image {
            opacity:.10;
        }

        .closing-image::after {
            background:linear-gradient(
                180deg,
                rgba(240,240,237,.05),
                rgba(240,240,237,.93)
            );
        }

        .closing-small {
            color:#777;
            font-size:7px;
        }

        .closing-title {
            color:#111;
            font:700 clamp(48px,9vw,76px)/.84 var(--min-hand);
            letter-spacing:-.04em;
        }

        .closing-text {
            color:#666;
            font-size:10px;
        }

        .footer {
            background:#f0f0ed;
            color:#777;
            border-top:1px solid #ccc;
        }

        /* Floating nav kept quiet */
        .mini-nav {
            left:50%;
            right:auto;
            width:auto;
            max-width:calc(100vw - 24px);
            transform:translateX(-50%);
            gap:2px;
            padding:5px 8px;
            border:1px solid rgba(0,0,0,.14);
            border-radius:999px;
            background:rgba(248,248,245,.94);
            box-shadow:0 6px 20px rgba(0,0,0,.14);
            backdrop-filter:blur(12px);
        }

        .mini-nav a {
            min-width:30px;
            min-height:30px;
            border-radius:999px;
            color:#111;
        }

        .mini-nav a:hover {
            background:#111;
            color:#fff;
        }

        .music-button {
            top:14px;
            right:12px;
            bottom:auto;
            width:34px;
            height:34px;
            border:1px solid rgba(255,255,255,.25);
            background:rgba(0,0,0,.64);
            color:#fff;
            box-shadow:none;
        }

        @media(max-width:680px) {

            .modern-section {
                padding:46px 12px;
            }

            .opening-content {
                padding:48px 13px 18px;
            }

            .opening-title {
                font-size:15px;
            }

            .opening-name {
                font-size:clamp(42px,14vw,56px);
            }

            .hero-content {
                padding:68px 13px 52px;
            }

            .hero-title {
                font-size:clamp(50px,19vw,74px);
            }

            .couple-layout {
                max-width:430px;
                grid-template-columns:1fr 1fr;
            }

            .couple-small {
                padding:14px;
            }

            .couple-profile-name {
                font-size:clamp(20px,7vw,28px);
            }

            .couple-profile-parent {
                font-size:7.5px;
            }

            .minimal-gallery {
                max-width:430px;
            }

            .minimal-gallery-stage {
                aspect-ratio:4/5;
            }

            .minimal-gallery-thumb {
                flex-basis:56px;
                width:56px;
                height:43px;
            }

            .event-editorial,
            .gift-grid,
            .guest-photo-grid,
            .wish-list,
            .rsvp-card {
                max-width:430px;
            }

            .guest-photo-grid {
                grid-template-columns:repeat(2,minmax(0,1fr));
            }

            .mini-nav {
                bottom:max(8px, env(safe-area-inset-bottom));
            }

            .music-button {
                top:max(10px, env(safe-area-inset-top));
                right:10px;
            }

        }


    </style>

</head>


<body class="locked">




<div
    id="opening"
    class="opening"
>

    @if($invitation->cover_path)

        <div class="opening-bg">

            <img
                src="{{ url(
                    '/storage/'
                    .
                    $invitation->cover_path
                ) }}"
                alt=""
            >

        </div>

    @else

        <div class="opening-no-cover"></div>

    @endif


    <div class="opening-content">

        <div class="opening-kicker">
            The Wedding Of
        </div>


        <h1 class="opening-title">

            <span class="opening-name first">
                {{ $invitation->groom_name }}
            </span>

            <span class="opening-and">
                &
            </span>

            <span class="opening-name second">
                {{ $invitation->bride_name }}
            </span>

        </h1>


        @if($invitation->event_date)

            <div class="opening-date">

                {{ $invitation
                    ->event_date
                    ->format('d . m . Y')
                }}

            </div>

        @endif


        <div class="opening-guest">

            Yth. Bapak/Ibu/Saudara/i<br>

            <strong>
                {{ $guest?->name ?? 'Tamu Undangan' }}
            </strong>

        </div>


        <div class="opening-action">

            <button
                type="button"
                id="openInvitation"
                class="modern-button light"
            >
                Buka Undangan
            </button>

        </div>

    </div>

</div>


<div class="modern-shell">




<section
    id="home"
    class="hero"
>

    @if($invitation->cover_path)

        <div
            id="heroParallax"
            class="hero-image"
        >

            <img
                src="{{ url(
                    '/storage/'
                    .
                    $invitation->cover_path
                ) }}"
                alt=""
            >

        </div>

    @else

        <div class="hero-empty"></div>

    @endif


    <div class="hero-content">

        <div class="hero-topline">
            The Wedding Of
        </div>


        <h1 class="hero-title">

            <span class="hero-word word-1">
                {{ $invitation->groom_name }}
            </span>

            <span class="hero-amp hero-word word-2">
                &
            </span>

            <span class="hero-word word-3">
                {{ $invitation->bride_name }}
            </span>

        </h1>


        <div class="hero-bottom">

            @if($invitation->event_date)

                <div class="hero-date">

                    {{ $invitation
                        ->event_date
                        ->format('d . m . Y')
                    }}

                </div>

            @endif


            <a
                href="#countdown"
                class="hero-scroll"
            >
                Explore
            </a>

        </div>

    </div>

</section>




@foreach($sectionOrder as $sectionKey)

    @if(data_get($sections, $sectionKey, true))

        @switch($sectionKey)

            @case('countdown')



<section
    id="countdown"
    class="countdown-section"
>

    <div class="modern-section">

        <div
            class="section-number"
            data-reveal
        >
            Save The Date
        </div>


        <h2
            class="section-title"
            data-reveal
        >

            {{ $invitation->groom_name }}
            &
            {{ $invitation->bride_name }}

        </h2>


        @if($invitation->event_date)

            <div
                class="date-display"
                data-reveal
            >

                {{ $invitation
                    ->event_date
                    ->format('d . m . Y')
                }}

            </div>


            <div
                id="countdownGrid"
                class="countdown"
                data-date="{{ $invitation->event_date->toIso8601String() }}"
                data-reveal="scale"
            >

                <div class="count-item">

                    <span
                        id="countDays"
                        class="count-value"
                    >
                        0
                    </span>

                    <span class="count-label">
                        Hari
                    </span>

                </div>


                <div class="count-item">

                    <span
                        id="countHours"
                        class="count-value"
                    >
                        0
                    </span>

                    <span class="count-label">
                        Jam
                    </span>

                </div>


                <div class="count-item">

                    <span
                        id="countMinutes"
                        class="count-value"
                    >
                        0
                    </span>

                    <span class="count-label">
                        Menit
                    </span>

                </div>


                <div class="count-item">

                    <span
                        id="countSeconds"
                        class="count-value"
                    >
                        0
                    </span>

                    <span class="count-label">
                        Detik
                    </span>

                </div>

            </div>

        @endif

    </div>

</section>


                @break


            @case('quote')



@if($invitation->quote)

    <section class="quote-section">

        <div class="modern-section">

            <div
                class="quote-mark"
                data-reveal
            >
                “
            </div>

            <p
                class="quote-text"
                data-reveal
            >
                {{ $invitation->quote }}
            </p>

        </div>

    </section>

@endif


                @break


            @case('groom')



<section class="couple-section">

    <div class="modern-section">

        <div
            class="section-number"
            data-reveal
        >
            The Groom
        </div>


        <div class="couple-layout">

            <div
                class="couple-photo"
                data-reveal="left"
            >

                @if($invitation->groom_photo_path)

                    <img
                        src="{{ url(
                            '/storage/'
                            .
                            $invitation->groom_photo_path
                        ) }}?v={{ $invitation->updated_at?->timestamp ?? time() }}"
                        alt="{{ $invitation->groom_name }}"
                        loading="lazy"
                        style="
                            object-position:
                            {{ $invitation->groom_photo_x ?? 50 }}%
                            {{ $invitation->groom_photo_y ?? 50 }}%;
                        "
                    >

                @else

                    <div
                        style="
                            width:100%;
                            height:100%;
                            display:grid;
                            place-items:center;
                            padding:30px;
                            color:#a99687;
                            font-size:9px;
                            letter-spacing:.12em;
                            text-transform:uppercase;
                            text-align:center;
                        "
                    >
                        Foto Groom belum diupload
                    </div>

                @endif

            </div>


            <div
                class="couple-small"
                data-reveal="right"
            >

                <div class="couple-small-label">
                    The Groom
                </div>


                <div class="couple-profile-info">

                    <h2 class="couple-profile-name">
                        {{ $invitation->groom_name }}
                    </h2>


                    @if($invitation->groom_parent_text)

                        <div class="couple-profile-parent">
                            {{ $invitation->groom_parent_text }}
                        </div>

                    @endif


                    @if($invitation->groom_origin)

                        <div class="couple-profile-origin">
                            {{ $invitation->groom_origin }}
                        </div>

                    @endif


                    @if($invitation->groom_instagram)

                        @php
                            $groomInstagram =
                                ltrim(
                                    $invitation->groom_instagram,
                                    '@'
                                );
                        @endphp

                        <a
                            href="https://instagram.com/{{ $groomInstagram }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="couple-profile-instagram"
                        >
                            {{ '@' . $groomInstagram }}
                        </a>

                    @endif

                </div>

            </div>

        </div>

    </div>

</section>

                @break


            @case('bride')



<section
    class="couple-section"
    style="background:#e5d7c5;"
>

    <div class="modern-section">

        <div
            class="section-number"
            data-reveal
        >
            The Bride
        </div>


        <div class="couple-layout">

            <div
                class="couple-small"
                data-reveal="left"
                style="background:#6a4530;"
            >

                <div class="couple-small-label">
                    The Bride
                </div>


                <div class="couple-profile-info">

                    <h2 class="couple-profile-name">
                        {{ $invitation->bride_name }}
                    </h2>


                    @if($invitation->bride_parent_text)

                        <div class="couple-profile-parent">
                            {{ $invitation->bride_parent_text }}
                        </div>

                    @endif


                    @if($invitation->bride_origin)

                        <div class="couple-profile-origin">
                            {{ $invitation->bride_origin }}
                        </div>

                    @endif


                    @if($invitation->bride_instagram)

                        @php
                            $brideInstagram =
                                ltrim(
                                    $invitation->bride_instagram,
                                    '@'
                                );
                        @endphp

                        <a
                            href="https://instagram.com/{{ $brideInstagram }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="couple-profile-instagram"
                        >
                            {{ '@' . $brideInstagram }}
                        </a>

                    @endif

                </div>

            </div>


            <div
                class="couple-photo"
                data-reveal="right"
            >

                @if($invitation->bride_photo_path)

                    <img
                        src="{{ url(
                            '/storage/'
                            .
                            $invitation->bride_photo_path
                        ) }}?v={{ $invitation->updated_at?->timestamp ?? time() }}"
                        alt="{{ $invitation->bride_name }}"
                        loading="lazy"
                        style="
                            object-position:
                            {{ $invitation->bride_photo_x ?? 50 }}%
                            {{ $invitation->bride_photo_y ?? 50 }}%;
                        "
                    >

                @else

                    <div
                        style="
                            width:100%;
                            height:100%;
                            display:grid;
                            place-items:center;
                            padding:30px;
                            color:#a99687;
                            font-size:9px;
                            letter-spacing:.12em;
                            text-transform:uppercase;
                            text-align:center;
                        "
                    >
                        Foto Bride belum diupload
                    </div>

                @endif

            </div>

        </div>

    </div>

</section>

                @break


            @case('story')



@if(
    data_get(
        $sections,
        'story',
        true
    )
    &&
    $invitation->story
)

    <section class="story-section">

        <div class="modern-section">

            <div
                class="section-number"
                data-reveal
            >
                Our Story
            </div>

            <h2
                class="section-title"
                data-reveal
            >
                Cerita Kami
            </h2>

            <div
                class="story-text"
                data-reveal
            >
                {{ $invitation->story }}
            </div>

        </div>

    </section>

@endif


                @break


            @case('gallery')



@if(
    data_get(
        $sections,
        'gallery',
        true
    )
    &&
    count($gallery)
)

    <section
        id="gallery"
        class="gallery-section"
    >

        <div class="modern-section">

            <div
                class="section-number"
                data-reveal
            >
                Galleries
            </div>

            <h2
                class="section-title"
                data-reveal
            >
                Moment<br>
                Yang Berharga
            </h2>


            <div
                class="minimal-gallery"
                data-minimal-gallery
            >

                @php
                    $firstGalleryPhoto =
                        $gallery[0] ?? null;
                @endphp

                <div class="minimal-gallery-stage">

                    @if($firstGalleryPhoto)

                        <img
                            data-minimal-gallery-main
                            src="{{ url(
                                '/storage/'
                                .
                                $firstGalleryPhoto
                            ) }}"
                            loading="lazy"
                            alt=""
                        >

                        <div
                            class="minimal-gallery-count"
                            data-minimal-gallery-count
                        >
                            1 / {{ count($gallery) }}
                        </div>

                    @endif

                </div>


                <div class="minimal-gallery-thumbs">

                    @foreach($gallery as $index => $photo)

                        <button
                            type="button"
                            class="minimal-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}"
                            data-minimal-gallery-thumb
                            data-src="{{ url('/storage/' . $photo) }}"
                            data-index="{{ $index + 1 }}"
                            aria-label="Lihat foto {{ $index + 1 }}"
                        >
                            <img
                                src="{{ url(
                                    '/storage/'
                                    .
                                    $photo
                                ) }}"
                                loading="lazy"
                                alt=""
                            >
                        </button>

                    @endforeach

                </div>

            </div>

        </div>

    </section>

@endif


                @break


            @case('event')



<section
    id="event"
    class="event-section"
>

    <div class="modern-section">

        <div
            class="section-number"
            data-reveal
        >
            Location
        </div>

        <h2
            class="section-title"
            data-reveal
        >
            Save<br>
            The Date
        </h2>


        <div class="event-editorial">


            <div
                class="event-date-card"
                data-reveal="left"
            >

                <div class="event-mini-label">
                    Wedding Day
                </div>


                @if($invitation->event_date)

                    <div class="event-day-number">

                        {{ $invitation
                            ->event_date
                            ->format('d')
                        }}

                    </div>


                    <div class="event-date-bottom">

                        {{ $invitation
                            ->event_date
                            ->translatedFormat(
                                'F Y'
                            )
                        }}

                        <br>

                        {{ $invitation
                            ->event_date
                            ->format('H:i')
                        }}

                    </div>

                @endif

            </div>


            <div
                class="event-info-card"
                data-reveal="right"
            >

                <div class="event-mini-label">
                    Venue
                </div>


                <h3 class="event-title">

                    {{ $invitation->venue_name
                        ?: 'Lokasi Acara'
                    }}

                </h3>


                @if($invitation->venue_address)

                    <div class="event-address">
                        {{ $invitation->venue_address }}
                    </div>

                @endif


                <div class="event-actions">

                    @if($invitation->maps_url)

                        <a
                            href="{{ $invitation->maps_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="modern-button"
                        >
                            Lihat Lokasi
                        </a>

                    @endif

                </div>

            </div>

        </div>

    </div>

</section>


                @break


            @case('rsvp')



<section
    id="rsvp"
    class="rsvp-section"
>

    <div class="modern-section">

        <div
            class="section-number"
            data-reveal
        >
            Reservation
        </div>

        <h2
            class="section-title"
            data-reveal
        >
            RSVP
        </h2>

        <p
            class="section-copy"
            data-reveal
        >
            Mohon konfirmasi kehadiran untuk membantu kami
            mempersiapkan hari bahagia ini.
        </p>


        @if(session('ok'))

            <div
                class="success-message"
                data-reveal
            >
                {{ session('ok') }}
            </div>

        @endif


        @if($errors->any())

            <div
                class="success-message"
                role="alert"
                data-reveal
                style="border-color:#d6a29a;color:#7f2f25"
            >
                <strong>Periksa kembali data RSVP.</strong>

                <ul style="margin:8px 0 0;padding-left:18px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        @endif


        <form
            method="POST"
            action="{{ route(
                'public.rsvp',
                $invitation
            ) }}"
            class="rsvp-card"
            data-reveal="scale"
        >

            @csrf


            <input
                type="hidden"
                name="token"
                value="{{ request()->query('g') }}"
            >


            <label class="field-label">
                Nama
            </label>

            <input
                type="text"
                name="name"
                class="field"
                value="{{ old(
                    'name',
                    $guest?->name
                ) }}"
                placeholder="Nama Anda"
                @readonly($guest)
                required
            >


            <label class="field-label">
                Kehadiran
            </label>

            <select
                name="status"
                class="field"
                required
            >

                <option value="hadir">
                    Iya, saya akan datang
                </option>

                <option value="tidak_hadir">
                    Maaf, saya tidak bisa datang
                </option>

                <option value="ragu">
                    Masih belum pasti
                </option>

            </select>


            <label class="field-label">
                Jumlah Tamu
            </label>

            <input
                type="number"
                name="party_size"
                class="field"
                min="1"
                max="10"
                value="{{ old(
                    'party_size',
                    $guest?->party_size ?: 1
                ) }}"
                required
            >


            <label class="field-label">
                Ucapan & Doa
            </label>

            <textarea
                name="message"
                class="field"
                placeholder="Tulis ucapan untuk kedua mempelai..."
            >{{ old('message') }}</textarea>


            <div class="rsvp-action">

                <button
                    type="submit"
                    class="modern-button dark"
                >
                    Kirim Respon
                </button>

            </div>

        </form>

    </div>

</section>


                @break


            @case('gift')



@if(
    data_get(
        $sections,
        'gift',
        true
    )
    &&
    count($giftAccounts)
)

    <section class="gift-section">

        <div class="modern-section">

            <div
                class="section-number"
                data-reveal
            >
                Wedding Gift
            </div>

            <h2
                class="section-title"
                data-reveal
            >
                Kirim<br>
                Hadiah
            </h2>

            <p
                class="section-copy gift-copy"
                data-reveal
            >
                Doa dan kehadiran Anda adalah hadiah terindah.
                Namun apabila ingin memberikan tanda kasih,
                dapat melalui rekening berikut.
            </p>


            <div class="gift-grid">

                @foreach($giftAccounts as $index => $gift)

                    <div
                        class="gift-card"
                        data-reveal
                        style="
                            transition-delay:
                            {{ min(
                                $index * 100,
                                400
                            ) }}ms;
                        "
                    >

                        <div class="gift-bank">
                            {{ $gift['bank']
                                ?? 'Direct Transfer'
                            }}
                        </div>

                        <div class="gift-number">
                            {{ $gift['number']
                                ?? '-'
                            }}
                        </div>

                        @if(!empty($gift['name']))

                            <div class="gift-name">

                                a.n.
                                {{ $gift['name'] }}

                            </div>

                        @endif

                    </div>

                @endforeach

            </div>

        </div>

    </section>

@endif


                @break


            @case('guest_photo')



@if(
    in_array(
        $invitation->plan,
        [
            'premium',
            'pro'
        ],
        true
    )
)

    <section class="guest-photo-section">

        <div class="modern-section">

            <div
                class="section-number"
                data-reveal
            >
                Guest Moment
            </div>

            <h2
                class="section-title"
                data-reveal
            >
                Bagikan<br>
                Momenmu
            </h2>


            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route(
                    'public.photo',
                    $invitation
                ) }}"
                class="rsvp-card"
                data-reveal="scale"
            >

                @csrf

                <input
                    type="hidden"
                    name="token"
                    value="{{ request()->query('g') }}"
                >


                <label class="field-label">
                    Nama
                </label>

                <input
                    type="text"
                    name="guest_name"
                    class="field"
                    value="{{ $guest?->name }}"
                    placeholder="Nama Anda"
                    @readonly($guest)
                    required
                >


                <label class="field-label">
                    Foto
                </label>

                <input
                    type="file"
                    name="photo"
                    class="field"
                    accept="image/*"
                    required
                >


                <label class="field-label">
                    Caption
                </label>

                <input
                    type="text"
                    name="caption"
                    class="field"
                    placeholder="Caption opsional"
                >


                <div class="rsvp-action">

                    <button
                        type="submit"
                        class="modern-button"
                    >
                        Kirim Foto
                    </button>

                </div>

            </form>


            @if($photos->count())

                <div class="guest-photo-grid">

                    @foreach($photos as $index => $photo)

                        <div
                            class="guest-photo-item"
                            data-reveal="scale"
                            style="
                                transition-delay:
                                {{ min(
                                    ($index % 6) * 70,
                                    350
                                ) }}ms;
                            "
                        >

                            <img
                                src="{{ url(
                                    '/storage/'
                                    .
                                    $photo->path
                                ) }}"
                                loading="lazy"
                                alt=""
                            >

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </section>

@endif


                @break


            @case('wishes')



@if(
    data_get(
        $sections,
        'wishes',
        true
    )
    &&
    $wishes->count()
)

    <section
        id="wishes"
        class="wishes-section"
    >

        <div class="modern-section">

            <div
                class="section-number"
                data-reveal
            >
                Wishes
            </div>

            <h2
                class="section-title"
                data-reveal
            >
                Ucapan<br>
                & Doa
            </h2>


            <div class="wish-list">

                @foreach($wishes as $index => $wish)

                    <article
                        class="wish-card"
                        data-reveal
                        style="
                            transition-delay:
                            {{ min(
                                ($index % 5) * 70,
                                280
                            ) }}ms;
                        "
                    >

                        <div class="wish-name">
                            {{ $wish->guest_name }}
                        </div>

                        <div class="wish-message">
                            {{ $wish->message }}
                        </div>

                    </article>

                @endforeach

            </div>

        </div>

    </section>

@endif


                @break


        @endswitch

    @endif

@endforeach




<section class="closing">

    @if(
        $galleryTwo
        ||
        $invitation->cover_path
    )

        <div
            id="closingParallax"
            class="closing-image"
        >

            <img
                src="{{ url(
                    '/storage/'
                    .
                    (
                        $galleryTwo
                        ?: $invitation->cover_path
                    )
                ) }}"
                loading="lazy"
                alt=""
            >

        </div>

    @endif


    <div class="closing-content">

        <div
            class="closing-small"
            data-reveal
        >
            Thank You
        </div>

        <h2
            class="closing-title"
            data-reveal
        >

            {{ $invitation->groom_name }}

            <br>

            &

            <br>

            {{ $invitation->bride_name }}

        </h2>

        <p
            class="closing-text"
            data-reveal
        >
            Merupakan suatu kehormatan dan kebahagiaan bagi kami
            apabila Anda berkenan hadir dan memberikan doa restu.
        </p>

    </div>

</section>


<footer class="footer">
    Made with UNDANGANTA.ID
</footer>


</div>




<nav class="mini-nav">

    <a href="#home">
        Home
    </a>

    <a href="#gallery">
        Gallery
    </a>

    <a href="#event">
        Event
    </a>

    <a href="#rsvp">
        RSVP
    </a>

</nav>




@if($invitation->music_url)

    <audio
        id="inviteMusic"
        loop
        preload="none"
    >

        <source
            src="{{ $invitation->music_url }}"
        >

    </audio>


    <button
        type="button"
        id="musicButton"
        class="music-button"
        aria-label="Musik"
    >
        ♪
    </button>

@endif


<script>

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const opening =
        document.getElementById(
            'opening'
        );

    const openButton =
        document.getElementById(
            'openInvitation'
        );

    const music =
        document.getElementById(
            'inviteMusic'
        );

    const musicButton =
        document.getElementById(
            'musicButton'
        );

    const heroParallax =
        document.getElementById(
            'heroParallax'
        );

    const closingParallax =
        document.getElementById(
            'closingParallax'
        );

    const reduceMotion =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

    let musicPlaying =
        false;


    /*
    |--------------------------------------------------------------------------
    | OPEN INVITATION
    |--------------------------------------------------------------------------
    */

    function hideOpening() {

        if (!opening) {
            return;
        }

        opening.classList.add(
            'hidden'
        );

        document.body.classList.remove(
            'locked'
        );

        document.body.classList.add(
            'invitation-opened'
        );

    }


    if (openButton) {

        openButton.addEventListener(
            'click',
            async function () {

                hideOpening();


                if (music) {

                    try {

                        await music.play();

                        musicPlaying =
                            true;

                        if (musicButton) {

                            musicButton.textContent =
                                'Ⅱ';

                            musicButton.classList.add(
                                'playing'
                            );

                        }

                    } catch (error) {
                        // Autoplay may be blocked.
                    }

                }

            }
        );

    } else {

        document.body.classList.remove(
            'locked'
        );

        document.body.classList.add(
            'invitation-opened'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | MUSIC
    |--------------------------------------------------------------------------
    */

    if (
        music
        &&
        musicButton
    ) {

        musicButton.addEventListener(
            'click',
            async function () {

                try {

                    if (musicPlaying) {

                        music.pause();

                        musicPlaying =
                            false;

                        musicButton.textContent =
                            '♪';

                        musicButton.classList.remove(
                            'playing'
                        );

                    } else {

                        await music.play();

                        musicPlaying =
                            true;

                        musicButton.textContent =
                            'Ⅱ';

                        musicButton.classList.add(
                            'playing'
                        );

                    }

                } catch (error) {
                    // Ignore playback restriction.
                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SCROLL REVEAL
    |--------------------------------------------------------------------------
    */

    const revealItems =
        document.querySelectorAll(
            '[data-reveal]'
        );


    if (reduceMotion) {

        revealItems.forEach(
            function (element) {

                element.classList.add(
                    'is-visible'
                );

            }
        );

    } else {

        const revealObserver =
            new IntersectionObserver(
                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                entry.target
                                    .classList
                                    .add(
                                        'is-visible'
                                    );

                                revealObserver
                                    .unobserve(
                                        entry.target
                                    );

                            }

                        }
                    );

                },
                {
                    threshold:.14,

                    rootMargin:
                        '0px 0px -7% 0px'
                }
            );


        revealItems.forEach(
            function (element) {

                revealObserver.observe(
                    element
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | COUNTDOWN
    |--------------------------------------------------------------------------
    */

    const countdown =
        document.getElementById(
            'countdownGrid'
        );


    if (countdown) {

        const eventTime =
            new Date(
                countdown.dataset.date
            ).getTime();


        const daysElement =
            document.getElementById(
                'countDays'
            );

        const hoursElement =
            document.getElementById(
                'countHours'
            );

        const minutesElement =
            document.getElementById(
                'countMinutes'
            );

        const secondsElement =
            document.getElementById(
                'countSeconds'
            );


        function updateCountdown() {

            const now =
                Date.now();

            let distance =
                eventTime - now;


            if (
                !Number.isFinite(
                    eventTime
                )
                ||
                distance <= 0
            ) {

                distance = 0;

            }


            const days =
                Math.floor(
                    distance /
                    86400000
                );


            const hours =
                Math.floor(
                    (
                        distance
                        %
                        86400000
                    )
                    /
                    3600000
                );


            const minutes =
                Math.floor(
                    (
                        distance
                        %
                        3600000
                    )
                    /
                    60000
                );


            const seconds =
                Math.floor(
                    (
                        distance
                        %
                        60000
                    )
                    /
                    1000
                );


            if (daysElement) {

                daysElement.textContent =
                    days;

            }


            if (hoursElement) {

                hoursElement.textContent =
                    String(hours)
                        .padStart(
                            2,
                            '0'
                        );

            }


            if (minutesElement) {

                minutesElement.textContent =
                    String(minutes)
                        .padStart(
                            2,
                            '0'
                        );

            }


            if (secondsElement) {

                secondsElement.textContent =
                    String(seconds)
                        .padStart(
                            2,
                            '0'
                        );

            }

        }


        updateCountdown();

        setInterval(
            updateCountdown,
            1000
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SUBTLE PARALLAX
    |--------------------------------------------------------------------------
    */

    if (!reduceMotion) {

        let ticking =
            false;


        function updateParallax() {

            const scrollY =
                window.scrollY;


            if (heroParallax) {

                const heroOffset =
                    Math.min(
                        scrollY * .075,
                        55
                    );

                heroParallax.style.transform =
                    'translate3d(0,'
                    +
                    heroOffset
                    +
                    'px,0)';

            }


            if (closingParallax) {

                const rect =
                    closingParallax
                        .parentElement
                        .getBoundingClientRect();

                const viewport =
                    window.innerHeight;

                if (
                    rect.top
                    <
                    viewport
                    &&
                    rect.bottom
                    >
                    0
                ) {

                    const progress =
                        (
                            viewport
                            -
                            rect.top
                        )
                        /
                        (
                            viewport
                            +
                            rect.height
                        );

                    const offset =
                        (
                            progress
                            -
                            .5
                        )
                        *
                        45;

                    closingParallax
                        .style
                        .transform =
                            'translate3d(0,'
                            +
                            offset
                            +
                            'px,0)';

                }

            }


            ticking =
                false;

        }


        window.addEventListener(
            'scroll',
            function () {

                if (!ticking) {

                    window
                        .requestAnimationFrame(
                            updateParallax
                        );

                    ticking =
                        true;

                }

            },
            {
                passive:true
            }
        );


        updateParallax();

    }

</script>



<script>
document.addEventListener('DOMContentLoaded', function () {

    document
        .querySelectorAll('[data-minimal-gallery]')
        .forEach(function (gallery) {

            const main =
                gallery.querySelector(
                    '[data-minimal-gallery-main]'
                );

            const count =
                gallery.querySelector(
                    '[data-minimal-gallery-count]'
                );

            const thumbs =
                Array.from(
                    gallery.querySelectorAll(
                        '[data-minimal-gallery-thumb]'
                    )
                );

            if (!main || !thumbs.length) {
                return;
            }

            thumbs.forEach(function (thumb) {

                thumb.addEventListener(
                    'click',
                    function () {

                        const src =
                            thumb.dataset.src;

                        if (!src) {
                            return;
                        }

                        main.src = src;

                        thumbs.forEach(
                            function (item) {
                                item.classList.remove(
                                    'is-active'
                                );
                            }
                        );

                        thumb.classList.add(
                            'is-active'
                        );

                        if (count) {
                            count.textContent =
                                thumb.dataset.index
                                +
                                ' / '
                                +
                                thumbs.length;
                        }

                    }
                );

            });

        });

});
</script>

</body>

</html>
