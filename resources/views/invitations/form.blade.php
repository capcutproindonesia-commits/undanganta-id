@extends('layouts.app')

@section('content')

@php
    $gift = collect($invitation->gift_accounts ?? [])->first();
    $sections = $invitation->sections ?? [];

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

    $sectionLabels = [
        'countdown' => 'Save the Date & Countdown',
        'quote' => 'Quote',
        'groom' => 'The Groom',
        'bride' => 'The Bride',
        'story' => 'Cerita',
        'gallery' => 'Galeri',
        'event' => 'Lokasi & Acara',
        'rsvp' => 'RSVP',
        'gift' => 'Amplop Digital',
        'guest_photo' => 'Guest Moment',
        'wishes' => 'Ucapan & Doa',
    ];

    $defaultSectionOrder = array_keys($sectionLabels);

    $savedOrder = $sections['order'] ?? $defaultSectionOrder;

    $oldOrder = json_decode(
        old('section_order', ''),
        true
    );

    if (is_array($oldOrder)) {
        $savedOrder = $oldOrder;
    }

    $sectionOrder = collect($savedOrder)
        ->filter(fn ($key) => isset($sectionLabels[$key]))
        ->unique()
        ->values()
        ->all();

    foreach ($defaultSectionOrder as $key) {
        if (!in_array($key, $sectionOrder, true)) {
            $sectionOrder[] = $key;
        }
    }
@endphp


<style>

    /*
    |--------------------------------------------------------------------------
    | EDITOR PAGE ONLY
    |--------------------------------------------------------------------------
    */

    .edit-page {
        width: 100%;
        max-width: 1160px;
        margin: 0 auto;
        padding: 34px 22px 54px;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .edit-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 26px;
    }

    .edit-kicker {
        margin-bottom: 5px;
        color: #86868b;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .04em;
    }

    .edit-title {
        margin: 0;
        color: #1d1d1f;
        font-size: 34px;
        line-height: 1.08;
        font-weight: 700;
        letter-spacing: -.04em;
    }

    .edit-description {
        margin: 8px 0 0;
        color: #86868b;
        font-size: 13px;
        line-height: 1.5;
    }

    .edit-head-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        flex-wrap: wrap;
    }


    /*
    |--------------------------------------------------------------------------
    | DARK CAPSULE
    |--------------------------------------------------------------------------
    */

    .edit-primary {
        position: relative;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;

        min-height: 38px;
        padding: 0 15px;

        border: 1px solid rgba(255,255,255,.09);
        border-radius: 999px;

        background:
            linear-gradient(
                180deg,
                #303033 0%,
                #131315 100%
            );

        color: #fff;

        font-size: 11px;
        font-weight: 650;
        line-height: 1;

        white-space: nowrap;
        cursor: pointer;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.16),
            inset 0 -1px 0 rgba(0,0,0,.25),
            0 4px 12px rgba(0,0,0,.12);

        transition:
            transform .16s ease,
            box-shadow .16s ease,
            background .16s ease;
    }

    .edit-primary::before {
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

    .edit-primary:hover {
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

    .edit-primary:active {
        transform: scale(.97);
    }


    /*
    |--------------------------------------------------------------------------
    | LIGHT CAPSULE
    |--------------------------------------------------------------------------
    */

    .edit-light {
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
                rgba(255,255,255,.98),
                rgba(244,244,246,.96)
            );

        color: #3a3a3c;

        font-size: 10px;
        font-weight: 600;
        line-height: 1;

        white-space: nowrap;
        cursor: pointer;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,1),
            0 2px 5px rgba(0,0,0,.04);

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    .edit-light:hover {
        transform: translateY(-1px);
        background: #fff;
        box-shadow: 0 4px 9px rgba(0,0,0,.06);
    }

    .edit-light:active {
        transform: scale(.97);
    }

    .edit-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-height: 34px;
        padding: 0 12px;

        border: 1px solid #ebcecb;
        border-radius: 999px;

        background: #fff;
        color: #b42318;

        font-size: 10px;
        font-weight: 600;

        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILDER
    |--------------------------------------------------------------------------
    */

    .edit-layout {
        display: grid;
        grid-template-columns: 205px minmax(0,1fr);
        gap: 20px;
        align-items: start;
    }

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    .edit-side {
        position: sticky;
        top: 84px;

        padding: 8px;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #fff;
    }

    .edit-side-label {
        padding: 8px 10px 6px;

        color: #aeaeb2;

        font-size: 9px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: .09em;
    }

    .edit-side-link {
        display: flex;
        align-items: center;
        gap: 9px;

        min-height: 39px;
        padding: 0 11px;

        border-radius: 9px;

        color: #66666b;

        font-size: 11px;
        font-weight: 600;

        transition:
            background .15s ease,
            color .15s ease;
    }

    .edit-side-link:hover {
        background: #f7f7f8;
        color: #1d1d1f;
    }

    .edit-side-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;

        border-radius: 50%;
        background: #c7c7cc;
    }


    /*
    |--------------------------------------------------------------------------
    | CONTENT
    |--------------------------------------------------------------------------
    */

    .edit-content {
        min-width: 0;
    }

    .edit-card {
        width: 100%;
        min-width: 0;

        margin-bottom: 14px;
        padding: 20px;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #fff;
    }

    .edit-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;

        margin-bottom: 18px;
    }

    .edit-section-title {
        margin: 0;

        color: #1d1d1f;

        font-size: 16px;
        font-weight: 700;

        letter-spacing: -.02em;
    }

    .edit-section-copy {
        margin: 5px 0 0;

        color: #9a9a9f;

        font-size: 10px;
        line-height: 1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    .edit-grid {
        display: grid;
        grid-template-columns: repeat(2,minmax(0,1fr));
        gap: 14px;
    }

    .edit-grid.three {
        grid-template-columns: repeat(3,minmax(0,1fr));
    }

    .edit-grid > * {
        min-width: 0;
    }

    .edit-field {
        min-width: 0;
    }

    .edit-field + .edit-field {
        margin-top: 0;
    }

    .edit-label {
        display: block;

        margin-bottom: 7px;

        color: #515154;

        font-size: 11px;
        font-weight: 650;
    }

    .edit-input,
    .edit-select,
    .edit-textarea {
        width: 100%;
        max-width: 100%;

        min-height: 42px;

        padding: 10px 12px;

        border: 1px solid #dedee2;
        border-radius: 10px;

        outline: none;

        background: #fff;
        color: #1d1d1f;

        font-size: 12px;

        transition:
            border-color .15s ease,
            box-shadow .15s ease;
    }

    .edit-textarea {
        min-height: 100px;
        resize: vertical;
        line-height: 1.55;
    }

    .edit-input:focus,
    .edit-select:focus,
    .edit-textarea:focus {
        border-color: #9f9fa5;

        box-shadow:
            0 0 0 3px rgba(0,0,0,.035);
    }


    /*
    |--------------------------------------------------------------------------
    | COVER
    |--------------------------------------------------------------------------
    */

    .cover-layout {
        display: grid;
        grid-template-columns: 190px minmax(0,1fr);
        gap: 20px;
        align-items: start;
    }

    .cover-preview {
        width: 190px;
        aspect-ratio: 4/5;

        overflow: hidden;

        display: grid;
        place-items: center;

        border: 1px solid #e5e5e7;
        border-radius: 13px;

        background: #f5f5f7;
    }

    .cover-preview img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .cover-empty {
        padding: 18px;

        color: #8e8e93;

        font-size: 10px;
        text-align: center;
    }

    .upload-box {
        width: 100%;

        padding: 20px;

        border: 1px dashed #d2d2d7;
        border-radius: 12px;

        background: #fafafa;
    }

    .upload-title {
        margin-bottom: 4px;

        color: #1d1d1f;

        font-size: 12px;
        font-weight: 650;
    }

    .upload-copy {
        color: #9a9a9f;

        font-size: 10px;
        line-height: 1.45;
    }

    .upload-box input[type="file"] {
        display: block;

        width: 100%;
        max-width: 100%;

        margin-top: 13px;

        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | GALLERY
    |--------------------------------------------------------------------------
    */

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
        gap: 10px;

        margin-top: 16px;
    }

    .gallery-card {
        min-width: 0;

        overflow: hidden;

        border: 1px solid #e5e5e7;
        border-radius: 11px;

        background: #fff;

        cursor: grab;
    }

    .gallery-card.dragging {
        opacity: .45;
    }

    .gallery-image {
        width: 100%;
        aspect-ratio: 1/1;

        display: block;

        object-fit: cover;

        background: #f5f5f7;
    }

    .gallery-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;

        padding: 8px 9px;
    }

    .gallery-drag {
        color: #9a9a9f;

        font-size: 9px;
    }

    .gallery-delete {
        padding: 0;

        border: 0;

        background: transparent;
        color: #b42318;

        font-size: 9px;
        font-weight: 600;

        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLES
    |--------------------------------------------------------------------------
    */

    .toggle-grid {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
        gap: 9px;

        margin-top: 2px;
    }

    .toggle-item {
        min-width: 0;

        display: flex;
        align-items: center;
        gap: 8px;

        padding: 11px 12px;

        border: 1px solid #e5e5e7;
        border-radius: 10px;

        background: #fff;

        color: #515154;

        font-size: 10px;
        font-weight: 600;
    }

    .toggle-item input {
        width: auto;
        margin: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | COUPLE PROFILE
    |--------------------------------------------------------------------------
    */

    .couple-profile-grid {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
        margin-top:16px;
    }

    .couple-profile-card {
        padding:14px;
        border:1px solid #e5e5e7;
        border-radius:12px;
        background:#fafafa;
    }

    .couple-profile-title {
        margin-bottom:4px;
        color:#1d1d1f;
        font-size:12px;
        font-weight:700;
    }

    .couple-profile-copy {
        margin-bottom:12px;
        color:#9a9a9f;
        font-size:8px;
        line-height:1.45;
    }

    .couple-photo-preview {
        width:100%;
        max-width:220px;
        aspect-ratio:1/1;
        overflow:hidden;
        margin-bottom:12px;
        border:1px solid #e2e2e5;
        border-radius:10px;
        background:#f0f0f2;
    }

    .couple-photo-preview img {
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
    }

    .couple-profile-card .edit-field + .edit-field {
        margin-top:10px;
    }

    .photo-adjust-wrap {
        margin-bottom:12px;
    }

    .photo-adjust-stage {
        position:relative;

        width:100%;
        max-width:280px;

        aspect-ratio:4/5;

        overflow:hidden;

        border:1px solid #dedee3;
        border-radius:12px;

        background:
            linear-gradient(
                135deg,
                #f5f5f7,
                #ececf0
            );

        cursor:grab;

        touch-action:none;
        user-select:none;
    }

    .photo-adjust-stage.is-dragging {
        cursor:grabbing;
    }

    .photo-adjust-stage img {
        width:100%;
        height:100%;

        display:block;

        object-fit:cover;

        pointer-events:none;
        user-select:none;
    }

    .photo-adjust-empty {
        position:absolute;
        inset:0;

        display:grid;
        place-items:center;

        padding:20px;

        color:#9a9a9f;

        font-size:9px;
        line-height:1.5;

        text-align:center;
    }

    .photo-adjust-hint {
        margin-top:7px;

        color:#8e8e93;

        font-size:8px;
        line-height:1.45;
    }

    .photo-adjust-actions {
        display:flex;
        align-items:center;
        gap:8px;

        margin-top:8px;
    }

    .photo-adjust-reset {
        min-height:32px;

        padding:0 11px;

        border:1px solid #dedee3;
        border-radius:999px;

        background:#fff;
        color:#3a3a3c;

        font-size:8px;
        font-weight:650;

        cursor:pointer;
    }

    @media(max-width:700px) {
        .couple-profile-grid {
            grid-template-columns:1fr;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VISUAL PAGE BUILDER
    |--------------------------------------------------------------------------
    */

    .edit-layout.builder-mode {
        grid-template-columns:1fr;
        max-width:820px;
        margin:0 auto;
    }

    .edit-layout.builder-mode .edit-side {
        display:none;
    }

    .edit-layout.builder-mode .edit-content {
        width:100%;
        min-width:0;
    }

    #settings.visual-builder-card {
        order:-100;
        padding:14px;
        border-radius:16px;
    }

    .builder-theme-bar {
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:8px;
        margin:12px 0 14px;
    }

    .builder-theme-chip {
        min-height:40px;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:8px 10px;
        border:1px solid #e4e4e7;
        border-radius:10px;
        background:#fafafa;
        color:#3a3a3c;
        font-size:9px;
        font-weight:650;
        text-align:center;
    }

    .section-manager {
        gap:7px !important;
    }

    .section-manager-item,
    .builder-fixed-item {
        display:grid !important;
        grid-template-columns:34px minmax(0,1fr) auto auto !important;
        align-items:center !important;
        gap:8px !important;

        min-height:48px !important;
        padding:7px 8px !important;

        border:1px solid #e6e6e9 !important;
        border-radius:10px !important;

        background:#fff !important;
    }

    .builder-fixed-item {
        margin-bottom:7px;
    }

    .section-manager-item.is-open,
    .builder-fixed-item.is-open {
        border-color:#cfcfd4 !important;
        box-shadow:0 8px 24px rgba(0,0,0,.05);
    }

    .section-manager-item.dragging {
        opacity:.5;
        transform:scale(.995);
    }

    .section-drag,
    .builder-fixed-drag {
        width:32px !important;
        height:32px !important;

        display:grid;
        place-items:center;

        border:0 !important;
        border-radius:8px !important;

        background:#f5f5f7 !important;
        color:#8e8e93 !important;

        font-size:15px !important;

        cursor:grab;
        touch-action:none;
    }

    .builder-fixed-drag {
        cursor:default;
        opacity:.55;
    }

    .section-manager-name,
    .builder-fixed-name {
        color:#1d1d1f;
        font-size:10px !important;
        font-weight:700 !important;
    }

    .section-manager-status,
    .builder-fixed-status {
        margin-top:2px;
        color:#9a9a9f;
        font-size:7px !important;
    }

    .section-edit-button {
        min-height:30px;

        padding:0 10px;

        border:1px solid #dedee3;
        border-radius:999px;

        background:#f7f7f8;
        color:#1d1d1f;

        font-size:8px;
        font-weight:700;

        cursor:pointer;
    }

    .section-edit-button:hover {
        background:#efeff1;
    }

    .section-editor-panel {
        grid-column:1 / -1;

        display:none;

        padding:10px 2px 2px;
    }

    .section-manager-item.is-open
    .section-editor-panel,
    .builder-fixed-item.is-open
    .section-editor-panel {
        display:block;
    }

    .section-editor-panel > .edit-card,
    .section-editor-panel > section.edit-card {
        margin:0 !important;
        padding:14px !important;

        border:1px solid #ececef !important;
        border-radius:12px !important;

        box-shadow:none !important;
        background:#fafafa !important;
    }

    .section-editor-panel .edit-section-head {
        margin-bottom:12px;
    }

    .section-editor-panel .edit-section-title {
        font-size:13px;
    }

    .section-editor-panel .edit-section-copy {
        font-size:8px;
    }

    .section-mini-preview {
        position:relative;

        overflow:hidden;

        margin-bottom:12px;

        border:1px solid rgba(0,0,0,.06);
        border-radius:12px;

        background:#e9dfd2;
    }

    .section-mini-preview.modern-dark {
        background:#201711;
        color:#f2e8dd;
    }

    .section-mini-preview-inner {
        min-height:110px;

        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;

        padding:20px;

        text-align:center;
    }

    .section-mini-kicker {
        margin-bottom:8px;

        font-size:7px;
        font-weight:700;

        letter-spacing:.18em;
        text-transform:uppercase;

        opacity:.6;
    }

    .section-mini-title {
        font-family:Georgia,"Times New Roman",serif;

        font-size:26px;
        line-height:.94;
        letter-spacing:-.04em;
    }

    .section-mini-copy {
        max-width:420px;

        margin-top:8px;

        font-family:Georgia,"Times New Roman",serif;

        font-size:10px;
        line-height:1.6;

        opacity:.72;
    }

    .section-inline-note {
        padding:12px;

        border:1px dashed #d9d9dd;
        border-radius:10px;

        background:#fafafa;
        color:#77777d;

        font-size:9px;
        line-height:1.6;
    }

    .builder-static-row {
        display:grid;
        gap:7px;
    }

    .builder-original-holder {
        display:none !important;
    }

    .section-manager-wrap {
        margin-top:14px !important;
    }

    .section-manager-head {
        margin-bottom:8px !important;
    }

    @media(max-width:700px) {

        .edit-page {
            padding-left:10px;
            padding-right:10px;
        }

        .edit-head {
            padding-left:2px;
            padding-right:2px;
        }

        #settings.visual-builder-card {
            padding:10px;
        }

        .builder-theme-bar {
            grid-template-columns:1fr;
        }

        .section-manager-item,
        .builder-fixed-item {
            grid-template-columns:32px minmax(0,1fr) auto auto !important;
            gap:6px !important;
            padding:6px !important;
        }

        .section-edit-button {
            min-height:28px;
            padding:0 8px;
            font-size:7px;
        }

        .section-toggle {
            transform:scale(.9);
            transform-origin:right center;
        }

        .section-editor-panel {
            padding-top:8px;
        }

        .section-editor-panel > .edit-card,
        .section-editor-panel > section.edit-card {
            padding:11px !important;
        }

        .couple-profile-grid {
            grid-template-columns:1fr !important;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | VISUAL BUILDER V2
    |--------------------------------------------------------------------------
    */

    .edit-page {
        max-width:760px;
        margin:0 auto;
    }

    .edit-head {
        align-items:center;
        gap:14px;
        margin-bottom:14px;
    }

    .edit-title {
        font-size:clamp(26px,4vw,34px);
    }

    .edit-description {
        margin-top:4px;
        font-size:10px;
    }

    .visual-builder-card {
        padding:12px !important;
    }

    .builder-theme-bar {
        grid-template-columns:minmax(0,1fr) auto auto !important;
        align-items:center;
    }

    .builder-theme-chip {
        min-height:34px !important;
        padding:7px 9px !important;
        border-radius:9px !important;
        font-size:8px !important;
    }

    .builder-theme-chip:first-child {
        justify-content:flex-start;
    }

    .section-manager-head {
        position:sticky;
        top:8px;
        z-index:15;

        padding:8px 10px;
        margin:0 0 8px !important;

        border:1px solid #e7e7ea;
        border-radius:10px;

        background:rgba(255,255,255,.94);
        backdrop-filter:blur(12px);
    }

    .section-manager-title {
        font-size:10px !important;
    }

    .section-manager-copy {
        font-size:7px !important;
    }

    .section-manager-item,
    .builder-fixed-item {
        grid-template-columns:30px minmax(0,1fr) 38px 52px !important;
        min-height:46px !important;
        padding:6px 7px !important;
        border-radius:9px !important;
    }

    .section-drag,
    .builder-fixed-drag {
        width:28px !important;
        height:30px !important;
        border-radius:7px !important;
        font-size:13px !important;
    }

    .section-manager-name,
    .builder-fixed-name {
        font-size:9px !important;
    }

    .section-manager-status,
    .builder-fixed-status {
        font-size:6.5px !important;
    }

    .section-edit-button {
        min-height:28px !important;
        padding:0 8px !important;
        font-size:7px !important;
    }

    .section-toggle {
        transform:scale(.82);
        transform-origin:center;
    }

    .section-editor-panel {
        padding-top:7px !important;
    }

    .theme-section-preview {
        position:relative;
        overflow:hidden;

        min-height:148px;

        margin-bottom:10px;

        border:1px solid rgba(0,0,0,.06);
        border-radius:12px;

        background:#e9dfd2;
        color:#241b15;
    }

    .theme-section-preview.dark {
        background:#201711;
        color:#f2e8dd;
    }

    .theme-section-preview .preview-kicker {
        font-size:6px;
        font-weight:700;
        letter-spacing:.18em;
        text-transform:uppercase;
        opacity:.65;
    }

    .theme-section-preview .preview-serif {
        font-family:Georgia,"Times New Roman",serif;
        letter-spacing:-.04em;
    }

    .theme-section-preview .preview-photo {
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
    }

    .theme-couple-preview {
        display:grid;
        grid-template-columns:1fr 1fr;
        min-height:190px;
    }

    .theme-couple-copy {
        display:flex;
        flex-direction:column;
        justify-content:flex-end;

        padding:16px;

        background:#6a4530;
        color:#fff;
    }

    .theme-couple-copy.groom {
        background:#5a3518;
    }

    .theme-couple-name {
        margin-top:auto;

        font-family:Georgia,"Times New Roman",serif;

        font-size:24px;
        line-height:.95;
        letter-spacing:-.04em;
    }

    .theme-couple-meta {
        margin-top:10px;

        font-size:7px;
        line-height:1.55;

        opacity:.78;
    }

    .theme-couple-photo {
        min-height:190px;
        background:#32251c;
    }

    .theme-couple-photo img {
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
    }

    .theme-gallery-preview {
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:4px;

        padding:10px;
        background:#201711;
    }


    .builder-gallery-grid {
        align-items:stretch;
    }

    .builder-gallery-card {
        position:relative;
        overflow:hidden;

        border-radius:6px;
        background:#30241c;

        cursor:grab;
    }

    .builder-gallery-card:active {
        cursor:grabbing;
    }

    .builder-gallery-card.dragging {
        opacity:.45;
    }

    .builder-gallery-card img {
        width:100%;
        aspect-ratio:4/5;
        display:block;
        object-fit:cover;
    }

    .builder-gallery-delete {
        position:absolute;
        top:5px;
        right:5px;

        width:24px;
        height:24px;

        display:grid;
        place-items:center;

        padding:0;

        border:1px solid rgba(255,255,255,.38);
        border-radius:50%;

        background:rgba(20,14,10,.72);
        color:#fff;

        font-size:15px;
        line-height:1;

        cursor:pointer;
        backdrop-filter:blur(6px);
    }

    .builder-gallery-add {
        min-height:0;
        aspect-ratio:4/5;

        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
        gap:6px;

        border:1px dashed rgba(255,255,255,.34);
        border-radius:6px;

        background:rgba(255,255,255,.055);
        color:#d8c7b9;

        font-size:8px;
        font-weight:700;

        cursor:pointer;

        transition:
            background .15s ease,
            border-color .15s ease;
    }

    .builder-gallery-add:hover {
        background:rgba(255,255,255,.09);
        border-color:rgba(255,255,255,.55);
    }

    .builder-gallery-plus {
        width:30px;
        height:30px;

        display:grid;
        place-items:center;

        border:1px solid rgba(255,255,255,.26);
        border-radius:50%;

        font-size:18px;
        font-weight:400;
    }

    .builder-gallery-help {
        margin-top:7px;

        color:#8e8e93;

        font-size:7px;
        line-height:1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | INLINE EDITING — SINGLE REAL INPUT ONLY
    |--------------------------------------------------------------------------
    */

    .inline-real-input,
    .inline-real-textarea {
        width:100% !important;

        border:0 !important;
        border-bottom:1px solid rgba(255,255,255,.26) !important;

        border-radius:0 !important;

        background:transparent !important;
        color:inherit !important;

        outline:none !important;

        box-shadow:none !important;
    }

    .inline-real-input::placeholder,
    .inline-real-textarea::placeholder {
        color:currentColor !important;
        opacity:.42 !important;
    }

    .inline-real-input:focus,
    .inline-real-textarea:focus {
        border-bottom-color:currentColor !important;
    }

    .inline-name-input {
        margin-top:auto !important;

        padding:0 0 4px !important;

        font-family:Georgia,"Times New Roman",serif !important;

        font-size:24px !important;
        line-height:1 !important;
        letter-spacing:-.04em !important;
    }

    .inline-meta-input {
        margin-top:7px !important;
        padding:0 0 3px !important;

        font-size:7px !important;
        line-height:1.5 !important;
    }

    .inline-photo-shell {
        position:relative;

        width:100%;
        min-height:0;
        aspect-ratio:1/1;

        overflow:hidden;

        background:#30241c;
    }

    .inline-photo-shell [data-inline-photo-host] {
        width:100%;
        height:100%;
    }

    .inline-photo-shell .photo-adjust-wrap {
        width:100%;
        height:100%;

        margin:0 !important;
    }

    .inline-photo-shell .photo-adjust-stage {
        width:100% !important;
        max-width:none !important;
        height:100% !important;
        aspect-ratio:1/1 !important;

        margin:0 !important;

        border:0 !important;
        border-radius:0 !important;

        cursor:grab;
        touch-action:none;
    }

    .inline-photo-shell .photo-adjust-stage.is-dragging {
        cursor:grabbing;
    }

    .inline-photo-shell .photo-adjust-hint,
    .inline-photo-shell .photo-adjust-actions {
        display:none !important;
    }

    .inline-photo-plus {
        position:absolute;
        left:50%;
        bottom:9px;
        z-index:6;

        width:34px;
        height:34px;

        display:flex;
        align-items:center;
        justify-content:center;

        padding:0;

        border:1px solid rgba(255,255,255,.38);
        border-radius:50%;

        background:rgba(25,17,12,.78);
        color:#fff;

        font-size:20px;
        font-weight:300;
        line-height:1;

        text-align:center;
        text-indent:0;

        cursor:pointer;

        transform:translateX(-50%);

        backdrop-filter:blur(8px);
        box-shadow:0 5px 16px rgba(0,0,0,.16);
    }

    .inline-photo-reset {
        position:absolute;
        left:9px;
        bottom:9px;
        z-index:6;

        min-height:28px;
        padding:0 9px;

        border:1px solid rgba(255,255,255,.28);
        border-radius:999px;

        background:rgba(25,17,12,.60);
        color:#fff;

        font-size:6px;
        font-weight:700;

        cursor:pointer;

        backdrop-filter:blur(8px);
    }

    .inline-photo-label {
        position:absolute;
        left:50%;
        top:8px;
        z-index:6;

        transform:translateX(-50%);

        padding:4px 7px;

        border-radius:999px;

        background:rgba(25,17,12,.55);
        color:#fff;

        font-size:5.5px;
        white-space:nowrap;

        backdrop-filter:blur(7px);
    }

    .inline-quote-host,
    .inline-story-host {
        width:100%;
    }

    .inline-quote-host .inline-real-textarea {
        min-height:92px !important;

        padding:8px 0 !important;

        border-bottom:0 !important;

        font-family:Georgia,"Times New Roman",serif !important;

        font-size:14px !important;
        line-height:1.7 !important;
        font-style:italic !important;

        text-align:center !important;

        resize:vertical !important;
    }

    .inline-story-host .inline-real-textarea {
        min-height:120px !important;

        padding:8px 0 !important;

        border-bottom:0 !important;

        font-family:Georgia,"Times New Roman",serif !important;

        font-size:11px !important;
        line-height:1.8 !important;

        text-align:center !important;

        resize:vertical !important;
    }

    .inline-edit-note {
        margin-top:6px;

        color:#8e8e93;

        font-size:6.5px;
        line-height:1.45;

        text-align:center;
    }

    .inline-source-hidden {
        display:none !important;
    }

    @media(max-width:700px) {

        body {
            overflow-x:hidden;
        }

        .edit-page {
            width:100%;
            max-width:100%;

            padding:
                8px
                8px
                96px !important;
        }

        .edit-head {
            position:sticky;
            top:0;
            z-index:40;

            display:flex;
            align-items:center;
            justify-content:space-between;

            gap:8px;

            margin:0 -8px 10px;
            padding:8px 10px;

            border-bottom:1px solid #ececef;

            background:rgba(255,255,255,.96);

            backdrop-filter:blur(14px);
        }

        .edit-head-copy {
            min-width:0;
        }

        .edit-eyebrow,
        .edit-description {
            display:none;
        }

        .edit-title {
            margin:0 !important;

            font-size:18px !important;
            line-height:1.05 !important;

            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .edit-head-actions {
            flex:0 0 auto;

            display:flex;
            align-items:center;
            gap:5px !important;
        }

        .edit-head-actions .edit-button,
        .edit-head-actions button,
        .edit-head-actions a {
            min-height:32px !important;

            padding:0 10px !important;

            border-radius:999px !important;

            font-size:7px !important;
        }

        .edit-layout.builder-mode {
            width:100%;
            max-width:100%;
        }

        #settings.visual-builder-card {
            width:100%;

            padding:8px !important;

            border-radius:12px !important;
        }

        #settings > .edit-section-head {
            display:none;
        }

        .builder-theme-bar {
            display:flex !important;
            align-items:center;

            gap:5px;

            overflow-x:auto;

            margin:0 0 8px !important;
            padding:0 0 3px;

            scrollbar-width:none;
        }

        .builder-theme-bar::-webkit-scrollbar {
            display:none;
        }

        .builder-theme-chip {
            flex:0 0 auto;

            min-height:28px !important;

            padding:5px 8px !important;

            border-radius:999px !important;

            font-size:6.5px !important;

            white-space:nowrap;
        }

        .builder-theme-chip:first-child {
            grid-column:auto !important;
        }

        .section-manager-head {
            position:static !important;

            margin:0 0 6px !important;
            padding:7px 8px !important;

            border-radius:8px !important;

            background:#fafafa !important;

            backdrop-filter:none !important;
        }

        .section-manager-copy {
            margin-top:2px !important;
        }

        .builder-static-row,
        .section-manager,
        .builder-bottom-fixed {
            gap:5px !important;
        }

        .section-manager-item,
        .builder-fixed-item {
            grid-template-columns:
                26px
                minmax(0,1fr)
                34px
                45px
                !important;

            gap:4px !important;

            min-height:42px !important;

            padding:5px 6px !important;

            border-radius:8px !important;

            box-shadow:none !important;
        }

        .section-manager-item.is-open,
        .builder-fixed-item.is-open {
            border-color:#d3d3d8 !important;

            box-shadow:
                0 6px 18px
                rgba(0,0,0,.045)
                !important;
        }

        .section-drag,
        .builder-fixed-drag {
            width:25px !important;
            height:28px !important;

            border-radius:7px !important;

            font-size:12px !important;
        }

        .section-manager-name,
        .builder-fixed-name {
            font-size:8.5px !important;
            line-height:1.15 !important;
        }

        .section-manager-status,
        .builder-fixed-status {
            margin-top:1px !important;

            font-size:5.8px !important;
        }

        .section-toggle {
            width:38px !important;
            height:22px !important;

            transform:scale(.78);
            transform-origin:center;
        }

        .section-edit-button {
            min-height:26px !important;

            padding:0 7px !important;

            border-radius:999px !important;

            font-size:6.5px !important;
        }

        .section-editor-panel {
            padding:5px 0 1px !important;
        }

        .theme-section-preview {
            min-height:0 !important;

            margin-bottom:7px !important;

            border-radius:9px !important;
        }

        .theme-couple-preview {
            min-height:0 !important;
            aspect-ratio:2/1;
            align-items:stretch;
        }

        .theme-couple-copy {
            min-height:0 !important;
            height:100%;

            padding:12px !important;
        }

        .inline-name-input {
            font-size:18px !important;
        }

        .inline-meta-input {
            margin-top:5px !important;

            font-size:6.5px !important;
        }

        .inline-photo-shell {
            min-height:0 !important;
            height:100% !important;
            aspect-ratio:1/1 !important;
        }

        .inline-photo-plus {
            left:50%;
            right:auto;
            bottom:7px;

            width:30px;
            height:30px;

            font-size:17px;

            transform:translateX(-50%);
        }

        .inline-photo-reset {
            left:7px;
            bottom:7px;

            min-height:25px;

            padding:0 8px;

            font-size:5.5px;
        }

        .inline-photo-label {
            top:6px;

            max-width:90%;

            overflow:hidden;
            text-overflow:ellipsis;

            font-size:5px;
        }

        .theme-story-preview,
        .theme-quote-preview,
        .theme-simple-preview {
            min-height:120px !important;

            padding:16px !important;
        }

        .theme-preview-title {
            font-size:22px !important;
        }

        .inline-quote-host .inline-real-textarea {
            min-height:78px !important;

            font-size:12px !important;
        }

        .inline-story-host .inline-real-textarea {
            min-height:96px !important;

            font-size:9.5px !important;
        }

        .theme-gallery-preview {
            grid-template-columns:
                repeat(
                    3,
                    minmax(0,1fr)
                )
                !important;

            gap:3px !important;

            padding:6px !important;
        }

        .builder-gallery-add,
        .builder-gallery-card {
            border-radius:5px !important;
        }

        .builder-gallery-plus {
            width:25px;
            height:25px;

            font-size:15px;
        }

        .builder-gallery-delete {
            top:4px;
            right:4px;

            width:21px;
            height:21px;

            font-size:13px;
        }

        .theme-event-preview {
            min-height:135px !important;
        }

        .theme-event-date,
        .theme-event-info {
            padding:11px !important;
        }

        .theme-event-day {
            font-size:40px !important;
        }

        .theme-event-name {
            font-size:17px !important;
        }

        .builder-save-hint {
            margin-top:7px !important;

            padding:7px 8px !important;

            font-size:6px !important;
        }

        .edit-save-bar,
        .save-bar {
            left:8px !important;
            right:8px !important;
            bottom:
                max(
                    8px,
                    env(
                        safe-area-inset-bottom
                    )
                )
                !important;

            width:auto !important;

            border-radius:12px !important;
        }

    }

    .builder-gallery-pending {
        position:relative;
    }

    .builder-gallery-pending::after {
        content:"Baru";

        position:absolute;
        left:5px;
        bottom:5px;

        padding:3px 5px;

        border-radius:999px;

        background:rgba(20,14,10,.72);
        color:#fff;

        font-size:6px;
        font-weight:700;
        letter-spacing:.04em;
        text-transform:uppercase;
    }

    .theme-gallery-preview img {
        width:100%;
        aspect-ratio:4/5;
        display:block;
        object-fit:cover;
        border-radius:5px;
    }

    .theme-gallery-empty {
        grid-column:1/-1;

        min-height:120px;

        display:grid;
        place-items:center;

        color:#b8a596;

        font-size:8px;
        text-align:center;
    }

    .theme-event-preview {
        display:grid;
        grid-template-columns:.78fr 1.22fr;
        min-height:170px;
    }

    .theme-event-date {
        display:flex;
        flex-direction:column;
        justify-content:space-between;

        padding:15px;

        background:#201711;
        color:#fff;
    }

    .theme-event-day {
        margin-top:auto;

        font-family:Georgia,"Times New Roman",serif;

        font-size:56px;
        line-height:.75;
    }

    .theme-event-info {
        display:flex;
        flex-direction:column;
        justify-content:center;

        padding:16px;

        background:#f6f0e7;
    }

    .theme-event-name {
        margin-top:8px;

        font-family:Georgia,"Times New Roman",serif;

        font-size:24px;
        line-height:.95;
    }

    .theme-story-preview,
    .theme-quote-preview,
    .theme-simple-preview {
        min-height:150px;

        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;

        padding:22px;

        text-align:center;
    }

    .theme-story-preview {
        background:#d8c5ad;
    }

    .theme-quote-preview {
        background:#201711;
        color:#efe5d9;
    }

    .theme-simple-preview {
        background:#e9dfd2;
    }

    .theme-preview-title {
        margin-top:8px;

        font-family:Georgia,"Times New Roman",serif;

        font-size:27px;
        line-height:.95;
        letter-spacing:-.04em;
    }

    .theme-preview-copy {
        max-width:420px;

        margin-top:8px;

        font-family:Georgia,"Times New Roman",serif;

        font-size:8px;
        line-height:1.65;

        opacity:.7;
    }

    .builder-bottom-fixed {
        margin-top:7px;
    }

    .builder-fixed-item.closing {
        margin-bottom:0;
    }

    .builder-save-hint {
        margin-top:9px;

        padding:8px 10px;

        border:1px solid #e6e6e9;
        border-radius:9px;

        background:#fafafa;
        color:#77777d;

        font-size:7px;
        line-height:1.5;
        text-align:center;
    }

    @media(max-width:700px) {

        .edit-page {
            max-width:100%;
            padding-left:8px;
            padding-right:8px;
        }

        .edit-head {
            margin-bottom:10px;
        }

        .edit-head-actions {
            gap:6px;
        }

        .builder-theme-bar {
            grid-template-columns:1fr 1fr !important;
        }

        .builder-theme-chip:first-child {
            grid-column:1/-1;
        }

        .section-manager-head {
            top:6px;
        }

        .section-manager-item,
        .builder-fixed-item {
            grid-template-columns:28px minmax(0,1fr) 34px 48px !important;
            gap:5px !important;
        }

        .theme-couple-preview {
            min-height:165px;
        }

        .theme-couple-photo {
            min-height:165px;
        }

        .theme-couple-name {
            font-size:19px;
        }

        .theme-event-preview {
            min-height:150px;
        }

        .theme-event-day {
            font-size:45px;
        }

        .theme-event-name {
            font-size:19px;
        }

    }



    /*
    |--------------------------------------------------------------------------
    | DEDICATED THEME SECTION
    |--------------------------------------------------------------------------
    */

    .theme-section-panel {
        padding:12px;

        border:1px solid #ececef;
        border-radius:10px;

        background:#fafafa;
    }

    .theme-section-label {
        display:block;

        margin-bottom:7px;

        color:#515154;

        font-size:9px;
        font-weight:700;
    }

    .theme-section-select {
        width:100%;

        min-height:38px;

        padding:0 10px;

        border:1px solid #dedee3;
        border-radius:9px;

        background:#fff;
        color:#1d1d1f;

        font-size:10px;
        font-weight:650;

        outline:none;
        box-shadow:none;
    }

    .theme-section-select:focus {
        border-color:#a9a9ae;
    }

    .theme-current-value {
        min-height:26px;

        display:inline-flex;
        align-items:center;
        justify-content:center;

        padding:4px 8px;

        border:1px solid #e4e4e7;
        border-radius:999px;

        background:#fafafa;
        color:#3a3a3c;

        font-size:7px;
        font-weight:700;
    }

    @media(max-width:700px) {

        .theme-section-panel {
            padding:9px;
        }

        .theme-section-select {
            min-height:34px;
            font-size:8px;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | SECTION MANAGER
    |--------------------------------------------------------------------------
    */

    .section-manager-wrap {
        margin-top: 18px;
    }

    .section-manager-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 10px;
    }

    .section-manager-title {
        color: #1d1d1f;
        font-size: 12px;
        font-weight: 700;
    }

    .section-manager-copy {
        margin-top: 3px;
        color: #9a9a9f;
        font-size: 9px;
        line-height: 1.45;
    }

    .section-manager {
        display: grid;
        gap: 7px;
    }

    .section-manager-item {
        display: grid;
        grid-template-columns: 34px minmax(0,1fr) auto;
        align-items: center;
        gap: 10px;

        min-height: 52px;
        padding: 7px 10px;

        border: 1px solid #e5e5e7;
        border-radius: 11px;

        background: #fff;

        transition:
            box-shadow .15s ease,
            transform .15s ease,
            opacity .15s ease;
    }

    .section-manager-item.dragging {
        opacity: .55;
        transform: scale(.995);
        box-shadow: 0 8px 24px rgba(0,0,0,.08);
    }

    .section-drag {
        width: 32px;
        height: 34px;

        display: grid;
        place-items: center;

        border: 0;
        border-radius: 8px;

        background: #f5f5f7;
        color: #8e8e93;

        font-size: 16px;
        line-height: 1;

        cursor: grab;

        touch-action: none;
        user-select: none;
    }

    .section-drag:active {
        cursor: grabbing;
    }

    .section-manager-name {
        color: #1d1d1f;
        font-size: 11px;
        font-weight: 650;
    }

    .section-manager-status {
        margin-top: 3px;
        color: #9a9a9f;
        font-size: 8px;
    }

    .section-toggle {
        position: relative;

        width: 42px;
        height: 24px;

        flex: 0 0 42px;
    }

    .section-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .section-toggle-track {
        position: absolute;
        inset: 0;

        border-radius: 999px;

        background: #d1d1d6;

        cursor: pointer;

        transition:
            background .18s ease;
    }

    .section-toggle-track::after {
        content: "";

        position: absolute;
        top: 3px;
        left: 3px;

        width: 18px;
        height: 18px;

        border-radius: 50%;

        background: #fff;

        box-shadow:
            0 1px 3px rgba(0,0,0,.18);

        transition:
            transform .18s ease;
    }

    .section-toggle input:checked
    + .section-toggle-track {
        background: #34c759;
    }

    .section-toggle input:checked
    + .section-toggle-track::after {
        transform: translateX(18px);
    }

    @media(max-width:700px) {
        .section-manager-item {
            grid-template-columns: 38px minmax(0,1fr) auto;
            min-height: 56px;
        }

        .section-drag {
            width: 36px;
            height: 38px;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE BAR
    |--------------------------------------------------------------------------
    */

    .save-bar {
        position: sticky;
        bottom: 12px;
        z-index: 30;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;

        width: 100%;

        padding: 11px 13px;

        border: 1px solid #dedee2;
        border-radius: 13px;

        background: rgba(255,255,255,.96);

        box-shadow:
            0 8px 24px rgba(0,0,0,.06);
    }

    .save-title {
        color: #1d1d1f;

        font-size: 11px;
        font-weight: 650;
    }

    .save-copy {
        margin-top: 2px;

        color: #9a9a9f;

        font-size: 9px;
    }


    /*
    |--------------------------------------------------------------------------
    | PLANS
    |--------------------------------------------------------------------------
    */

    .plan-grid {
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 10px;
    }

    .plan-card {
        min-width: 0;

        padding: 16px;

        border: 1px solid #e5e5e7;
        border-radius: 11px;

        background: #fff;
    }

    .plan-name {
        color: #1d1d1f;

        font-size: 12px;
        font-weight: 700;
    }

    .plan-price {
        margin: 6px 0 13px;

        color: #1d1d1f;

        font-size: 21px;
        font-weight: 700;

        letter-spacing: -.03em;
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */

    .edit-errors {
        margin-bottom: 18px;
        padding: 13px 14px;

        border: 1px solid #efc3bf;
        border-radius: 11px;

        background: #fff4f3;
        color: #8a1c13;

        font-size: 11px;
    }

    .edit-errors ul {
        margin: 7px 0 0;
        padding-left: 18px;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media(max-width:950px) {

        .edit-layout {
            grid-template-columns: 1fr;
        }

        .edit-side {
            position: static;

            display: flex;
            gap: 5px;

            overflow-x: auto;

            padding-bottom: 8px;
        }

        .edit-side-label {
            display: none;
        }

        .edit-side-link {
            flex: 0 0 auto;

            border: 1px solid #e5e5e7;

            white-space: nowrap;
        }

        .gallery-grid {
            grid-template-columns: repeat(3,minmax(0,1fr));
        }

    }


    @media(max-width:700px) {

        .edit-page {
            padding: 22px 14px 38px;
        }

        .edit-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-title {
            font-size: 28px;
        }

        .edit-description {
            font-size: 12px;
        }

        .edit-grid,
        .edit-grid.three {
            grid-template-columns: 1fr;
        }

        .cover-layout {
            grid-template-columns: 1fr;
        }

        .cover-preview {
            width: 170px;
        }

        .toggle-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }

        .gallery-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }

        .plan-grid {
            grid-template-columns: 1fr;
        }

        .save-bar {
            bottom: 8px;
        }

    }

</style>


<div class="edit-page">


    @if($errors->any())

        <div class="edit-errors">

            <strong>
                Ada data yang perlu diperbaiki.
            </strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- HEADER --}}

    <header class="edit-head">

        <div>

            <div class="edit-kicker">
                UNDANGANTA.ID
            </div>

            <h1 class="edit-title">
                {{ $invitation->exists
                    ? 'Edit Undangan'
                    : 'Undangan Baru'
                }}
            </h1>

            <p class="edit-description">

                @if($invitation->exists)

                    {{ $invitation->groom_name }}
                    &
                    {{ $invitation->bride_name }}

                @else

                    Buat undangan baru.

                @endif

            </p>

        </div>


        @if($invitation->exists)

            <div class="edit-head-actions">

                @if($invitation->is_published)

                    <a
                        href="{{ route(
                            'public.invitation',
                            $invitation->slug
                        ) }}"
                        target="_blank"
                        class="edit-light"
                    >
                        Preview
                    </a>

                @endif


                <form
                    method="POST"
                    action="{{ route(
                        'invitations.publish',
                        $invitation
                    ) }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="edit-primary"
                    >
                        {{ $invitation->is_published
                            ? 'Jadikan Draft'
                            : 'Publish'
                        }}
                    </button>

                </form>

            </div>

        @endif

    </header>


    <form
        id="invitationForm"
        method="POST"
        enctype="multipart/form-data"
        action="{{ $invitation->exists
            ? route('invitations.update', $invitation)
            : route('invitations.store')
        }}"
    >

        @csrf

        @if($invitation->exists)
            @method('PUT')
        @endif


        <input
            type="hidden"
            id="galleryOrder"
            name="gallery_order"
            value=""
        >


        <div class="edit-layout">


            {{-- SIDEBAR --}}

            <aside class="edit-side">

                <div class="edit-side-label">
                    Editor
                </div>


                <a href="#identity" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Identitas
                </a>


                <a href="#event" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Acara
                </a>


                <a href="#cover" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Cover
                </a>


                <a href="#story" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Cerita
                </a>


                <a href="#gallery" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Galeri
                </a>


                <a href="#gift" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Amplop
                </a>


                <a href="#settings" class="edit-side-link">
                    <span class="edit-side-dot"></span>
                    Susunan & Tampilan
                </a>

            </aside>


            {{-- CONTENT --}}

            <main class="edit-content">


                {{-- IDENTITAS --}}

                <section
                    id="identity"
                    class="edit-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Identitas Undangan
                            </h2>

                            <p class="edit-section-copy">
                                Judul, URL, foto, dan profil kedua mempelai.
                            </p>

                        </div>

                    </div>


                    <div class="edit-grid">

                        <div class="edit-field">

                            <label class="edit-label">
                                Judul Undangan
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="title"
                                required
                                value="{{ old(
                                    'title',
                                    $invitation->title
                                ) }}"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Slug URL
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="slug"
                                value="{{ old(
                                    'slug',
                                    $invitation->slug
                                ) }}"
                                placeholder="contoh: dimas-siti"
                            >

                        </div>

                    </div>


                    <div class="couple-profile-grid">

                        <div class="couple-profile-card">

                            <div class="couple-profile-title">
                                The Groom
                            </div>

                            <div class="couple-profile-copy">
                                Foto dan profil khusus mempelai pria.
                            </div>


                            <div class="photo-adjust-wrap">

                                <div
                                    class="photo-adjust-stage"
                                    data-photo-adjust="groom"
                                    data-x="{{ old(
                                        'groom_photo_x',
                                        $invitation->groom_photo_x ?? 50
                                    ) }}"
                                    data-y="{{ old(
                                        'groom_photo_y',
                                        $invitation->groom_photo_y ?? 50
                                    ) }}"
                                >

                                    <img
                                        data-photo-image="groom"
                                        src="{{ $invitation->groom_photo_path
                                            ? url('/storage/' . $invitation->groom_photo_path)
                                            : ''
                                        }}"
                                        alt="Foto Groom"
                                        style="
                                            object-position:
                                            {{ old(
                                                'groom_photo_x',
                                                $invitation->groom_photo_x ?? 50
                                            ) }}%
                                            {{ old(
                                                'groom_photo_y',
                                                $invitation->groom_photo_y ?? 50
                                            ) }}%;
                                            {{ $invitation->groom_photo_path
                                                ? ''
                                                : 'display:none;'
                                            }}
                                        "
                                    >

                                    <div
                                        class="photo-adjust-empty"
                                        data-photo-empty="groom"
                                        style="{{ $invitation->groom_photo_path
                                            ? 'display:none;'
                                            : ''
                                        }}"
                                    >
                                        Upload foto Groom untuk melihat preview.
                                    </div>

                                </div>

                                <div class="photo-adjust-hint">
                                    Setelah upload, tarik foto ke kiri/kanan atau atas/bawah untuk mengatur framing.
                                </div>

                                <div class="photo-adjust-actions">

                                    <button
                                        type="button"
                                        class="photo-adjust-reset"
                                        data-photo-reset="groom"
                                    >
                                        Posisi Tengah
                                    </button>

                                </div>

                                <input
                                    type="hidden"
                                    name="groom_photo_x"
                                    value="{{ old(
                                        'groom_photo_x',
                                        $invitation->groom_photo_x ?? 50
                                    ) }}"
                                    data-photo-x="groom"
                                >

                                <input
                                    type="hidden"
                                    name="groom_photo_y"
                                    value="{{ old(
                                        'groom_photo_y',
                                        $invitation->groom_photo_y ?? 50
                                    ) }}"
                                    data-photo-y="groom"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Foto Groom
                                </label>

                                <input
                                    class="edit-input"
                                    type="file"
                                    name="groom_photo"
                                    accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
                                    data-photo-input="groom"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Nama Groom
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="groom_name"
                                    required
                                    value="{{ old(
                                        'groom_name',
                                        $invitation->groom_name
                                    ) }}"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Keterangan Orang Tua
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="groom_parent_text"
                                    value="{{ old(
                                        'groom_parent_text',
                                        $invitation->groom_parent_text
                                    ) }}"
                                    placeholder="Putra dari Bapak ... dan Ibu ..."
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Asal / Domisili
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="groom_origin"
                                    value="{{ old(
                                        'groom_origin',
                                        $invitation->groom_origin
                                    ) }}"
                                    placeholder="Makassar, Sulawesi Selatan"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Instagram
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="groom_instagram"
                                    value="{{ old(
                                        'groom_instagram',
                                        $invitation->groom_instagram
                                    ) }}"
                                    placeholder="@username"
                                >

                            </div>

                        </div>


                        <div class="couple-profile-card">

                            <div class="couple-profile-title">
                                The Bride
                            </div>

                            <div class="couple-profile-copy">
                                Foto dan profil khusus mempelai wanita.
                            </div>


                            <div class="photo-adjust-wrap">

                                <div
                                    class="photo-adjust-stage"
                                    data-photo-adjust="bride"
                                    data-x="{{ old(
                                        'bride_photo_x',
                                        $invitation->bride_photo_x ?? 50
                                    ) }}"
                                    data-y="{{ old(
                                        'bride_photo_y',
                                        $invitation->bride_photo_y ?? 50
                                    ) }}"
                                >

                                    <img
                                        data-photo-image="bride"
                                        src="{{ $invitation->bride_photo_path
                                            ? url('/storage/' . $invitation->bride_photo_path)
                                            : ''
                                        }}"
                                        alt="Foto Bride"
                                        style="
                                            object-position:
                                            {{ old(
                                                'bride_photo_x',
                                                $invitation->bride_photo_x ?? 50
                                            ) }}%
                                            {{ old(
                                                'bride_photo_y',
                                                $invitation->bride_photo_y ?? 50
                                            ) }}%;
                                            {{ $invitation->bride_photo_path
                                                ? ''
                                                : 'display:none;'
                                            }}
                                        "
                                    >

                                    <div
                                        class="photo-adjust-empty"
                                        data-photo-empty="bride"
                                        style="{{ $invitation->bride_photo_path
                                            ? 'display:none;'
                                            : ''
                                        }}"
                                    >
                                        Upload foto Bride untuk melihat preview.
                                    </div>

                                </div>

                                <div class="photo-adjust-hint">
                                    Setelah upload, tarik foto ke kiri/kanan atau atas/bawah untuk mengatur framing.
                                </div>

                                <div class="photo-adjust-actions">

                                    <button
                                        type="button"
                                        class="photo-adjust-reset"
                                        data-photo-reset="bride"
                                    >
                                        Posisi Tengah
                                    </button>

                                </div>

                                <input
                                    type="hidden"
                                    name="bride_photo_x"
                                    value="{{ old(
                                        'bride_photo_x',
                                        $invitation->bride_photo_x ?? 50
                                    ) }}"
                                    data-photo-x="bride"
                                >

                                <input
                                    type="hidden"
                                    name="bride_photo_y"
                                    value="{{ old(
                                        'bride_photo_y',
                                        $invitation->bride_photo_y ?? 50
                                    ) }}"
                                    data-photo-y="bride"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Foto Bride
                                </label>

                                <input
                                    class="edit-input"
                                    type="file"
                                    name="bride_photo"
                                    accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
                                    data-photo-input="bride"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Nama Bride
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="bride_name"
                                    required
                                    value="{{ old(
                                        'bride_name',
                                        $invitation->bride_name
                                    ) }}"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Keterangan Orang Tua
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="bride_parent_text"
                                    value="{{ old(
                                        'bride_parent_text',
                                        $invitation->bride_parent_text
                                    ) }}"
                                    placeholder="Putri dari Bapak ... dan Ibu ..."
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Asal / Domisili
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="bride_origin"
                                    value="{{ old(
                                        'bride_origin',
                                        $invitation->bride_origin
                                    ) }}"
                                    placeholder="Makassar, Sulawesi Selatan"
                                >

                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Instagram
                                </label>

                                <input
                                    class="edit-input"
                                    type="text"
                                    name="bride_instagram"
                                    value="{{ old(
                                        'bride_instagram',
                                        $invitation->bride_instagram
                                    ) }}"
                                    placeholder="@username"
                                >

                            </div>

                        </div>

                    </div>

                </section>


                {{-- EVENT --}}

                <section
                    id="event"
                    class="edit-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Acara
                            </h2>

                            <p class="edit-section-copy">
                                Atur waktu dan lokasi acara.
                            </p>

                        </div>

                    </div>


                    <div class="edit-grid">

                        <div class="edit-field">

                            <label class="edit-label">
                                Tanggal & Waktu
                            </label>

                            <input
                                class="edit-input"
                                type="datetime-local"
                                name="event_date"
                                required
                                value="{{ old(
                                    'event_date',
                                    $invitation->event_date?->format('Y-m-d\TH:i')
                                ) }}"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Nama Venue
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="venue_name"
                                required
                                value="{{ old(
                                    'venue_name',
                                    $invitation->venue_name
                                ) }}"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Alamat Venue
                            </label>

                            <textarea
                                class="edit-textarea"
                                name="venue_address"
                                rows="4"
                            >{{ old(
                                'venue_address',
                                $invitation->venue_address
                            ) }}</textarea>

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Google Maps URL
                            </label>

                            <input
                                class="edit-input"
                                type="url"
                                name="maps_url"
                                value="{{ old(
                                    'maps_url',
                                    $invitation->maps_url
                                ) }}"
                                placeholder="https://maps.google.com/..."
                            >

                        </div>

                    </div>

                </section>


                {{-- COVER --}}

                <section
                    id="cover"
                    class="edit-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Cover
                            </h2>

                            <p class="edit-section-copy">
                                Foto utama yang tampil pada undangan.
                            </p>

                        </div>

                    </div>


                    <div class="cover-layout">

                        <div class="cover-preview">

                            @if($invitation->cover_path)

                                <img
                                    src="{{ url(
                                        '/storage/'
                                        .
                                        $invitation->cover_path
                                    ) }}"
                                    alt="Cover"
                                    id="coverPreview"
                                >

                            @else

                                <div
                                    class="cover-empty"
                                    id="coverPlaceholder"
                                >
                                    Belum ada cover.
                                </div>

                                <img
                                    src=""
                                    alt=""
                                    id="coverPreview"
                                    style="display:none"
                                >

                            @endif

                        </div>


                        <div>

                            <div class="upload-box">

                                <div class="upload-title">
                                    Pilih atau ganti cover
                                </div>

                                <div class="upload-copy">
                                    JPG, JPEG, PNG, WEBP, HEIC atau HEIF.
                                    Maksimal 12 MB.
                                </div>

                                <input
                                    id="coverInput"
                                    type="file"
                                    name="cover"
                                    accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/jpeg,image/png,image/webp,image/heic,image/heif"
                                >

                            </div>


                            @if(
                                $invitation->exists
                                &&
                                $invitation->cover_path
                            )

                                <div style="margin-top:10px;">

                                    <button
                                        type="button"
                                        class="edit-danger"
                                        onclick="
                                            if(confirm('Hapus cover ini?')){
                                                document
                                                    .getElementById('deleteCoverForm')
                                                    .submit();
                                            }
                                        "
                                    >
                                        Hapus Cover
                                    </button>

                                </div>

                            @endif

                        </div>

                    </div>

                </section>


                {{-- STORY --}}

                <section
                    id="story"
                    class="edit-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Cerita
                            </h2>

                            <p class="edit-section-copy">
                                Quote dan love story pasangan.
                            </p>

                        </div>

                    </div>


                    <div class="edit-field" style="margin-bottom:14px;">

                        <label class="edit-label">
                            Quote
                        </label>

                        <textarea
                            class="edit-textarea"
                            name="quote"
                            rows="3"
                        >{{ old(
                            'quote',
                            $invitation->quote
                        ) }}</textarea>

                    </div>


                    <div class="edit-field">

                        <label class="edit-label">
                            Love Story
                        </label>

                        <textarea
                            class="edit-textarea"
                            name="story"
                            rows="7"
                        >{{ old(
                            'story',
                            $invitation->story
                        ) }}</textarea>

                    </div>

                </section>


                {{-- GIFT --}}

                <section
                    id="gift"
                    class="edit-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Amplop Digital
                            </h2>

                            <p class="edit-section-copy">
                                Tambahkan rekening atau e-wallet.
                            </p>

                        </div>

                    </div>


                    <div class="edit-grid three">

                        <div class="edit-field">

                            <label class="edit-label">
                                Bank / E-Wallet
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="gift_bank"
                                value="{{ old(
                                    'gift_bank',
                                    $gift['bank'] ?? ''
                                ) }}"
                                placeholder="BCA / BRI / DANA"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Nomor
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="gift_number"
                                value="{{ old(
                                    'gift_number',
                                    $gift['number'] ?? ''
                                ) }}"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Atas Nama
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="gift_name"
                                value="{{ old(
                                    'gift_name',
                                    $gift['name'] ?? ''
                                ) }}"
                            >

                        </div>

                    </div>

                </section>


                {{-- SETTINGS --}}

                <section
                    id="settings"
                    class="edit-card visual-builder-card"
                >

                    <div class="edit-section-head">

                        <div>

                            <h2 class="edit-section-title">
                                Susunan & Tampilan Section
                            </h2>

                            <p class="edit-section-copy">
                                Tampilkan/sembunyikan section dan tarik urutannya ke atas atau ke bawah.
                            </p>

                        </div>

                    </div>


                    <div class="builder-theme-bar">

                        <div class="builder-theme-chip">
                            Editor Undangan
                        </div>

                        <div class="builder-theme-chip">
                            Drag untuk ubah urutan
                        </div>

                        <div class="builder-theme-chip">
                            Toggle untuk tampil/sembunyi
                        </div>

                    </div>


                    <div class="edit-grid">

<div class="edit-field">

                            <label class="edit-label">
                                Music URL
                            </label>

                            <input
                                class="edit-input"
                                type="url"
                                name="music_url"
                                value="{{ old(
                                    'music_url',
                                    $invitation->music_url
                                ) }}"
                            >

                        </div>


                        <div class="edit-field">

                            <label class="edit-label">
                                Custom Domain
                            </label>

                            <input
                                class="edit-input"
                                type="text"
                                name="custom_domain"
                                value="{{ old(
                                    'custom_domain',
                                    $invitation->custom_domain
                                ) }}"
                            >

                        </div>

                    </div>


                    <div class="section-manager-wrap">

                        <div class="section-manager-head">

                            <div>

                                <div class="section-manager-title">
                                    Susunan Section
                                </div>

                                <div class="section-manager-copy">
                                    Tarik ⋮⋮ untuk mengubah posisi. Toggle hijau berarti ditampilkan.
                                </div>

                            </div>

                        </div>


                        <input
                            type="hidden"
                            name="section_order"
                            id="sectionOrder"
                            value='@json($sectionOrder)'
                        >


                        <div class="builder-static-row">

                            <div
                                class="builder-fixed-item"
                                data-fixed-section="theme"
                            >

                                <div class="builder-fixed-drag">
                                    ◇
                                </div>

                                <div>

                                    <div class="builder-fixed-name">
                                        Tema
                                    </div>

                                    <div class="builder-fixed-status">
                                        Pilih gaya tampilan undangan
                                    </div>

                                </div>

                                <span
                                    class="theme-current-value"
                                    data-theme-current
                                >
                                    {{ ucfirst(
                                        old(
                                            'theme',
                                            $invitation->theme ?? 'modern'
                                        )
                                    ) }}
                                </span>

                                <button
                                    type="button"
                                    class="section-edit-button"
                                    data-fixed-edit="theme"
                                >
                                    Edit
                                </button>

                                <div
                                    class="section-editor-panel"
                                    data-fixed-panel="theme"
                                >

                                    <div class="theme-section-panel">

                                        <label
                                            class="theme-section-label"
                                            for="themeSelect"
                                        >
                                            Pilih Tema
                                        </label>

                                        <select
                                            id="themeSelect"
                                            class="theme-section-select"
                                            name="theme"
                                        >

                                            @foreach([
                                                'modern' => 'Modern',
                                                'minimal' => 'Minimal',
                                                'classic' => 'Classic',
                                                'floral' => 'Floral',
                                            ] as $value => $label)

                                                <option
                                                    value="{{ $value }}"
                                                    @selected(
                                                        old(
                                                            'theme',
                                                            $invitation->theme ?? 'modern'
                                                        ) === $value
                                                    )
                                                >
                                                    {{ $label }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                </div>

                            </div>


                            <div
                                class="builder-fixed-item"
                                data-fixed-section="opening"
                            >

                                <div class="builder-fixed-drag">
                                    ↕
                                </div>

                                <div>

                                    <div class="builder-fixed-name">
                                        Opening
                                    </div>

                                    <div class="builder-fixed-status">
                                        Selalu tampil paling atas
                                    </div>

                                </div>

                                <span
                                    class="builder-theme-chip"
                                    style="
                                        min-height:26px;
                                        padding:4px 8px;
                                        font-size:7px;
                                    "
                                >
                                    FIXED
                                </span>

                                <button
                                    type="button"
                                    class="section-edit-button"
                                    data-fixed-edit="opening"
                                >
                                    Edit
                                </button>

                                <div
                                    class="section-editor-panel"
                                    data-fixed-panel="opening"
                                >

                                    <div class="section-mini-preview modern-dark">

                                        <div class="section-mini-preview-inner">

                                            <div class="section-mini-kicker">
                                                The Wedding Of
                                            </div>

                                            <div class="section-mini-title">
                                                {{ $invitation->groom_name ?: 'Groom' }}
                                                &
                                                {{ $invitation->bride_name ?: 'Bride' }}
                                            </div>

                                            <div class="section-mini-copy">
                                                Opening mengikuti cover dan tema yang dipilih.
                                            </div>

                                        </div>

                                    </div>


                                    <div data-fixed-editor-slot="opening"></div>

                                </div>

                            </div>

                        </div>


                        <div
                            class="section-manager"
                            id="sectionManager"
                        >

                            @foreach($sectionOrder as $sectionKey)

                                @php
                                    $isVisible = old(
                                        'section_' . $sectionKey,
                                        $sections[$sectionKey] ?? true
                                    );
                                @endphp

                                <div
                                    class="section-manager-item"
                                    data-section="{{ $sectionKey }}"
                                    draggable="true"
                                >

                                    <button
                                        type="button"
                                        class="section-drag"
                                        aria-label="Geser {{ $sectionLabels[$sectionKey] }}"
                                        title="Tarik untuk mengubah urutan"
                                    >
                                        ⋮⋮
                                    </button>


                                    <div>

                                        <div class="section-manager-name">
                                            {{ $sectionLabels[$sectionKey] }}
                                        </div>

                                        <div
                                            class="section-manager-status"
                                            data-section-status
                                        >
                                            {{ $isVisible ? 'Ditampilkan' : 'Disembunyikan' }}
                                        </div>

                                    </div>


                                    <label class="section-toggle">

                                        <input
                                            type="checkbox"
                                            name="section_{{ $sectionKey }}"
                                            value="1"
                                            @checked($isVisible)
                                            data-section-toggle
                                        >

                                        <span class="section-toggle-track"></span>

                                    </label>


                                    <button
                                        type="button"
                                        class="section-edit-button"
                                        data-section-edit="{{ $sectionKey }}"
                                    >
                                        Edit
                                    </button>


                                    <div
                                        class="section-editor-panel"
                                        data-section-panel="{{ $sectionKey }}"
                                    >

                                        <div
                                            class="theme-section-preview"
                                            data-theme-preview="{{ $sectionKey }}"
                                        >

                                            @switch($sectionKey)

                                                @case('groom')

                                                    <div class="theme-couple-preview">

                                                        <div class="theme-couple-copy groom">

                                                            <div class="preview-kicker">
                                                                The Groom
                                                            </div>

                                                            <div data-inline-host="groom_name"></div>
                                                            <div data-inline-host="groom_parent_text"></div>
                                                            <div data-inline-host="groom_origin"></div>
                                                            <div data-inline-host="groom_instagram"></div>

                                                        </div>

                                                        <div class="inline-photo-shell">

                                                            <div data-inline-photo-host="groom"></div>

                                                            <div class="inline-photo-label">
                                                                Geser foto untuk atur framing
                                                            </div>

                                                            <button
                                                                type="button"
                                                                class="inline-photo-reset"
                                                                data-inline-reset="groom"
                                                            >
                                                                Reset Tengah
                                                            </button>

                                                            <button
                                                                type="button"
                                                                class="inline-photo-plus"
                                                                data-inline-upload="groom"
                                                                aria-label="Ganti foto Groom"
                                                                title="Ganti foto Groom"
                                                            >
                                                                +
                                                            </button>

                                                        </div>

                                                    </div>

                                                    @break


                                                @case('bride')

                                                    <div class="theme-couple-preview">

                                                        <div class="theme-couple-copy">

                                                            <div class="preview-kicker">
                                                                The Bride
                                                            </div>

                                                            <div data-inline-host="bride_name"></div>
                                                            <div data-inline-host="bride_parent_text"></div>
                                                            <div data-inline-host="bride_origin"></div>
                                                            <div data-inline-host="bride_instagram"></div>

                                                        </div>

                                                        <div class="inline-photo-shell">

                                                            <div data-inline-photo-host="bride"></div>

                                                            <div class="inline-photo-label">
                                                                Geser foto untuk atur framing
                                                            </div>

                                                            <button
                                                                type="button"
                                                                class="inline-photo-reset"
                                                                data-inline-reset="bride"
                                                            >
                                                                Reset Tengah
                                                            </button>

                                                            <button
                                                                type="button"
                                                                class="inline-photo-plus"
                                                                data-inline-upload="bride"
                                                                aria-label="Ganti foto Bride"
                                                                title="Ganti foto Bride"
                                                            >
                                                                +
                                                            </button>

                                                        </div>

                                                    </div>

                                                    @break


                                                @case('quote')

                                                    <div class="theme-quote-preview">

                                                        <div class="preview-kicker">
                                                            Quote
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            “
                                                        </div>

                                                        <div
                                                            class="inline-quote-host"
                                                            data-inline-host="quote"
                                                        ></div>

                                                    </div>

                                                    <div class="inline-edit-note">
                                                        Edit quote langsung di preview.
                                                    </div>

                                                    @break


                                                @case('story')

                                                    <div class="theme-story-preview">

                                                        <div class="preview-kicker">
                                                            Our Story
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            Cerita Kami
                                                        </div>

                                                        <div
                                                            class="inline-story-host"
                                                            data-inline-host="story"
                                                        ></div>

                                                    </div>

                                                    <div class="inline-edit-note">
                                                        Edit cerita langsung di preview.
                                                    </div>

                                                    @break


                                                @case('gallery')

                                                    <div
                                                        class="theme-gallery-preview builder-gallery-grid"
                                                        id="galleryGrid"
                                                    >

                                                        @foreach($gallery as $index => $previewPhoto)

                                                            <div
                                                                class="gallery-card builder-gallery-card"
                                                                draggable="true"
                                                                data-path="{{ $previewPhoto }}"
                                                            >

                                                                <img
                                                                    src="{{ url(
                                                                        '/storage/'
                                                                        .
                                                                        $previewPhoto
                                                                    ) }}"
                                                                    alt="Gallery {{ $index + 1 }}"
                                                                >

                                                                <button
                                                                    type="button"
                                                                    class="builder-gallery-delete"
                                                                    onclick="deleteGallery({{ $index }})"
                                                                    aria-label="Hapus foto"
                                                                >
                                                                    ×
                                                                </button>

                                                            </div>

                                                        @endforeach


                                                        <label
                                                            class="builder-gallery-add"
                                                            for="galleryInput"
                                                        >

                                                            <span class="builder-gallery-plus">
                                                                +
                                                            </span>

                                                            <span>
                                                                Tambah Foto
                                                            </span>

                                                            <input
                                                                id="galleryInput"
                                                                type="file"
                                                                name="gallery[]"
                                                                multiple
                                                                accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/jpeg,image/png,image/webp,image/heic,image/heif"
                                                                hidden
                                                            >

                                                        </label>

                                                    </div>

                                                    <div class="builder-gallery-help">
                                                        Klik <strong>Tambah Foto</strong> langsung di preview. Kamu juga bisa geser foto lama untuk mengatur urutan, lalu tekan Simpan.
                                                    </div>

                                                    @break


                                                @case('event')

                                                    <div class="theme-event-preview">

                                                        <div class="theme-event-date">

                                                            <div class="preview-kicker">
                                                                Wedding Day
                                                            </div>

                                                            <div class="theme-event-day">
                                                                {{ $invitation->event_date
                                                                    ? $invitation->event_date->format('d')
                                                                    : '--'
                                                                }}
                                                            </div>

                                                        </div>

                                                        <div class="theme-event-info">

                                                            <div class="preview-kicker">
                                                                Venue
                                                            </div>

                                                            <div class="theme-event-name">
                                                                {{ $invitation->venue_name ?: 'Lokasi Acara' }}
                                                            </div>

                                                            <div class="theme-preview-copy">
                                                                {{ $invitation->venue_address ?: 'Alamat acara' }}
                                                            </div>

                                                        </div>

                                                    </div>

                                                    @break


                                                @case('countdown')

                                                    <div class="theme-simple-preview">

                                                        <div class="preview-kicker">
                                                            Save The Date
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            {{ $invitation->event_date
                                                                ? $invitation->event_date->format('d . m . Y')
                                                                : 'Tanggal Acara'
                                                            }}
                                                        </div>

                                                        <div class="theme-preview-copy">
                                                            Countdown otomatis mengikuti tanggal acara.
                                                        </div>

                                                    </div>

                                                    @break


                                                @case('gift')

                                                    <div class="theme-quote-preview">

                                                        <div class="preview-kicker">
                                                            Wedding Gift
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            {{ $gift['bank'] ?? 'Amplop Digital' }}
                                                        </div>

                                                        <div class="theme-preview-copy">
                                                            {{ $gift['number'] ?? 'Nomor rekening akan tampil di sini.' }}
                                                        </div>

                                                    </div>

                                                    @break


                                                @case('rsvp')

                                                    <div class="theme-simple-preview">

                                                        <div class="preview-kicker">
                                                            Reservation
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            RSVP
                                                        </div>

                                                        <div class="theme-preview-copy">
                                                            Form konfirmasi kehadiran tamu.
                                                        </div>

                                                    </div>

                                                    @break


                                                @case('guest_photo')

                                                    <div class="theme-simple-preview">

                                                        <div class="preview-kicker">
                                                            Guest Moment
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            Foto Tamu
                                                        </div>

                                                        <div class="theme-preview-copy">
                                                            Foto tamu yang disetujui akan muncul di section ini.
                                                        </div>

                                                    </div>

                                                    @break


                                                @case('wishes')

                                                    <div class="theme-simple-preview">

                                                        <div class="preview-kicker">
                                                            Wishes
                                                        </div>

                                                        <div class="theme-preview-title">
                                                            Ucapan & Doa
                                                        </div>

                                                        <div class="theme-preview-copy">
                                                            Ucapan tamu yang sudah disetujui akan tampil di sini.
                                                        </div>

                                                    </div>

                                                    @break

                                            @endswitch

                                        </div>


                                        <div
                                            data-section-editor-slot="{{ $sectionKey }}"
                                        ></div>

                                    </div>

                                </div>

                            @endforeach

                        </div>


                        <div class="builder-bottom-fixed">

                            <div
                                class="builder-fixed-item closing"
                                data-fixed-section="closing"
                            >

                                <div class="builder-fixed-drag">
                                    ↕
                                </div>

                                <div>

                                    <div class="builder-fixed-name">
                                        Closing
                                    </div>

                                    <div class="builder-fixed-status">
                                        Selalu tampil paling bawah
                                    </div>

                                </div>

                                <span
                                    class="builder-theme-chip"
                                    style="
                                        min-height:26px;
                                        padding:4px 8px;
                                        font-size:7px;
                                    "
                                >
                                    FIXED
                                </span>

                                <button
                                    type="button"
                                    class="section-edit-button"
                                    data-fixed-edit="closing"
                                >
                                    Edit
                                </button>

                                <div
                                    class="section-editor-panel"
                                    data-fixed-panel="closing"
                                >

                                    <div class="theme-section-preview dark">

                                        <div class="theme-simple-preview">

                                            <div class="preview-kicker">
                                                Thank You
                                            </div>

                                            <div class="theme-preview-title">
                                                {{ $invitation->groom_name ?: 'Groom' }}
                                                &
                                                {{ $invitation->bride_name ?: 'Bride' }}
                                            </div>

                                            <div class="theme-preview-copy">
                                                Closing mengikuti tema aktif dan selalu berada di akhir undangan.
                                            </div>

                                        </div>

                                    </div>

                                    <div class="section-inline-note">
                                        Closing tidak dapat dipindahkan agar struktur undangan tetap stabil.
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="builder-save-hint">
                            Setelah mengubah urutan, toggle, foto, atau isi section, tekan Simpan di bawah.
                        </div>

                    </div>

                </section>


                {{-- SAVE --}}

                <div class="save-bar">

                    <div>

                        <div class="save-title">

                            {{ $invitation->exists
                                ? 'Simpan perubahan'
                                : 'Buat undangan'
                            }}

                        </div>

                        <div class="save-copy">
                            Pastikan seluruh data sudah benar.
                        </div>

                    </div>


                    <button
                        type="submit"
                        class="edit-primary"
                    >
                        {{ $invitation->exists
                            ? 'Simpan'
                            : 'Buat Undangan'
                        }}
                    </button>

                </div>

            </main>

        </div>

    </form>


    {{-- DELETE COVER FORM --}}

    @if(
        $invitation->exists
        &&
        $invitation->cover_path
    )

        <form
            id="deleteCoverForm"
            method="POST"
            action="{{ route(
                'invitations.cover.destroy',
                $invitation
            ) }}"
            style="display:none;"
        >

            @csrf
            @method('DELETE')

        </form>

    @endif


    {{-- DELETE GALLERY FORMS --}}

    @if($invitation->exists)

        @foreach($gallery as $index => $path)

            <form
                id="deleteGalleryForm{{ $index }}"
                method="POST"
                action="{{ route(
                    'invitations.gallery.destroy',
                    [
                        $invitation,
                        $index
                    ]
                ) }}"
                style="display:none;"
            >

                @csrf
                @method('DELETE')

            </form>

        @endforeach


        {{-- PACKAGES --}}

        @if(isset($plans) && $plans->count())

            <section
                class="edit-card"
                style="margin-top:22px;"
            >

                <div class="edit-section-head">

                    <div>

                        <h2 class="edit-section-title">
                            Paket
                        </h2>

                        <p class="edit-section-copy">
                            Paket aktif:
                            <strong>
                                {{ strtoupper(
                                    $invitation->plan ?? 'free'
                                ) }}
                            </strong>
                        </p>

                    </div>

                </div>


                <div class="plan-grid">

                    @foreach($plans as $plan)

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            action="{{ route(
                                'orders.store',
                                $invitation
                            ) }}"
                            class="plan-card"
                        >

                            @csrf

                            <input
                                type="hidden"
                                name="plan_id"
                                value="{{ $plan->id }}"
                            >

                            <input
                                type="hidden"
                                name="payment_method"
                                value="transfer_manual"
                            >


                            <div class="plan-name">
                                {{ $plan->name }}
                            </div>

                            <div class="plan-price">
                                Rp{{ number_format(
                                    $plan->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                            <div class="edit-field">

                                <label class="edit-label">
                                    Bukti Transfer
                                </label>

                                <input
                                    type="file"
                                    name="payment_proof"
                                    required
                                    accept=".jpg,.jpeg,.png,.webp"
                                    style="
                                        width:100%;
                                        max-width:100%;
                                        font-size:10px;
                                    "
                                >

                            </div>


                            <button
                                type="submit"
                                class="edit-light"
                                style="margin-top:12px;"
                            >
                                Pilih Paket
                            </button>

                        </form>

                    @endforeach

                </div>

            </section>

        @endif


        {{-- OTHER --}}

        <section
            class="edit-card"
            style="margin-top:14px;"
        >

            <div class="edit-section-head">

                <div>

                    <h2 class="edit-section-title">
                        Lainnya
                    </h2>

                    <p class="edit-section-copy">
                        Kelola tamu atau hapus undangan.
                    </p>

                </div>

            </div>


            <div style="
                display:flex;
                align-items:center;
                gap:7px;
                flex-wrap:wrap;
            ">

                <a
                    href="{{ route(
                        'guests.index',
                        $invitation
                    ) }}"
                    class="edit-light"
                >
                    Kelola Tamu
                </a>


                <form
                    method="POST"
                    action="{{ route(
                        'invitations.destroy',
                        $invitation
                    ) }}"
                    onsubmit="
                        return confirm(
                            'Hapus undangan beserta semua medianya?'
                        )
                    "
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="edit-danger"
                    >
                        Hapus Undangan
                    </button>

                </form>

            </div>

        </section>

    @endif

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | COVER PREVIEW
    |--------------------------------------------------------------------------
    */

    const coverInput =
        document.getElementById('coverInput');

    const coverPreview =
        document.getElementById('coverPreview');

    const coverPlaceholder =
        document.getElementById('coverPlaceholder');


    if (coverInput && coverPreview) {

        coverInput.addEventListener(
            'change',
            function () {

                const file =
                    this.files &&
                    this.files[0];

                if (!file) {
                    return;
                }

                if (
                    file.type === 'image/heic'
                    ||
                    file.type === 'image/heif'
                    ||
                    /\.hei[cf]$/i.test(file.name)
                ) {
                    return;
                }

                const url =
                    URL.createObjectURL(file);

                coverPreview.src =
                    url;

                coverPreview.style.display =
                    'block';

                if (coverPlaceholder) {

                    coverPlaceholder.style.display =
                        'none';

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TRUE INLINE EDITOR — MOVE REAL CONTROLS, NO DUPLICATES
    |--------------------------------------------------------------------------
    */

    function moveRealInputToHost(
        fieldName,
        className
    ) {

        const realInput =
            document.querySelector(
                '[name="' + fieldName + '"]'
            );

        const host =
            document.querySelector(
                '[data-inline-host="' + fieldName + '"]'
            );

        if (
            !realInput
            ||
            !host
        ) {
            return;
        }

        const sourceField =
            realInput.closest(
                '.edit-field'
            );

        realInput.classList.add(
            'inline-real-input'
        );

        if (
            realInput.tagName
            ===
            'TEXTAREA'
        ) {
            realInput.classList.remove(
                'inline-real-input'
            );

            realInput.classList.add(
                'inline-real-textarea'
            );
        }

        if (className) {
            realInput.classList.add(
                className
            );
        }

        host.appendChild(
            realInput
        );

        if (
            sourceField
            &&
            sourceField.children.length
            <=
            1
        ) {
            sourceField.classList.add(
                'inline-source-hidden'
            );
        }

    }


    [
        ['groom_name', 'inline-name-input'],
        ['groom_parent_text', 'inline-meta-input'],
        ['groom_origin', 'inline-meta-input'],
        ['groom_instagram', 'inline-meta-input'],

        ['bride_name', 'inline-name-input'],
        ['bride_parent_text', 'inline-meta-input'],
        ['bride_origin', 'inline-meta-input'],
        ['bride_instagram', 'inline-meta-input'],

        ['quote', ''],
        ['story', '']
    ].forEach(
        function (config) {
            moveRealInputToHost(
                config[0],
                config[1]
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | MOVE REAL PHOTO ADJUSTER INTO THE THEME PREVIEW
    |--------------------------------------------------------------------------
    */

    ['groom', 'bride'].forEach(
        function (type) {

            const photoWrap =
                document.querySelector(
                    '[data-photo-adjust="' + type + '"]'
                )
                ? document
                    .querySelector(
                        '[data-photo-adjust="' + type + '"]'
                    )
                    .closest(
                        '.photo-adjust-wrap'
                    )
                : null;

            const photoHost =
                document.querySelector(
                    '[data-inline-photo-host="' + type + '"]'
                );

            const realFileInput =
                document.querySelector(
                    '[data-photo-input="' + type + '"]'
                );

            const uploadButton =
                document.querySelector(
                    '[data-inline-upload="' + type + '"]'
                );

            const resetButton =
                document.querySelector(
                    '[data-inline-reset="' + type + '"]'
                );

            const originalReset =
                document.querySelector(
                    '[data-photo-reset="' + type + '"]'
                );


            if (
                photoWrap
                &&
                photoHost
            ) {
                photoHost.appendChild(
                    photoWrap
                );
            }


            if (realFileInput) {

                /*
                | Keep the ONE real Laravel file input in the form,
                | but hide the browser's default file chooser.
                */
                realFileInput.style.display =
                    'none';

            }


            if (
                uploadButton
                &&
                realFileInput
            ) {

                uploadButton.addEventListener(
                    'click',
                    function () {

                        realFileInput.click();

                    }
                );

            }


            if (
                resetButton
                &&
                originalReset
            ) {

                resetButton.addEventListener(
                    'click',
                    function () {

                        originalReset.click();

                    }
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | HIDE NOW-EMPTY OLD PANELS
    |--------------------------------------------------------------------------
    */

    const identitySectionInline =
        document.getElementById(
            'identity'
        );

    if (identitySectionInline) {
        identitySectionInline.classList.add(
            'inline-source-hidden'
        );
    }

    const storySourceSection =
        document.getElementById(
            'story'
        );

    if (storySourceSection) {
        storySourceSection.classList.add(
            'inline-source-hidden'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INLINE GALLERY UPLOAD PREVIEW
    |--------------------------------------------------------------------------
    */

    const inlineGalleryInput =
        document.getElementById(
            'galleryInput'
        );

    const inlineGalleryGrid =
        document.getElementById(
            'galleryGrid'
        );


    if (
        inlineGalleryInput
        &&
        inlineGalleryGrid
    ) {

        inlineGalleryInput.addEventListener(
            'change',
            function () {

                inlineGalleryGrid
                    .querySelectorAll(
                        '.builder-gallery-pending'
                    )
                    .forEach(
                        function (item) {
                            item.remove();
                        }
                    );


                const addTile =
                    inlineGalleryGrid
                        .querySelector(
                            '.builder-gallery-add'
                        );


                Array.from(
                    inlineGalleryInput.files
                    || []
                )
                .forEach(
                    function (file) {

                        const reader =
                            new FileReader();

                        reader.onload =
                            function (event) {

                                const card =
                                    document.createElement(
                                        'div'
                                    );

                                card.className =
                                    'builder-gallery-card builder-gallery-pending';

                                const image =
                                    document.createElement(
                                        'img'
                                    );

                                image.src =
                                    event.target.result;

                                image.alt =
                                    'Foto baru';

                                card.appendChild(
                                    image
                                );


                                if (addTile) {

                                    inlineGalleryGrid
                                        .insertBefore(
                                            card,
                                            addTile
                                        );

                                } else {

                                    inlineGalleryGrid
                                        .appendChild(
                                            card
                                        );

                                }

                            };

                        reader.readAsDataURL(
                            file
                        );

                    }
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DELETE GALLERY
    |--------------------------------------------------------------------------
    */

    function deleteGallery(index) {

        const form =
            document.getElementById(
                'deleteGalleryForm'
                +
                index
            );

        if (
            form
            &&
            confirm(
                'Hapus foto ini dari galeri?'
            )
        ) {
            form.submit();
        }

    }


    /*
    |--------------------------------------------------------------------------
    | GALLERY REORDER
    |--------------------------------------------------------------------------
    */

    const galleryGrid =
        document.getElementById(
            'galleryGrid'
        );

    const orderInput =
        document.getElementById(
            'galleryOrder'
        );

    let draggedCard =
        null;


    function updateGalleryOrder() {

        if (
            !galleryGrid
            ||
            !orderInput
        ) {
            return;
        }

        const order =
            Array.from(
                galleryGrid.querySelectorAll(
                    '.gallery-card'
                )
            )
            .map(
                card =>
                    card.dataset.path
            );

        orderInput.value =
            JSON.stringify(order);

    }


    if (galleryGrid) {

        const cards =
            galleryGrid.querySelectorAll(
                '.gallery-card'
            );


        cards.forEach(
            card => {

                card.addEventListener(
                    'dragstart',
                    function () {

                        draggedCard =
                            this;

                        this.classList.add(
                            'dragging'
                        );

                    }
                );


                card.addEventListener(
                    'dragend',
                    function () {

                        this.classList.remove(
                            'dragging'
                        );

                        draggedCard =
                            null;

                        updateGalleryOrder();

                    }
                );

            }
        );


        galleryGrid.addEventListener(
            'dragover',
            function (event) {

                event.preventDefault();

                if (!draggedCard) {
                    return;
                }

                const afterElement =
                    getDragAfterElement(
                        galleryGrid,
                        event.clientY
                    );

                if (!afterElement) {

                    galleryGrid.appendChild(
                        draggedCard
                    );

                } else {

                    galleryGrid.insertBefore(
                        draggedCard,
                        afterElement
                    );

                }

            }
        );


        updateGalleryOrder();

    }


    function getDragAfterElement(
        container,
        y
    ) {

        const elements =
            [
                ...container.querySelectorAll(
                    '.gallery-card:not(.dragging)'
                )
            ];

        return elements.reduce(
            (closest, child) => {

                const box =
                    child.getBoundingClientRect();

                const offset =
                    y
                    -
                    box.top
                    -
                    box.height / 2;

                if (
                    offset < 0
                    &&
                    offset > closest.offset
                ) {

                    return {
                        offset:
                            offset,

                        element:
                            child
                    };

                }

                return closest;

            },
            {
                offset:
                    Number.NEGATIVE_INFINITY
            }
        ).element;

    }


    /*
    |--------------------------------------------------------------------------
    | VISUAL ACCORDION PAGE BUILDER
    |--------------------------------------------------------------------------
    */

    const editLayout =
        document.querySelector(
            '.edit-layout'
        );

    if (editLayout) {
        editLayout.classList.add(
            'builder-mode'
        );
    }


    const settingsCard =
        document.getElementById(
            'settings'
        );

    const editContent =
        document.querySelector(
            '.edit-content'
        );

    if (
        settingsCard
        &&
        editContent
    ) {
        editContent.prepend(
            settingsCard
        );
    }


    function moveToSlot(
        element,
        sectionKey
    ) {

        const slot =
            document.querySelector(
                '[data-section-editor-slot="'
                +
                sectionKey
                +
                '"]'
            );

        if (
            element
            &&
            slot
        ) {
            slot.appendChild(
                element
            );
        }

    }


    function addNote(
        sectionKey,
        text
    ) {

        const slot =
            document.querySelector(
                '[data-section-editor-slot="'
                +
                sectionKey
                +
                '"]'
            );

        if (
            !slot
            ||
            slot.children.length
        ) {
            return;
        }

        const note =
            document.createElement(
                'div'
            );

        note.className =
            'section-inline-note';

        note.textContent =
            text;

        slot.appendChild(
            note
        );

    }


    /*
    |--------------------------------------------------------------------------
    | OPENING
    |--------------------------------------------------------------------------
    */

    const identitySection =
        document.getElementById(
            'identity'
        );

    const coverSection =
        document.getElementById(
            'cover'
        );

    const openingSlot =
        document.querySelector(
            '[data-fixed-editor-slot="opening"]'
        );

    if (
        identitySection
        &&
        openingSlot
    ) {

        const identityGrid =
            identitySection.querySelector(
                '.edit-grid'
            );

        if (identityGrid) {
            openingSlot.appendChild(
                identityGrid
            );
        }

    }

    if (
        coverSection
        &&
        openingSlot
    ) {
        openingSlot.appendChild(
            coverSection
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GROOM / BRIDE
    |--------------------------------------------------------------------------
    */

    if (identitySection) {

        const coupleCards =
            identitySection.querySelectorAll(
                '.couple-profile-card'
            );

        if (coupleCards[0]) {
            moveToSlot(
                coupleCards[0],
                'groom'
            );
        }

        if (coupleCards[1]) {
            moveToSlot(
                coupleCards[1],
                'bride'
            );
        }

        identitySection.classList.add(
            'builder-original-holder'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | QUOTE — move quote field only
    |--------------------------------------------------------------------------
    */

    const quoteInput =
        document.querySelector(
            '[name="quote"]'
        );

    if (quoteInput) {

        const quoteField =
            quoteInput.closest(
                '.edit-field'
            );

        moveToSlot(
            quoteField,
            'quote'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DIRECT SECTIONS
    |--------------------------------------------------------------------------
    */

    [
        ['event', 'event'],
        ['story', 'story'],
        ['gift', 'gift']
    ].forEach(
        function (pair) {

            const element =
                document.getElementById(
                    pair[0]
                );

            moveToSlot(
                element,
                pair[1]
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SECTIONS WITHOUT EXTRA DATA
    |--------------------------------------------------------------------------
    */

    addNote(
        'countdown',
        'Countdown memakai tanggal acara. Ubah tanggal melalui menu Event.'
    );

    addNote(
        'rsvp',
        'RSVP memakai formulir konfirmasi kehadiran bawaan tema. Gunakan toggle untuk menampilkan atau menyembunyikannya.'
    );

    addNote(
        'guest_photo',
        'Guest Moment menampilkan unggahan foto tamu yang sudah disetujui.'
    );

    addNote(
        'wishes',
        'Wishes menampilkan ucapan tamu yang sudah disetujui.'
    );


    /*
    |--------------------------------------------------------------------------
    | OPEN / CLOSE ACCORDION
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        '[data-section-edit]'
    ).forEach(
        function (button) {

            button.addEventListener(
                'click',
                function () {

                    const item =
                        button.closest(
                            '.section-manager-item'
                        );

                    if (!item) {
                        return;
                    }

                    const willOpen =
                        !item.classList.contains(
                            'is-open'
                        );

                    document
                        .querySelectorAll(
                            '.section-manager-item.is-open'
                        )
                        .forEach(
                            function (openItem) {
                                if (
                                    openItem
                                    !==
                                    item
                                ) {
                                    openItem
                                        .classList
                                        .remove(
                                            'is-open'
                                        );

                                    const otherButton =
                                        openItem
                                            .querySelector(
                                                '[data-section-edit]'
                                            );

                                    if (otherButton) {
                                        otherButton.textContent =
                                            'Edit';
                                    }
                                }
                            }
                        );

                    item.classList.toggle(
                        'is-open',
                        willOpen
                    );

                    button.textContent =
                        willOpen
                            ? 'Tutup'
                            : 'Edit';

                    if (willOpen) {
                        setTimeout(
                            function () {
                                item.scrollIntoView({
                                    behavior:
                                        'smooth',
                                    block:
                                        'nearest'
                                });
                            },
                            60
                        );
                    }

                }
            );

        }
    );


    document.querySelectorAll(
        '[data-fixed-edit]'
    ).forEach(
        function (button) {

            button.addEventListener(
                'click',
                function () {

                    const item =
                        button.closest(
                            '.builder-fixed-item'
                        );

                    if (!item) {
                        return;
                    }

                    const willOpen =
                        !item.classList.contains(
                            'is-open'
                        );

                    item.classList.toggle(
                        'is-open',
                        willOpen
                    );

                    button.textContent =
                        willOpen
                            ? 'Tutup'
                            : 'Edit';

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | LIVE COUPLE PREVIEW
    |--------------------------------------------------------------------------
    */

    function syncCouplePreview(
        type
    ) {

        const fileInput =
            document.querySelector(
                '[data-photo-input="' + type + '"]'
            );

        const adjustImage =
            document.querySelector(
                '[data-photo-image="' + type + '"]'
            );

        const previewImage =
            document.querySelector(
                '[data-section="' + type + '"] .theme-couple-photo img'
            );

        if (
            fileInput
            &&
            previewImage
        ) {

            fileInput.addEventListener(
                'change',
                function () {

                    const file =
                        fileInput.files
                        &&
                        fileInput.files[0];

                    if (!file) {
                        return;
                    }

                    const reader =
                        new FileReader();

                    reader.onload =
                        function (event) {

                            previewImage.src =
                                event.target.result;

                        };

                    reader.readAsDataURL(
                        file
                    );

                }
            );

        }


        const xInput =
            document.querySelector(
                '[data-photo-x="' + type + '"]'
            );

        const yInput =
            document.querySelector(
                '[data-photo-y="' + type + '"]'
            );

        if (
            adjustImage
            &&
            previewImage
            &&
            xInput
            &&
            yInput
        ) {

            const observer =
                new MutationObserver(
                    function () {

                        previewImage.style.objectPosition =
                            xInput.value
                            +
                            '% '
                            +
                            yInput.value
                            +
                            '%';

                    }
                );

            observer.observe(
                adjustImage,
                {
                    attributes:true,
                    attributeFilter:[
                        'style',
                        'src'
                    ]
                }
            );

        }

    }


    syncCouplePreview(
        'groom'
    );

    syncCouplePreview(
        'bride'
    );


    /*
    |--------------------------------------------------------------------------
    | THEME-AWARE MINI PREVIEW
    |--------------------------------------------------------------------------
    */

    const themeSelect =
        document.querySelector(
            '[name="theme"]'
        );

    const themeCurrentValue =
        document.querySelector(
            '[data-theme-current]'
        );

    if (
        themeSelect
        &&
        themeCurrentValue
    ) {

        const syncThemeCurrent =
            function () {

                const selectedOption =
                    themeSelect.options[
                        themeSelect.selectedIndex
                    ];

                themeCurrentValue.textContent =
                    selectedOption
                        ? selectedOption.textContent.trim()
                        : themeSelect.value;

            };

        themeSelect.addEventListener(
            'change',
            syncThemeCurrent
        );

        syncThemeCurrent();

    }

    function refreshMiniTheme() {

        if (!themeSelect) {
            return;
        }

        const theme =
            themeSelect.value;

        document
            .querySelectorAll(
                '[data-theme-preview]'
            )
            .forEach(
                function (preview) {

                    preview.classList.toggle(
                        'modern-dark',
                        theme
                        ===
                        'modern'
                        &&
                        ['quote','gallery','gift']
                            .includes(
                                preview.dataset.themePreview
                            )
                    );

                }
            );

    }

    if (themeSelect) {

        themeSelect.addEventListener(
            'change',
            refreshMiniTheme
        );

        refreshMiniTheme();

    }


    /*
    |--------------------------------------------------------------------------
    | COUPLE PHOTO ADJUSTER
    |--------------------------------------------------------------------------
    */

    ['groom', 'bride'].forEach(
        function (type) {

            const stage =
                document.querySelector(
                    '[data-photo-adjust="' + type + '"]'
                );

            const image =
                document.querySelector(
                    '[data-photo-image="' + type + '"]'
                );

            const empty =
                document.querySelector(
                    '[data-photo-empty="' + type + '"]'
                );

            const input =
                document.querySelector(
                    '[data-photo-input="' + type + '"]'
                );

            const xInput =
                document.querySelector(
                    '[data-photo-x="' + type + '"]'
                );

            const yInput =
                document.querySelector(
                    '[data-photo-y="' + type + '"]'
                );

            const reset =
                document.querySelector(
                    '[data-photo-reset="' + type + '"]'
                );

            if (
                !stage
                ||
                !image
                ||
                !xInput
                ||
                !yInput
            ) {
                return;
            }

            let x =
                Number(xInput.value || 50);

            let y =
                Number(yInput.value || 50);

            let dragging =
                false;

            let startX =
                0;

            let startY =
                0;

            let startPositionX =
                x;

            let startPositionY =
                y;


            function clamp(
                value
            ) {
                return Math.max(
                    0,
                    Math.min(
                        100,
                        value
                    )
                );
            }


            function render() {

                x =
                    clamp(x);

                y =
                    clamp(y);

                xInput.value =
                    Math.round(x);

                yInput.value =
                    Math.round(y);

                image.style.objectPosition =
                    x + '% ' + y + '%';

                stage.dataset.x =
                    x;

                stage.dataset.y =
                    y;

            }


            if (input) {

                input.addEventListener(
                    'change',
                    function () {

                        const file =
                            input.files
                            &&
                            input.files[0];

                        if (!file) {
                            return;
                        }

                        const reader =
                            new FileReader();

                        reader.onload =
                            function (event) {

                                image.src =
                                    event.target.result;

                                image.style.display =
                                    'block';

                                if (empty) {
                                    empty.style.display =
                                        'none';
                                }

                                x = 50;
                                y = 50;

                                render();

                            };

                        reader.readAsDataURL(
                            file
                        );

                    }
                );

            }


            stage.addEventListener(
                'pointerdown',
                function (event) {

                    if (
                        !image.src
                        ||
                        image.style.display
                        ===
                        'none'
                    ) {
                        return;
                    }

                    dragging =
                        true;

                    startX =
                        event.clientX;

                    startY =
                        event.clientY;

                    startPositionX =
                        x;

                    startPositionY =
                        y;

                    stage.classList.add(
                        'is-dragging'
                    );

                    stage.setPointerCapture(
                        event.pointerId
                    );

                    event.preventDefault();

                }
            );


            stage.addEventListener(
                'pointermove',
                function (event) {

                    if (!dragging) {
                        return;
                    }

                    const rect =
                        stage.getBoundingClientRect();

                    const dx =
                        (
                            event.clientX
                            -
                            startX
                        )
                        /
                        rect.width
                        *
                        100;

                    const dy =
                        (
                            event.clientY
                            -
                            startY
                        )
                        /
                        rect.height
                        *
                        100;

                    x =
                        startPositionX
                        -
                        dx;

                    y =
                        startPositionY
                        -
                        dy;

                    render();

                    event.preventDefault();

                }
            );


            function finishDrag(
                event
            ) {

                if (!dragging) {
                    return;
                }

                dragging =
                    false;

                stage.classList.remove(
                    'is-dragging'
                );

                if (
                    event
                    &&
                    stage.hasPointerCapture(
                        event.pointerId
                    )
                ) {
                    stage.releasePointerCapture(
                        event.pointerId
                    );
                }

            }


            stage.addEventListener(
                'pointerup',
                finishDrag
            );

            stage.addEventListener(
                'pointercancel',
                finishDrag
            );

            stage.setAttribute(
                'tabindex',
                '0'
            );

            stage.setAttribute(
                'aria-label',
                'Atur posisi foto dengan drag atau tombol panah'
            );

            stage.addEventListener(
                'keydown',
                function (event) {

                    const step = 2;

                    if (event.key === 'ArrowLeft') {
                        x -= step;
                    } else if (event.key === 'ArrowRight') {
                        x += step;
                    } else if (event.key === 'ArrowUp') {
                        y -= step;
                    } else if (event.key === 'ArrowDown') {
                        y += step;
                    } else {
                        return;
                    }

                    render();
                    event.preventDefault();

                }
            );


            if (reset) {

                reset.addEventListener(
                    'click',
                    function () {

                        x = 50;
                        y = 50;

                        render();

                    }
                );

            }


            render();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SECTION MANAGER — DRAG + TOUCH REORDER
    |--------------------------------------------------------------------------
    */

    const sectionManager =
        document.getElementById(
            'sectionManager'
        );

    const sectionOrderInput =
        document.getElementById(
            'sectionOrder'
        );

    let draggedSection =
        null;

    let touchDraggedSection =
        null;


    function updateSectionOrder() {

        if (
            !sectionManager
            ||
            !sectionOrderInput
        ) {
            return;
        }

        const order =
            Array.from(
                sectionManager.querySelectorAll(
                    '.section-manager-item'
                )
            )
            .map(
                item =>
                    item.dataset.section
            );

        sectionOrderInput.value =
            JSON.stringify(order);

    }


    function updateSectionStatus(
        item
    ) {

        const checkbox =
            item.querySelector(
                '[data-section-toggle]'
            );

        const status =
            item.querySelector(
                '[data-section-status]'
            );

        if (
            !checkbox
            ||
            !status
        ) {
            return;
        }

        status.textContent =
            checkbox.checked
                ? 'Ditampilkan'
                : 'Disembunyikan';

    }


    if (sectionManager) {

        const sectionItems =
            sectionManager.querySelectorAll(
                '.section-manager-item'
            );


        sectionItems.forEach(
            item => {

                updateSectionStatus(
                    item
                );


                const checkbox =
                    item.querySelector(
                        '[data-section-toggle]'
                    );

                if (checkbox) {

                    checkbox.addEventListener(
                        'change',
                        function () {

                            updateSectionStatus(
                                item
                            );

                        }
                    );

                }


                item.addEventListener(
                    'dragstart',
                    function (event) {

                        if (
                            event.target.closest(
                                '.section-toggle'
                            )
                        ) {
                            event.preventDefault();
                            return;
                        }

                        draggedSection =
                            item;

                        item.classList.add(
                            'dragging'
                        );

                    }
                );


                item.addEventListener(
                    'dragend',
                    function () {

                        item.classList.remove(
                            'dragging'
                        );

                        draggedSection =
                            null;

                        updateSectionOrder();

                    }
                );


                const handle =
                    item.querySelector(
                        '.section-drag'
                    );

                if (handle) {

                    handle.addEventListener(
                        'pointerdown',
                        function (event) {

                            if (
                                event.pointerType
                                ===
                                'mouse'
                            ) {
                                return;
                            }

                            touchDraggedSection =
                                item;

                            item.classList.add(
                                'dragging'
                            );

                            handle.setPointerCapture(
                                event.pointerId
                            );

                            event.preventDefault();

                        }
                    );


                    handle.addEventListener(
                        'pointermove',
                        function (event) {

                            if (
                                !touchDraggedSection
                            ) {
                                return;
                            }

                            const target =
                                document.elementFromPoint(
                                    event.clientX,
                                    event.clientY
                                );

                            const overItem =
                                target
                                ? target.closest(
                                    '.section-manager-item'
                                )
                                : null;

                            if (
                                !overItem
                                ||
                                overItem
                                ===
                                touchDraggedSection
                            ) {
                                return;
                            }

                            const rect =
                                overItem
                                    .getBoundingClientRect();

                            const before =
                                event.clientY
                                <
                                rect.top
                                +
                                rect.height / 2;

                            if (before) {

                                sectionManager
                                    .insertBefore(
                                        touchDraggedSection,
                                        overItem
                                    );

                            } else {

                                sectionManager
                                    .insertBefore(
                                        touchDraggedSection,
                                        overItem.nextSibling
                                    );

                            }

                        }
                    );


                    const finishTouchDrag =
                        function () {

                            if (
                                !touchDraggedSection
                            ) {
                                return;
                            }

                            touchDraggedSection
                                .classList
                                .remove(
                                    'dragging'
                                );

                            touchDraggedSection =
                                null;

                            updateSectionOrder();

                        };


                    handle.addEventListener(
                        'pointerup',
                        finishTouchDrag
                    );

                    handle.addEventListener(
                        'pointercancel',
                        finishTouchDrag
                    );

                }

            }
        );


        sectionManager.addEventListener(
            'dragover',
            function (event) {

                event.preventDefault();

                if (!draggedSection) {
                    return;
                }

                const afterElement =
                    getSectionAfterElement(
                        sectionManager,
                        event.clientY
                    );

                if (!afterElement) {

                    sectionManager.appendChild(
                        draggedSection
                    );

                } else {

                    sectionManager.insertBefore(
                        draggedSection,
                        afterElement
                    );

                }

            }
        );


        updateSectionOrder();

    }


    function getSectionAfterElement(
        container,
        y
    ) {

        const elements =
            [
                ...container.querySelectorAll(
                    '.section-manager-item:not(.dragging)'
                )
            ];

        return elements.reduce(
            (closest, child) => {

                const box =
                    child.getBoundingClientRect();

                const offset =
                    y
                    -
                    box.top
                    -
                    box.height / 2;

                if (
                    offset < 0
                    &&
                    offset > closest.offset
                ) {
                    return {
                        offset:
                            offset,

                        element:
                            child
                    };
                }

                return closest;

            },
            {
                offset:
                    Number.NEGATIVE_INFINITY
            }
        ).element;

    }

</script>

@endsection