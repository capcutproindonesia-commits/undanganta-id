@php
    $theme = strtolower(
        $invitation->theme ?? 'modern'
    );

    $allowedThemes = \App\Support\ThemeCatalog::codes();

    if (!in_array($theme, $allowedThemes, true)) {
        $theme = 'modern';
    }
@endphp

@include('public.themes.' . $theme)

@if(!empty($previewMode))
    <style>
        .theme-preview-bar {
            position:fixed;
            z-index:99999;
            top:14px;
            left:50%;
            display:flex;
            align-items:center;
            gap:12px;
            width:min(620px,calc(100% - 28px));
            padding:10px 12px 10px 16px;
            border:1px solid rgba(0,0,0,.12);
            border-radius:14px;
            background:rgba(255,255,255,.96);
            box-shadow:0 10px 35px rgba(0,0,0,.16);
            color:#1d1d1f;
            font:600 12px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            transform:translateX(-50%);
        }

        .theme-preview-bar span {
            min-width:0;
            flex:1;
        }

        .theme-preview-bar a {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-height:36px;
            padding:0 14px;
            border-radius:10px;
            background:#1d1d1f;
            color:#fff;
            text-decoration:none;
            white-space:nowrap;
        }
    </style>

    <div class="theme-preview-bar">
        <span>Mode preview · formulir dinonaktifkan</span>
        <a href="{{ route('invitations.themes', ['plan' => $previewPlan]) }}">
            Kembali ke tema
        </a>
    </div>

    <script>
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
            });
        });
    </script>
@endif