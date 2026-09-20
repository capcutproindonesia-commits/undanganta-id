@extends('layouts.app')

@section('content')

<style>
    /*
    |--------------------------------------------------------------------------
    | UNDANGANTA.ID LANDING
    | Lassie-inspired, scoped only to welcome page
    |--------------------------------------------------------------------------
    */

    .ut-home {
        --ut-ink: #171719;
        --ut-muted: #6f6f76;
        --ut-line: #e6e4e0;
        --ut-warm: #fffaf3;
        --ut-peach: #ff715d;
        --ut-soft-peach: #fff0eb;
        --ut-green: #dff4d8;
        --ut-yellow: #fff2bd;
        --ut-blue: #ddeeff;

        width: 100%;
        overflow: hidden;
        background: #ffffff;
        color: var(--ut-ink);
    }

    .ut-home *,
    .ut-home *::before,
    .ut-home *::after {
        box-sizing: border-box;
    }

    .ut-container {
        width: calc(100% - 40px);
        max-width: 1180px;
        margin: 0 auto;
    }

    /*
    |--------------------------------------------------------------------------
    | HERO
    |--------------------------------------------------------------------------
    */

    .ut-hero {
        position: relative;
        min-height: calc(100vh - 64px);
        display: flex;
        align-items: center;
        padding: 80px 0 70px;
        background:
            radial-gradient(
                circle at 82% 18%,
                rgba(255, 215, 170, .38),
                transparent 29%
            ),
            radial-gradient(
                circle at 18% 80%,
                rgba(216, 237, 255, .42),
                transparent 25%
            ),
            linear-gradient(
                180deg,
                #fffdf9 0%,
                #ffffff 78%
            );
    }

    .ut-hero-grid {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns:
            minmax(0, 1.04fr)
            minmax(420px, .96fr);
        gap: 70px;
        align-items: center;
    }

    .ut-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 24px;
        padding: 7px 11px;
        border: 1px solid rgba(23,23,25,.1);
        border-radius: 999px;
        background: rgba(255,255,255,.74);
        backdrop-filter: blur(12px);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .ut-kicker-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--ut-peach);
        box-shadow:
            0 0 0 5px rgba(255,113,93,.12);
    }

    .ut-hero-title {
        max-width: 720px;
        margin: 0;
        font-size:
            clamp(52px, 6.3vw, 94px);
        line-height: .91;
        letter-spacing: -.072em;
        font-weight: 790;
    }

    .ut-hero-title span {
        color: var(--ut-peach);
    }

    .ut-hero-copy {
        max-width: 600px;
        margin: 28px 0 0;
        color: var(--ut-muted);
        font-size: 17px;
        line-height: 1.65;
    }

    .ut-hero-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 32px;
    }

    .ut-primary,
    .ut-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        padding: 0 20px;
        border-radius: 13px;
        font-size: 13px;
        font-weight: 700;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }

    .ut-primary {
        background: var(--ut-ink);
        color: #ffffff;
        box-shadow:
            0 12px 26px rgba(23,23,25,.14);
    }

    .ut-primary:hover {
        transform: translateY(-2px);
        background: #2a2a2e;
    }

    .ut-secondary {
        border: 1px solid var(--ut-line);
        background: rgba(255,255,255,.85);
        color: var(--ut-ink);
    }

    .ut-secondary:hover {
        transform: translateY(-2px);
        background: #ffffff;
    }

    .ut-mini-proof {
        display: flex;
        flex-wrap: wrap;
        gap: 17px;
        margin-top: 32px;
        color: #77777d;
        font-size: 12px;
    }

    .ut-mini-proof span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .ut-mini-proof i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #74b66a;
    }

    /*
    |--------------------------------------------------------------------------
    | HERO VISUAL
    |--------------------------------------------------------------------------
    */

    .ut-visual {
        position: relative;
        min-height: 590px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ut-phone {
        position: relative;
        z-index: 3;
        width: 300px;
        padding: 10px;
        border: 1px solid rgba(23,23,25,.11);
        border-radius: 39px;
        background: #19191b;
        box-shadow:
            0 40px 90px rgba(36,31,26,.2);
        transform: rotate(2.5deg);
    }

    .ut-phone-screen {
        min-height: 560px;
        overflow: hidden;
        border-radius: 31px;
        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.02),
                rgba(255,255,255,.7)
            ),
            #f6ede3;
    }

    .ut-phone-top {
        padding: 26px 24px 20px;
        text-align: center;
    }

    .ut-phone-label {
        color: #8d7d71;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .ut-phone-names {
        margin-top: 15px;
        font-family:
            Georgia,
            "Times New Roman",
            serif;
        font-size: 35px;
        line-height: .95;
        letter-spacing: -.04em;
        color: #4c4037;
    }

    .ut-phone-date {
        margin-top: 12px;
        color: #8b796b;
        font-size: 10px;
    }

    .ut-photo-stage {
        margin: 0 16px;
        aspect-ratio: .82;
        border-radius: 26px;
        overflow: hidden;
        position: relative;
        background:
            linear-gradient(
                155deg,
                #c7b09d 0%,
                #846b59 48%,
                #2f2925 100%
            );
    }

    .ut-photo-stage::before,
    .ut-photo-stage::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        filter: blur(1px);
    }

    .ut-photo-stage::before {
        width: 180px;
        height: 260px;
        left: -35px;
        bottom: -110px;
        background: rgba(255,231,204,.72);
        transform: rotate(20deg);
    }

    .ut-photo-stage::after {
        width: 150px;
        height: 240px;
        right: -35px;
        top: 65px;
        background: rgba(59,50,44,.62);
        transform: rotate(-18deg);
    }

    .ut-photo-text {
        position: absolute;
        left: 20px;
        right: 20px;
        bottom: 20px;
        z-index: 2;
        padding: 14px;
        border: 1px solid rgba(255,255,255,.26);
        border-radius: 16px;
        background: rgba(255,255,255,.16);
        backdrop-filter: blur(12px);
        color: white;
        font-size: 11px;
        line-height: 1.5;
    }

    .ut-phone-bottom {
        padding: 18px 18px 22px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px;
    }

    .ut-phone-chip {
        padding: 10px 11px;
        border-radius: 13px;
        background: rgba(255,255,255,.6);
        color: #65564a;
        font-size: 9px;
        line-height: 1.4;
    }

    /*
    |--------------------------------------------------------------------------
    | FLOATING ACTIVITY CARDS
    |--------------------------------------------------------------------------
    */

    .ut-float {
        position: absolute;
        z-index: 5;
        display: flex;
        align-items: center;
        gap: 11px;
        max-width: 230px;
        padding: 11px 13px;
        border: 1px solid rgba(23,23,25,.09);
        border-radius: 15px;
        background: rgba(255,255,255,.9);
        box-shadow:
            0 18px 40px rgba(33,30,26,.1);
        backdrop-filter: blur(12px);
        animation:
            utFloat 5s ease-in-out infinite;
    }

    .ut-float:nth-of-type(2) {
        animation-delay: -1.4s;
    }

    .ut-float:nth-of-type(3) {
        animation-delay: -2.8s;
    }

    .ut-float-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        font-size: 15px;
    }

    .ut-float strong {
        display: block;
        font-size: 11px;
        line-height: 1.3;
    }

    .ut-float small {
        display: block;
        margin-top: 3px;
        color: #83838a;
        font-size: 9px;
    }

    .ut-float-a {
        left: 0;
        top: 115px;
    }

    .ut-float-b {
        right: -8px;
        top: 215px;
    }

    .ut-float-c {
        left: 12px;
        bottom: 90px;
    }

    .ut-float-a .ut-float-icon {
        background: var(--ut-green);
    }

    .ut-float-b .ut-float-icon {
        background: var(--ut-yellow);
    }

    .ut-float-c .ut-float-icon {
        background: var(--ut-blue);
    }

    @keyframes utFloat {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-9px);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATEMENT
    |--------------------------------------------------------------------------
    */

    .ut-statement {
        padding: 115px 0;
        border-top: 1px solid #f0efed;
        background: #ffffff;
    }

    .ut-statement-inner {
        max-width: 940px;
        margin: 0 auto;
        text-align: center;
    }

    .ut-statement h2 {
        margin: 0;
        font-size:
            clamp(40px, 5vw, 68px);
        line-height: .98;
        letter-spacing: -.055em;
    }

    .ut-statement p {
        max-width: 650px;
        margin: 24px auto 0;
        color: var(--ut-muted);
        font-size: 16px;
        line-height: 1.7;
    }


    /*
    |--------------------------------------------------------------------------
    | THEME SHOWCASE
    |--------------------------------------------------------------------------
    */

    .ut-themes {
        padding: 0 0 120px;
        background: #ffffff;
    }

    .ut-themes-head {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(320px, .6fr);
        gap: 48px;
        align-items: end;
        margin-bottom: 42px;
    }

    .ut-themes-head small {
        display: block;
        margin-bottom: 12px;
        color: #88888e;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ut-themes-head h2 {
        max-width: 760px;
        margin: 0;
        font-size: clamp(42px, 5vw, 68px);
        line-height: .98;
        letter-spacing: -.055em;
    }

    .ut-themes-head p {
        max-width: 460px;
        margin: 0;
        justify-self: end;
        color: var(--ut-muted);
        font-size: 15px;
        line-height: 1.7;
    }

    .ut-theme-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .ut-theme-card {
        position: relative;
        min-height: 670px;
        overflow: hidden;
        border: 1px solid var(--ut-line);
        border-radius: 30px;
        background: #f7f5f1;
        transition:
            transform .28s ease,
            box-shadow .28s ease,
            border-color .28s ease;
    }

    .ut-theme-card:hover {
        transform: translateY(-5px);
        border-color: #d7d3cd;
        box-shadow: 0 26px 70px rgba(34, 31, 27, .12);
    }

    .ut-theme-card-top {
        position: relative;
        z-index: 3;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 28px 28px 0;
    }

    .ut-theme-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #77777d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .ut-theme-card h3 {
        margin: 0;
        font-size: 34px;
        line-height: 1;
        letter-spacing: -.045em;
    }

    .ut-theme-card-top p {
        max-width: 330px;
        margin: 10px 0 0;
        color: var(--ut-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .ut-theme-badge {
        flex: 0 0 auto;
        padding: 7px 10px;
        border: 1px solid rgba(23,23,25,.09);
        border-radius: 999px;
        background: rgba(255,255,255,.74);
        color: #4f4f54;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .ut-theme-preview {
        position: absolute;
        left: 50%;
        bottom: -118px;
        width: min(68%, 345px);
        min-height: 500px;
        transform: translateX(-50%) rotate(-3deg);
        overflow: hidden;
        border: 10px solid #19191b;
        border-bottom-width: 30px;
        border-radius: 34px;
        box-shadow: 0 38px 80px rgba(44,39,34,.18);
    }

    .ut-theme-card:nth-child(2) .ut-theme-preview {
        transform: translateX(-50%) rotate(3deg);
    }

    .ut-theme-modern {
        background:
            linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.7)),
            #eee7df;
    }

    .ut-theme-classic {
        background:
            linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.58)),
            #ebe1d2;
    }

    .ut-theme-mini {
        padding: 30px 22px 24px;
        text-align: center;
    }

    .ut-theme-mini small {
        display: block;
        color: #897d74;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .ut-theme-mini-name {
        margin-top: 18px;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 34px;
        line-height: .95;
        letter-spacing: -.04em;
        color: #4f433a;
    }

    .ut-theme-modern .ut-theme-mini-name {
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-weight: 760;
        color: #27272a;
    }

    .ut-theme-mini-date {
        margin-top: 12px;
        color: #8d8178;
        font-size: 9px;
    }

    .ut-theme-photo {
        position: relative;
        margin: 22px 18px 0;
        aspect-ratio: .82;
        overflow: hidden;
        border-radius: 22px;
        background:
            linear-gradient(150deg, #cab39f 0%, #866f5c 46%, #342d28 100%);
    }

    .ut-theme-card:nth-child(2) .ut-theme-photo {
        background:
            linear-gradient(150deg, #d7c5ab 0%, #8d7458 45%, #42352a 100%);
    }

    .ut-theme-photo::before,
    .ut-theme-photo::after {
        content: "";
        position: absolute;
        border-radius: 999px;
    }

    .ut-theme-photo::before {
        width: 170px;
        height: 250px;
        left: -40px;
        bottom: -95px;
        background: rgba(255,237,217,.65);
        transform: rotate(18deg);
    }

    .ut-theme-photo::after {
        width: 145px;
        height: 230px;
        right: -35px;
        top: 56px;
        background: rgba(53,46,41,.58);
        transform: rotate(-16deg);
    }

    .ut-theme-caption {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 16px;
        z-index: 2;
        padding: 12px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 14px;
        background: rgba(255,255,255,.14);
        backdrop-filter: blur(10px);
        color: #ffffff;
        font-size: 10px;
        line-height: 1.45;
    }

    .ut-theme-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 28px;
    }

    .ut-theme-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 18px;
        border: 1px solid var(--ut-line);
        border-radius: 12px;
        background: #ffffff;
        color: var(--ut-ink);
        font-size: 12px;
        font-weight: 700;
        transition:
            transform .2s ease,
            border-color .2s ease;
    }

    .ut-theme-link:hover {
        transform: translateY(-2px);
        border-color: #cfcfcf;
    }

    /*
    |--------------------------------------------------------------------------
    | FEATURE STORY
    |--------------------------------------------------------------------------
    */

    .ut-features {
        padding: 0 0 120px;
        background: #ffffff;
    }

    .ut-feature {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 520px;
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid var(--ut-line);
        border-radius: 30px;
        background: #fafafa;
    }

    .ut-feature:nth-child(even) .ut-feature-copy {
        order: 2;
    }

    .ut-feature:nth-child(even) .ut-feature-art {
        order: 1;
    }

    .ut-feature-copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px;
    }

    .ut-number {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        margin-bottom: 28px;
        border-radius: 50%;
        background: var(--ut-ink);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
    }

    .ut-feature-copy h3 {
        max-width: 470px;
        margin: 0;
        font-size:
            clamp(34px, 4vw, 58px);
        line-height: .98;
        letter-spacing: -.05em;
    }

    .ut-feature-copy p {
        max-width: 470px;
        margin: 20px 0 0;
        color: var(--ut-muted);
        font-size: 15px;
        line-height: 1.7;
    }

    .ut-feature-art {
        position: relative;
        min-height: 420px;
        overflow: hidden;
    }

    .ut-art-one {
        background:
            radial-gradient(
                circle at 40% 40%,
                #fff7d7,
                transparent 35%
            ),
            #f0e9ff;
    }

    .ut-art-two {
        background:
            radial-gradient(
                circle at 70% 30%,
                #ffe3db,
                transparent 33%
            ),
            #eaf5ef;
    }

    .ut-art-three {
        background:
            radial-gradient(
                circle at 50% 20%,
                #dfefff,
                transparent 38%
            ),
            #fff3e7;
    }

    .ut-ui-card {
        position: absolute;
        left: 50%;
        top: 50%;
        width: min(76%, 410px);
        transform: translate(-50%, -50%);
        padding: 20px;
        border: 1px solid rgba(23,23,25,.1);
        border-radius: 22px;
        background: rgba(255,255,255,.88);
        box-shadow:
            0 30px 70px rgba(45,42,38,.14);
        backdrop-filter: blur(16px);
    }

    .ut-ui-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .ut-ui-title {
        font-size: 12px;
        font-weight: 700;
    }

    .ut-ui-status {
        padding: 5px 8px;
        border-radius: 999px;
        background: #e7f5e2;
        color: #467b3d;
        font-size: 9px;
        font-weight: 700;
    }

    .ut-ui-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 0;
        border-top: 1px solid #ecebea;
    }

    .ut-ui-row:first-of-type {
        border-top: 0;
    }

    .ut-ui-row strong {
        font-size: 11px;
    }

    .ut-ui-row span {
        color: #84848a;
        font-size: 9px;
    }

    .ut-progress {
        height: 7px;
        margin-top: 18px;
        overflow: hidden;
        border-radius: 999px;
        background: #ecebea;
    }

    .ut-progress > div {
        width: 78%;
        height: 100%;
        border-radius: inherit;
        background: var(--ut-peach);
    }

    /*
    |--------------------------------------------------------------------------
    | HOW IT WORKS
    |--------------------------------------------------------------------------
    */

    .ut-how {
        padding: 110px 0;
        background: #f7f7f5;
    }

    .ut-section-head {
        max-width: 680px;
        margin-bottom: 50px;
    }

    .ut-section-head small {
        display: block;
        margin-bottom: 12px;
        color: #88888e;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ut-section-head h2 {
        margin: 0;
        font-size:
            clamp(40px, 5vw, 66px);
        line-height: .98;
        letter-spacing: -.055em;
    }

    .ut-steps {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .ut-step {
        min-height: 260px;
        display: flex;
        flex-direction: column;
        padding: 24px;
        border: 1px solid #e2e2df;
        border-radius: 22px;
        background: #ffffff;
    }

    .ut-step-index {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--ut-ink);
        color: white;
        font-size: 10px;
        font-weight: 700;
    }

    .ut-step h3 {
        margin:
            auto 0 8px;
        font-size: 20px;
        letter-spacing: -.025em;
    }

    .ut-step p {
        margin: 0;
        color: var(--ut-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL CTA
    |--------------------------------------------------------------------------
    */

    .ut-final {
        padding: 100px 0;
        background: #19191b;
        color: #ffffff;
    }

    .ut-final-box {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        align-items: end;
        gap: 50px;
    }

    .ut-final h2 {
        max-width: 720px;
        margin: 0;
        font-size:
            clamp(48px, 6vw, 84px);
        line-height: .92;
        letter-spacing: -.065em;
    }

    .ut-final-side {
        justify-self: end;
        max-width: 350px;
    }

    .ut-final-side p {
        margin: 0 0 22px;
        color: #b9b9bf;
        font-size: 14px;
        line-height: 1.7;
    }

    .ut-final .ut-primary {
        background: #ffffff;
        color: #19191b;
    }

    /*
    |--------------------------------------------------------------------------
    | FOOTER
    |--------------------------------------------------------------------------
    */

    .ut-footer {
        padding: 28px 0;
        border-top: 1px solid #303034;
        background: #19191b;
        color: #8f8f96;
        font-size: 11px;
    }

    .ut-footer-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .ut-footer strong {
        color: #ffffff;
    }

    /*
    |--------------------------------------------------------------------------
    | SCROLL REVEAL
    |--------------------------------------------------------------------------
    */

    .ut-reveal {
        opacity: 0;
        transform: translateY(24px);
        transition:
            opacity .7s ease,
            transform .7s ease;
    }

    .ut-reveal.ut-visible {
        opacity: 1;
        transform: translateY(0);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLET
    |--------------------------------------------------------------------------
    */

    @media(max-width: 980px) {

        .ut-hero-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .ut-hero-copy {
            max-width: 680px;
        }

        .ut-visual {
            min-height: 650px;
        }

        .ut-float-a {
            left: 10%;
        }

        .ut-float-b {
            right: 9%;
        }

        .ut-float-c {
            left: 12%;
        }

        .ut-themes-head {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .ut-themes-head p {
            justify-self: start;
        }

        .ut-theme-grid {
            grid-template-columns: 1fr;
        }

        .ut-theme-card {
            min-height: 650px;
        }

        .ut-feature {
            grid-template-columns: 1fr;
        }

        .ut-feature:nth-child(even) .ut-feature-copy,
        .ut-feature:nth-child(even) .ut-feature-art {
            order: initial;
        }

        .ut-feature-copy {
            padding: 45px;
        }

        .ut-steps {
            grid-template-columns:
                repeat(2, minmax(0,1fr));
        }

        .ut-final-box {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .ut-final-side {
            justify-self: start;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media(max-width: 680px) {

        .ut-container {
            width: calc(100% - 28px);
        }

        .ut-hero {
            min-height: auto;
            padding: 60px 0 45px;
        }

        .ut-hero-title {
            font-size:
                clamp(46px, 14vw, 66px);
        }

        .ut-hero-copy {
            margin-top: 21px;
            font-size: 15px;
        }

        .ut-hero-actions {
            align-items: stretch;
        }

        .ut-primary,
        .ut-secondary {
            width: 100%;
        }

        .ut-mini-proof {
            gap: 10px 16px;
        }

        .ut-visual {
            min-height: 540px;
            margin-top: 10px;
        }

        .ut-phone {
            width: 245px;
        }

        .ut-phone-screen {
            min-height: 455px;
        }

        .ut-phone-names {
            font-size: 29px;
        }

        .ut-float {
            max-width: 175px;
            padding: 9px 10px;
        }

        .ut-float-icon {
            width: 29px;
            height: 29px;
            flex-basis: 29px;
        }

        .ut-float-a {
            left: -2px;
            top: 95px;
        }

        .ut-float-b {
            right: -2px;
            top: 205px;
        }

        .ut-float-c {
            left: 2px;
            bottom: 78px;
        }

        .ut-statement {
            padding: 80px 0;
        }

        .ut-statement h2 {
            font-size: 42px;
        }

        .ut-themes {
            padding-bottom: 80px;
        }

        .ut-themes-head {
            margin-bottom: 28px;
        }

        .ut-themes-head h2 {
            font-size: 42px;
        }

        .ut-theme-card {
            min-height: 560px;
            border-radius: 22px;
        }

        .ut-theme-card-top {
            padding: 22px 20px 0;
        }

        .ut-theme-card h3 {
            font-size: 30px;
        }

        .ut-theme-card-top p {
            max-width: 250px;
            font-size: 12px;
        }

        .ut-theme-preview {
            width: min(72%, 280px);
            min-height: 420px;
            bottom: -118px;
        }

        .ut-feature {
            min-height: auto;
            border-radius: 22px;
        }

        .ut-feature-copy {
            padding: 32px 24px 36px;
        }

        .ut-feature-art {
            min-height: 350px;
        }

        .ut-ui-card {
            width: 82%;
        }

        .ut-how {
            padding: 75px 0;
        }

        .ut-steps {
            grid-template-columns: 1fr;
        }

        .ut-step {
            min-height: 190px;
        }

        .ut-final {
            padding: 75px 0;
        }

        .ut-final h2 {
            font-size: 49px;
        }

        .ut-footer-inner {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REDUCED MOTION
    |--------------------------------------------------------------------------
    */

    @media(prefers-reduced-motion: reduce) {

        .ut-float {
            animation: none;
        }

        .ut-reveal {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }
</style>


<div class="ut-home">

    <section class="ut-hero">

        <div class="ut-container">

            <div class="ut-hero-grid">

                <div>

                    <div class="ut-kicker">
                        <span class="ut-kicker-dot"></span>
                        Undangan digital yang terasa personal
                    </div>

                    <h1 class="ut-hero-title">
                        Momen baik,
                        <span>dibagikan dengan indah.</span>
                    </h1>

                    <p class="ut-hero-copy">
                        Buat undangan digital personal untuk hari spesialmu.
                        Kelola tamu, RSVP, wishes, album, amplop digital,
                        sampai check-in dari satu tempat.
                    </p>

                    <div class="ut-hero-actions">

                        @auth

                            <a
                                href="{{ route('dashboard') }}"
                                class="ut-primary"
                            >
                                Buka dashboard
                            </a>

                        @else

                            <a
                                href="{{ route('register') }}"
                                class="ut-primary"
                            >
                                Buat undangan sekarang
                            </a>

                        @endauth

                        <a
                            href="#cara-kerja"
                            class="ut-secondary"
                        >
                            Lihat cara kerja
                        </a>

                    </div>

                    <div class="ut-mini-proof">

                        <span>
                            <i></i>
                            Link tamu personal
                        </span>

                        <span>
                            <i></i>
                            RSVP real-time
                        </span>

                        <span>
                            <i></i>
                            Check-in QR
                        </span>

                    </div>

                </div>


                <div class="ut-visual">

                    <div class="ut-float ut-float-a">

                        <div class="ut-float-icon">
                            ✓
                        </div>

                        <div>
                            <strong>RSVP diterima</strong>
                            <small>2 tamu akan hadir</small>
                        </div>

                    </div>


                    <div class="ut-float ut-float-b">

                        <div class="ut-float-icon">
                            ♡
                        </div>

                        <div>
                            <strong>Ucapan baru</strong>
                            <small>Semoga bahagia selalu</small>
                        </div>

                    </div>


                    <div class="ut-float ut-float-c">

                        <div class="ut-float-icon">
                            ↗
                        </div>

                        <div>
                            <strong>Tamu check-in</strong>
                            <small>QR berhasil dipindai</small>
                        </div>

                    </div>


                    <div class="ut-phone">

                        <div class="ut-phone-screen">

                            <div class="ut-phone-top">

                                <div class="ut-phone-label">
                                    The Wedding Of
                                </div>

                                <div class="ut-phone-names">
                                    Dims
                                    <br>
                                    & Rara
                                </div>

                                <div class="ut-phone-date">
                                    Minggu · 18 Oktober 2026
                                </div>

                            </div>


                            <div class="ut-photo-stage">

                                <div class="ut-photo-text">
                                    Dengan penuh kebahagiaan,
                                    kami mengundang Anda untuk
                                    menjadi bagian dari hari istimewa kami.
                                </div>

                            </div>


                            <div class="ut-phone-bottom">

                                <div class="ut-phone-chip">
                                    <strong>Akad</strong><br>
                                    09.00 WITA
                                </div>

                                <div class="ut-phone-chip">
                                    <strong>Resepsi</strong><br>
                                    11.00 WITA
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <section class="ut-statement">

        <div class="ut-container">

            <div class="ut-statement-inner ut-reveal">

                <h2>
                    Bukan sekadar link undangan.
                    Semua kebutuhan acaramu
                    ada di satu tempat.
                </h2>

                <p>
                    Dari tamu pertama yang membuka undangan
                    sampai tamu terakhir melakukan check-in,
                    UNDANGANTA.ID membantu semuanya tetap rapi.
                </p>

            </div>

        </div>

    </section>



    <section class="ut-themes">

        <div class="ut-container">

            <div class="ut-themes-head ut-reveal">

                <div>

                    <small>
                        Pilih tema
                    </small>

                    <h2>
                        Pilih tampilan yang terasa paling kamu.
                    </h2>

                </div>

                <p>
                    Mulai dari gaya modern yang clean sampai nuansa klasik
                    yang lebih hangat dan elegan. Lihat preview sebelum
                    mulai mengisi undangan.
                </p>

            </div>


            <div class="ut-theme-grid">

                <article class="ut-theme-card ut-reveal">

                    <div class="ut-theme-card-top">

                        <div>

                            <div class="ut-theme-label">
                                Tema 01
                            </div>

                            <h3>
                                Modern
                            </h3>

                            <p>
                                Minimal, clean, contemporary.
                                Cocok untuk tampilan yang ringan dan fokus ke konten.
                            </p>

                        </div>

                        <div class="ut-theme-badge">
                            Available
                        </div>

                    </div>


                    <div class="ut-theme-preview ut-theme-modern">

                        <div class="ut-theme-mini">

                            <small>
                                The Wedding Of
                            </small>

                            <div class="ut-theme-mini-name">
                                Dims
                                <br>
                                & Rara
                            </div>

                            <div class="ut-theme-mini-date">
                                18 Oktober 2026
                            </div>

                        </div>

                        <div class="ut-theme-photo">

                            <div class="ut-theme-caption">
                                Save the date untuk hari istimewa kami.
                            </div>

                        </div>

                    </div>

                </article>


                <article class="ut-theme-card ut-reveal">

                    <div class="ut-theme-card-top">

                        <div>

                            <div class="ut-theme-label">
                                Tema 02
                            </div>

                            <h3>
                                Classic
                            </h3>

                            <p>
                                Elegant, warm, timeless.
                                Untuk nuansa yang lebih formal dan premium.
                            </p>

                        </div>

                        <div class="ut-theme-badge">
                            Premium
                        </div>

                    </div>


                    <div class="ut-theme-preview ut-theme-classic">

                        <div class="ut-theme-mini">

                            <small>
                                The Wedding Of
                            </small>

                            <div class="ut-theme-mini-name">
                                Dims
                                <br>
                                & Rara
                            </div>

                            <div class="ut-theme-mini-date">
                                18 Oktober 2026
                            </div>

                        </div>

                        <div class="ut-theme-photo">

                            <div class="ut-theme-caption">
                                Dengan penuh kebahagiaan,
                                kami mengundang Anda di hari istimewa kami.
                            </div>

                        </div>

                    </div>

                </article>

            </div>


            <div class="ut-theme-actions ut-reveal">

                @auth

                    <a
                        href="{{ route('dashboard') }}"
                        class="ut-theme-link"
                    >
                        Lihat tema di dashboard
                    </a>

                @else

                    <a
                        href="{{ route('register') }}"
                        class="ut-theme-link"
                    >
                        Lihat semua tema
                    </a>

                @endauth

            </div>

        </div>

    </section>


    <section class="ut-features">

        <div class="ut-container">


            <article class="ut-feature ut-reveal">

                <div class="ut-feature-copy">

                    <div class="ut-number">
                        01
                    </div>

                    <h3>
                        Undangan yang terasa milikmu.
                    </h3>

                    <p>
                        Pilih tema, atur pasangan, acara,
                        foto, cerita, dan detail penting
                        tanpa membuat semuanya terasa seperti template biasa.
                    </p>

                </div>


                <div class="ut-feature-art ut-art-one">

                    <div class="ut-ui-card">

                        <div class="ut-ui-head">

                            <div class="ut-ui-title">
                                Tema undangan
                            </div>

                            <div class="ut-ui-status">
                                Aktif
                            </div>

                        </div>

                        <div class="ut-ui-row">
                            <strong>Modern</strong>
                            <span>Preview</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>Classic</strong>
                            <span>Premium</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>Foto pasangan</strong>
                            <span>Diatur</span>
                        </div>

                        <div class="ut-progress">
                            <div></div>
                        </div>

                    </div>

                </div>

            </article>


            <article class="ut-feature ut-reveal">

                <div class="ut-feature-copy">

                    <div class="ut-number">
                        02
                    </div>

                    <h3>
                        Tahu siapa yang akan datang.
                    </h3>

                    <p>
                        Kelola daftar tamu, buat link personal,
                        pantau RSVP, jumlah kehadiran,
                        dan status check-in dalam satu dashboard.
                    </p>

                </div>


                <div class="ut-feature-art ut-art-two">

                    <div class="ut-ui-card">

                        <div class="ut-ui-head">

                            <div class="ut-ui-title">
                                Daftar tamu
                            </div>

                            <div class="ut-ui-status">
                                Live
                            </div>

                        </div>

                        <div class="ut-ui-row">
                            <strong>Andi Pratama</strong>
                            <span>Hadir · 2 orang</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>Salsa Putri</strong>
                            <span>Hadir · 1 orang</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>M. Iqbal</strong>
                            <span>Menunggu RSVP</span>
                        </div>

                    </div>

                </div>

            </article>


            <article class="ut-feature ut-reveal">

                <div class="ut-feature-copy">

                    <div class="ut-number">
                        03
                    </div>

                    <h3>
                        Hari H jadi lebih sederhana.
                    </h3>

                    <p>
                        QR check-in membantu penerimaan tamu
                        lebih cepat, sementara wishes dan album tamu
                        tetap tersimpan dalam undangan yang sama.
                    </p>

                </div>


                <div class="ut-feature-art ut-art-three">

                    <div class="ut-ui-card">

                        <div class="ut-ui-head">

                            <div class="ut-ui-title">
                                Check-in tamu
                            </div>

                            <div class="ut-ui-status">
                                Berhasil
                            </div>

                        </div>

                        <div class="ut-ui-row">
                            <strong>QR terverifikasi</strong>
                            <span>10:42 WITA</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>Nama tamu</strong>
                            <span>Andi Pratama</span>
                        </div>

                        <div class="ut-ui-row">
                            <strong>Status</strong>
                            <span>Sudah check-in</span>
                        </div>

                    </div>

                </div>

            </article>


        </div>

    </section>


    <section
        class="ut-how"
        id="cara-kerja"
    >

        <div class="ut-container">

            <div class="ut-section-head ut-reveal">

                <small>
                    Cara kerja
                </small>

                <h2>
                    Dari pilih tema sampai siap dibagikan.
                </h2>

            </div>


            <div class="ut-steps">

                <div class="ut-step ut-reveal">

                    <div class="ut-step-index">
                        1
                    </div>

                    <h3>
                        Pilih paket
                    </h3>

                    <p>
                        Tentukan paket sesuai kebutuhan acaramu.
                    </p>

                </div>


                <div class="ut-step ut-reveal">

                    <div class="ut-step-index">
                        2
                    </div>

                    <h3>
                        Pilih tema
                    </h3>

                    <p>
                        Lihat gaya visual sebelum mulai mengisi undangan.
                    </p>

                </div>


                <div class="ut-step ut-reveal">

                    <div class="ut-step-index">
                        3
                    </div>

                    <h3>
                        Isi undangan
                    </h3>

                    <p>
                        Tambahkan pasangan, acara, foto, dan detail lainnya.
                    </p>

                </div>


                <div class="ut-step ut-reveal">

                    <div class="ut-step-index">
                        4
                    </div>

                    <h3>
                        Bagikan
                    </h3>

                    <p>
                        Kirim link personal dan kelola tamu dari dashboard.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <section class="ut-final">

        <div class="ut-container">

            <div class="ut-final-box ut-reveal">

                <h2>
                    Buat undangan yang ingin orang buka.
                </h2>

                <div class="ut-final-side">

                    <p>
                        Mulai dari tema yang kamu suka,
                        lalu jadikan setiap detail terasa personal.
                    </p>

                    @auth

                        <a
                            href="{{ route('dashboard') }}"
                            class="ut-primary"
                        >
                            Masuk ke dashboard
                        </a>

                    @else

                        <a
                            href="{{ route('register') }}"
                            class="ut-primary"
                        >
                            Mulai buat undangan
                        </a>

                    @endauth

                </div>

            </div>

        </div>

    </section>


    <footer class="ut-footer">

        <div class="ut-container">

            <div class="ut-footer-inner">

                <div>
                    <strong>UNDANGANTA.ID</strong>
                </div>

                <div>
                    Copyright DIMS 2026
                </div>

            </div>

        </div>

    </footer>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const items = document.querySelectorAll('.ut-reveal');

        if (
            !('IntersectionObserver' in window)
            || window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            items.forEach(function (item) {
                item.classList.add('ut-visible');
            });

            return;
        }

        const observer = new IntersectionObserver(
            function (entries) {

                entries.forEach(function (entry) {

                    if (entry.isIntersecting) {
                        entry.target.classList.add('ut-visible');
                        observer.unobserve(entry.target);
                    }

                });

            },
            {
                threshold: 0.12
            }
        );

        items.forEach(function (item) {
            observer.observe(item);
        });

    });
</script>

@endsection