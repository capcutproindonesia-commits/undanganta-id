@php
    /*
     * Classic theme for UNDANGANTA.ID.
     * Visual structure adapted from dewanakl/undangan v3.14.0 (MIT License).
     * Copyright (c) 2023 dewana_kl. Data flow and forms use UNDANGANTA.ID.
     */
    $sections = $invitation->sections ?? [];
    $defaultOrder = ['countdown', 'quote', 'groom', 'bride', 'story', 'gallery', 'event', 'rsvp', 'gift', 'guest_photo', 'wishes'];
    $sectionOrder = collect(data_get($sections, 'order', $defaultOrder))
        ->filter(fn ($key) => in_array($key, $defaultOrder, true))
        ->unique()
        ->values()
        ->all();
    foreach ($defaultOrder as $key) {
        if (!in_array($key, $sectionOrder, true)) $sectionOrder[] = $key;
    }

    $gallery = collect($invitation->gallery ?? [])->map(function ($item) {
        if (is_string($item)) return $item;
        if (is_array($item)) return $item['path'] ?? $item['url'] ?? $item['file'] ?? null;
        if (is_object($item)) return $item->path ?? $item->url ?? $item->file ?? null;
        return null;
    })->filter()->values();

    $eventAt = $invitation->event_date
        ? \Illuminate\Support\Carbon::parse($invitation->event_date)
        : now()->addMonth();
    $mediaUrl = fn ($path) => $path ? url('/storage/' . ltrim($path, '/')) : null;
    $coverPath = $invitation->cover_path ?: $gallery->first();
    $coverUrl = $mediaUrl($coverPath);
    $groomUrl = $mediaUrl($invitation->groom_photo_path);
    $brideUrl = $mediaUrl($invitation->bride_photo_path);
    $guestName = $guest?->name ?: 'Tamu Undangan';
    $giftAccounts = collect($invitation->gift_accounts ?? [])->map(fn ($gift) => (array) $gift)->filter();
    if ($giftAccounts->isEmpty() && ($invitation->gift_bank || $invitation->gift_number)) {
        $giftAccounts = collect([[
            'bank' => $invitation->gift_bank,
            'number' => $invitation->gift_number,
            'name' => $invitation->gift_name,
        ]]);
    }
    $showSection = fn ($key) => (bool) data_get($sections, $key, true);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="theme-color" content="#1f2933">
    <title>{{ $invitation->groom_name }} & {{ $invitation->bride_name }}</title>
    <meta name="description" content="Undangan pernikahan {{ $invitation->groom_name }} dan {{ $invitation->bride_name }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;500;600&family=Noto+Naskh+Arabic:wght@500&family=Sacramento&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#263238;--muted:#6f777b;--paper:#fffaf2;--cream:#f3eadc;--deep:#1f2933;--gold:#b48a52;--line:rgba(38,50,56,.14);--serif:Georgia,'Times New Roman',serif;--sans:'Josefin Sans',sans-serif;--script:'Sacramento',cursive}
        *{box-sizing:border-box}html{scroll-behavior:smooth;background:var(--deep)}body{margin:0;color:var(--ink);background:var(--deep);font-family:var(--sans);font-weight:400;line-height:1.7}body.locked{overflow:hidden}button,input,select,textarea{font:inherit}a{color:inherit}img{display:block;max-width:100%}
        .page-shell{min-height:100vh}.desktop-cover{display:none}.invitation{position:relative;background:var(--paper);min-height:100vh;overflow:hidden}.section{position:relative;padding:76px 26px;text-align:center}.section.alt{background:var(--cream)}.eyebrow{margin:0 0 12px;color:var(--gold);font-size:.72rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase}.title{margin:0 0 18px;font-family:var(--script);font-size:clamp(2.8rem,12vw,4.8rem);font-weight:400;line-height:1}.copy{max-width:560px;margin:0 auto;color:var(--muted);font-size:.97rem}.ornament{display:flex;justify-content:center;align-items:center;gap:12px;margin:20px auto;color:var(--gold)}.ornament:before,.ornament:after{content:"";width:45px;height:1px;background:currentColor}.ornament span{font-family:var(--serif);font-size:1.2rem}.wave{height:48px;margin:-1px 0;background:var(--paper);clip-path:polygon(0 42%,18% 62%,35% 35%,53% 58%,72% 30%,100% 55%,100% 100%,0 100%)}.wave.to-alt{background:var(--cream)}
        .hero{min-height:100svh;display:grid;place-items:center;padding:80px 24px;background:linear-gradient(rgba(19,27,31,.56),rgba(19,27,31,.68)),var(--hero-image,linear-gradient(145deg,#44545c,#172127));background-position:center;background-size:cover;color:#fff}.hero-inner{width:min(100%,520px);text-align:center}.hero-kicker{font-size:.78rem;letter-spacing:.24em;text-transform:uppercase}.hero h1{margin:20px 0 6px;font-family:var(--script);font-size:clamp(4rem,17vw,6.7rem);font-weight:400;line-height:.86;text-shadow:0 8px 28px rgba(0,0,0,.25)}.hero-date{margin:22px 0 0;font-family:var(--serif);font-size:1rem;letter-spacing:.08em}.hero-scroll{display:inline-flex;flex-direction:column;align-items:center;gap:8px;margin-top:52px;color:rgba(255,255,255,.72);font-size:.68rem;letter-spacing:.16em;text-decoration:none;text-transform:uppercase}.hero-scroll:before{content:"";width:1px;height:38px;background:currentColor;animation:pulse 1.8s infinite}
        .gate{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:24px;background:linear-gradient(rgba(18,27,31,.52),rgba(18,27,31,.82)),var(--hero-image,linear-gradient(145deg,#44545c,#172127));background-position:center;background-size:cover;color:#fff;transition:opacity .65s ease,visibility .65s ease}.gate.closed{opacity:0;visibility:hidden;pointer-events:none}.gate-card{width:min(100%,430px);padding:38px 28px;text-align:center;border:1px solid rgba(255,255,255,.28);background:rgba(24,32,36,.55);backdrop-filter:blur(8px);border-radius:28px}.gate-small{font-size:.72rem;letter-spacing:.22em;text-transform:uppercase}.gate h1{margin:16px 0 8px;font-family:var(--script);font-size:4rem;font-weight:400;line-height:1}.guest-box{margin:26px 0;padding:16px;border-top:1px solid rgba(255,255,255,.28);border-bottom:1px solid rgba(255,255,255,.28)}.guest-box small{display:block;opacity:.78}.guest-box strong{display:block;margin-top:4px;font-family:var(--serif);font-size:1.12rem}.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 22px;border:1px solid var(--deep);border-radius:999px;background:var(--deep);color:#fff;font-size:.82rem;font-weight:600;letter-spacing:.04em;text-decoration:none;cursor:pointer;transition:.2s}.button:hover{transform:translateY(-1px);background:#34424b}.button.light{border-color:#fff;background:#fff;color:var(--deep)}.button.outline{background:transparent;color:var(--deep)}
        .countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;max-width:560px;margin:30px auto 0}.timebox{padding:18px 4px;border:1px solid var(--line);border-radius:18px;background:rgba(255,255,255,.58)}.timebox strong{display:block;font-family:var(--serif);font-size:1.6rem;font-weight:400;line-height:1}.timebox span{font-size:.61rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}.quote{max-width:600px;margin:auto;font-family:var(--serif);font-size:1.18rem;font-style:italic;line-height:1.9}.arabic{margin:0 0 22px;font-family:'Noto Naskh Arabic',serif;font-size:1.8rem}
        .couple{display:grid;gap:32px;max-width:580px;margin:30px auto 0}.portrait{position:relative;width:220px;aspect-ratio:1;margin:auto;border-radius:50%;padding:8px;border:1px solid var(--gold)}.portrait img,.portrait-placeholder{width:100%;height:100%;border-radius:50%;object-fit:cover;background:#ddd0bd}.portrait-placeholder{display:grid;place-items:center;color:#817565;font-family:var(--serif);font-size:.8rem}.person h3{margin:20px 0 4px;font-family:var(--script);font-size:3.3rem;font-weight:400;line-height:1}.person p{margin:5px auto;max-width:390px;color:var(--muted)}.instagram{display:inline-block;margin-top:10px;color:var(--gold);font-size:.84rem;text-decoration:none}
        .story-text{max-width:620px;margin:auto;white-space:pre-line}.gallery{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;max-width:640px;margin:28px auto 0}.gallery img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:20px}.gallery img:nth-child(3n){grid-column:span 2;aspect-ratio:16/10}.event-card,.form-card,.gift-card,.wish-card{max-width:620px;margin:26px auto 0;padding:25px;border:1px solid var(--line);border-radius:24px;background:rgba(255,255,255,.7);box-shadow:0 18px 50px rgba(38,50,56,.06)}.event-day{font-family:var(--serif);font-size:3.8rem;line-height:1;color:var(--gold)}.event-card h3{margin:14px 0 4px;font-family:var(--serif);font-weight:400}.event-card p{margin:4px 0;color:var(--muted)}
        .form-card{text-align:left}.field-label{display:block;margin:14px 0 6px;font-size:.7rem;font-weight:600;letter-spacing:.11em;text-transform:uppercase}.field{width:100%;min-height:46px;padding:11px 13px;border:1px solid var(--line);border-radius:13px;background:#fff;color:var(--ink);outline:none}.field:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(180,138,82,.13)}textarea.field{min-height:110px;resize:vertical}.form-grid{display:grid;grid-template-columns:1fr 120px;gap:12px}.form-action{margin-top:18px;text-align:center}.gift-grid,.wish-list,.photo-grid{display:grid;gap:12px;max-width:640px;margin:26px auto 0}.gift-card{margin:0}.gift-bank{font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;color:var(--gold)}.gift-number{margin:8px 0;font-family:var(--serif);font-size:1.35rem}.wish-card{margin:0;text-align:left}.wish-name{font-family:var(--serif);font-weight:600}.wish-message{margin-top:5px;color:var(--muted)}.photo-grid{grid-template-columns:repeat(2,1fr)}.photo-grid img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:16px}
        .closing{min-height:74svh;display:grid;place-items:center;padding:80px 25px;background:linear-gradient(rgba(22,30,34,.58),rgba(22,30,34,.78)),var(--closing-image,linear-gradient(145deg,#44545c,#172127));background-position:center;background-size:cover;color:#fff;text-align:center}.closing h2{margin:15px 0;font-family:var(--script);font-size:4.5rem;font-weight:400;line-height:1}.closing p{max-width:520px;margin:0 auto;color:rgba(255,255,255,.82)}.footer{padding:22px;background:#172127;color:rgba(255,255,255,.62);text-align:center;font-size:.7rem;letter-spacing:.08em}
        .nav{position:fixed;z-index:100;left:50%;bottom:max(14px,env(safe-area-inset-bottom));display:flex;gap:3px;transform:translateX(-50%);padding:6px;border:1px solid rgba(255,255,255,.25);border-radius:999px;background:rgba(27,36,41,.86);box-shadow:0 10px 35px rgba(0,0,0,.22);backdrop-filter:blur(10px)}.nav a,.nav button{display:grid;place-items:center;width:38px;height:38px;border:0;border-radius:50%;background:transparent;color:#fff;text-decoration:none;cursor:pointer}.nav a:hover,.nav button:hover{background:rgba(255,255,255,.13)}.reveal{opacity:0;transform:translateY(18px);transition:.7s ease}.reveal.visible{opacity:1;transform:none}.notice{max-width:620px;margin:0 auto 16px;padding:12px 15px;border-radius:14px;background:#eef8ee;color:#315739;font-size:.86rem}.error{background:#fff0ed;color:#8a3c31}
        @keyframes pulse{0%,100%{opacity:.25;transform:scaleY(.55);transform-origin:top}50%{opacity:1;transform:scaleY(1)}}
        @media(min-width:720px){.page-shell{display:grid;grid-template-columns:minmax(0,1fr) 440px}.desktop-cover{position:sticky;top:0;display:grid;place-items:center;height:100vh;padding:50px;background:linear-gradient(rgba(17,25,29,.42),rgba(17,25,29,.72)),var(--hero-image,linear-gradient(145deg,#44545c,#172127));background-position:center;background-size:cover;color:#fff;text-align:center}.desktop-cover h2{margin:16px 0 6px;font-family:var(--script);font-size:5.5rem;font-weight:400;line-height:.9}.desktop-cover p{font-family:var(--serif);letter-spacing:.08em}.invitation{box-shadow:-24px 0 70px rgba(0,0,0,.2)}.section{padding-left:36px;padding-right:36px}.nav{left:auto;right:24px;transform:none}}
        @media(max-width:420px){.section{padding:64px 18px}.countdown{gap:5px}.timebox{padding:15px 2px}.timebox strong{font-size:1.35rem}.form-grid{grid-template-columns:1fr}.gallery{gap:7px}.nav a,.nav button{width:36px;height:36px}.gate h1{font-size:3.4rem}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}.reveal{opacity:1;transform:none;transition:none}.hero-scroll:before{animation:none}}
    </style>
</head>
<body class="locked">
    <div id="classicGate" class="gate" style="--hero-image:url('{{ $coverUrl }}')">
        <div class="gate-card">
            <div class="gate-small">The Wedding Of</div>
            <h1>{{ $invitation->groom_name }} & {{ $invitation->bride_name }}</h1>
            <div class="guest-box"><small>Yth. Bapak/Ibu/Saudara/i</small><strong>{{ $guestName }}</strong></div>
            <button id="openInvitation" class="button light" type="button">Buka Undangan</button>
        </div>
    </div>

    <div class="page-shell" style="--hero-image:url('{{ $coverUrl }}')">
        <aside class="desktop-cover">
            <div><div class="eyebrow" style="color:#ead8b6">The Wedding Of</div><h2>{{ $invitation->groom_name }} & {{ $invitation->bride_name }}</h2><p>{{ $eventAt->locale('id')->translatedFormat('l, d F Y') }}</p></div>
        </aside>
        <main class="invitation">
            <section id="home" class="hero">
                <div class="hero-inner reveal"><div class="hero-kicker">Undangan Pernikahan</div><h1>{{ $invitation->groom_name }}<br>& {{ $invitation->bride_name }}</h1><div class="ornament"><span>❦</span></div><p class="hero-date">{{ $eventAt->locale('id')->translatedFormat('l, d F Y') }}</p><a class="hero-scroll" href="#content">Scroll</a></div>
            </section>
            <div id="content"></div>

            @foreach($sectionOrder as $key)
                @continue(!$showSection($key))
                @switch($key)
                    @case('countdown')
                        <section id="countdown" class="section alt"><div class="eyebrow">Save The Date</div><h2 class="title">Menuju Hari Bahagia</h2><p class="copy">Kami menantikan kehadiran Anda untuk menjadi bagian dari hari istimewa ini.</p><div id="classicCountdown" class="countdown" data-date="{{ $eventAt->format('Y-m-d\TH:i:sP') }}"><div class="timebox"><strong data-unit="days">0</strong><span>Hari</span></div><div class="timebox"><strong data-unit="hours">0</strong><span>Jam</span></div><div class="timebox"><strong data-unit="minutes">0</strong><span>Menit</span></div><div class="timebox"><strong data-unit="seconds">0</strong><span>Detik</span></div></div></section>
                        @break
                    @case('quote')
                        @if($invitation->quote)<section class="section"><p class="arabic">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p><div class="ornament"><span>❦</span></div><p class="quote reveal">“{{ $invitation->quote }}”</p></section>@endif
                        @break
                    @case('groom')
                        <section id="couple" class="section alt"><div class="eyebrow">The Groom</div><div class="person reveal"><div class="portrait">@if($groomUrl)<img src="{{ $groomUrl }}?v={{ $invitation->updated_at?->timestamp }}" alt="{{ $invitation->groom_name }}" style="object-position:{{ $invitation->groom_photo_x ?? 50 }}% {{ $invitation->groom_photo_y ?? 50 }}%">@else<div class="portrait-placeholder">Foto mempelai pria</div>@endif</div><h3>{{ $invitation->groom_name }}</h3>@if($invitation->groom_parent_text)<p>{{ $invitation->groom_parent_text }}</p>@endif @if($invitation->groom_origin)<p>{{ $invitation->groom_origin }}</p>@endif @if($invitation->groom_instagram)<a class="instagram" target="_blank" rel="noopener noreferrer" href="https://instagram.com/{{ ltrim($invitation->groom_instagram, '@') }}">{{ '@'.ltrim($invitation->groom_instagram, '@') }}</a>@endif</div></section>
                        @break
                    @case('bride')
                        <section class="section"><div class="eyebrow">The Bride</div><div class="person reveal"><div class="portrait">@if($brideUrl)<img src="{{ $brideUrl }}?v={{ $invitation->updated_at?->timestamp }}" alt="{{ $invitation->bride_name }}" style="object-position:{{ $invitation->bride_photo_x ?? 50 }}% {{ $invitation->bride_photo_y ?? 50 }}%">@else<div class="portrait-placeholder">Foto mempelai wanita</div>@endif</div><h3>{{ $invitation->bride_name }}</h3>@if($invitation->bride_parent_text)<p>{{ $invitation->bride_parent_text }}</p>@endif @if($invitation->bride_origin)<p>{{ $invitation->bride_origin }}</p>@endif @if($invitation->bride_instagram)<a class="instagram" target="_blank" rel="noopener noreferrer" href="https://instagram.com/{{ ltrim($invitation->bride_instagram, '@') }}">{{ '@'.ltrim($invitation->bride_instagram, '@') }}</a>@endif</div></section>
                        @break
                    @case('story')
                        @if($invitation->story)<section id="story" class="section alt"><div class="eyebrow">Our Story</div><h2 class="title">Cerita Kami</h2><div class="ornament"><span>❦</span></div><p class="copy story-text reveal">{{ $invitation->story }}</p></section>@endif
                        @break
                    @case('gallery')
                        @if($gallery->isNotEmpty())<section id="gallery" class="section"><div class="eyebrow">Gallery</div><h2 class="title">Momen Bahagia</h2><div class="gallery">@foreach($gallery as $image)<img class="reveal" src="{{ $mediaUrl($image) }}" loading="lazy" alt="Momen {{ $invitation->groom_name }} dan {{ $invitation->bride_name }}">@endforeach</div></section>@endif
                        @break
                    @case('event')
                        <section id="event" class="section alt"><div class="eyebrow">Save The Date</div><h2 class="title">Acara Pernikahan</h2><div class="event-card reveal"><div class="event-day">{{ $eventAt->format('d') }}</div><h3>{{ $eventAt->locale('id')->translatedFormat('F Y') }}</h3><p>{{ $eventAt->locale('id')->translatedFormat('l') }} · {{ $eventAt->format('H:i') }} WITA</p><div class="ornament"><span>❦</span></div><h3>{{ $invitation->venue_name }}</h3>@if($invitation->venue_address)<p>{{ $invitation->venue_address }}</p>@endif @if($invitation->maps_url)<p style="margin-top:18px"><a class="button outline" href="{{ $invitation->maps_url }}" target="_blank" rel="noopener noreferrer">Buka Lokasi</a></p>@endif</div></section>
                        @break
                    @case('rsvp')
                        <section id="rsvp" class="section"><div class="eyebrow">Reservation</div><h2 class="title">Konfirmasi Kehadiran</h2><p class="copy">Mohon konfirmasi untuk membantu kami mempersiapkan hari bahagia ini.</p>@if(session('ok'))<div class="notice">{{ session('ok') }}</div>@endif @if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif<form class="form-card reveal" method="POST" action="{{ route('public.rsvp', $invitation) }}">@csrf<input type="hidden" name="token" value="{{ request()->query('g') }}"><label class="field-label">Nama</label><input class="field" type="text" name="name" value="{{ old('name', $guest?->name) }}" placeholder="Nama Anda" @readonly($guest) required><div class="form-grid"><div><label class="field-label">Kehadiran</label><select class="field" name="status" required><option value="hadir">Iya, saya akan datang</option><option value="tidak_hadir">Maaf, saya tidak bisa datang</option><option value="ragu">Masih belum pasti</option></select></div><div><label class="field-label">Jumlah Tamu</label><input class="field" type="number" name="party_size" min="1" max="10" value="{{ old('party_size', $guest?->party_size ?: 1) }}"></div></div><label class="field-label">Ucapan & Doa</label><textarea class="field" name="message" placeholder="Tulis ucapan untuk kedua mempelai..."></textarea><div class="form-action"><button class="button" type="submit">Kirim Respon</button></div></form></section>
                        @break
                    @case('gift')
                        @if($giftAccounts->isNotEmpty())<section id="gift" class="section alt"><div class="eyebrow">Wedding Gift</div><h2 class="title">Tanda Kasih</h2><p class="copy">Doa dan kehadiran Anda adalah hadiah terindah. Jika ingin memberikan tanda kasih, dapat melalui rekening berikut.</p><div class="gift-grid">@foreach($giftAccounts as $gift)<div class="gift-card reveal"><div class="gift-bank">{{ $gift['bank'] ?? 'Transfer' }}</div><div class="gift-number">{{ $gift['number'] ?? '-' }}</div>@if(!empty($gift['name']))<div>a.n. {{ $gift['name'] }}</div>@endif</div>@endforeach</div></section>@endif
                        @break
                    @case('guest_photo')
                        @if(in_array($invitation->plan, ['premium','pro'], true))<section id="guest-photo" class="section"><div class="eyebrow">Guest Moment</div><h2 class="title">Bagikan Momenmu</h2><form class="form-card reveal" method="POST" enctype="multipart/form-data" action="{{ route('public.photo', $invitation) }}">@csrf<input type="hidden" name="token" value="{{ request()->query('g') }}"><label class="field-label">Nama</label><input class="field" type="text" name="guest_name" value="{{ $guest?->name }}" placeholder="Nama Anda" @readonly($guest) required><label class="field-label">Foto</label><input class="field" type="file" name="photo" accept="image/*" required><label class="field-label">Caption</label><input class="field" type="text" name="caption" placeholder="Caption opsional"><div class="form-action"><button class="button" type="submit">Kirim Foto</button></div></form>@if($photos->count())<div class="photo-grid">@foreach($photos as $photo)<img class="reveal" src="{{ url('/storage/'.$photo->path) }}" loading="lazy" alt="Guest moment">@endforeach</div>@endif</section>@endif
                        @break
                    @case('wishes')
                        @if($wishes->count())<section id="wishes" class="section alt"><div class="eyebrow">Wishes</div><h2 class="title">Ucapan & Doa</h2><div class="wish-list">@foreach($wishes as $wish)<article class="wish-card reveal"><div class="wish-name">{{ $wish->guest_name }}</div><div class="wish-message">{{ $wish->message }}</div></article>@endforeach</div></section>@endif
                        @break
                @endswitch
            @endforeach

            <section class="closing" style="--closing-image:url('{{ $mediaUrl($gallery->last() ?: $coverPath) }}')"><div class="reveal"><div class="eyebrow" style="color:#ead8b6">Thank You</div><h2>{{ $invitation->groom_name }} & {{ $invitation->bride_name }}</h2><p>Merupakan kehormatan bagi kami apabila Anda berkenan hadir dan memberikan doa restu.</p></div></section>
            <footer class="footer">Made with UNDANGANTA.ID</footer>
        </main>
    </div>

    <nav class="nav" aria-label="Navigasi undangan"><a href="#home" aria-label="Home">⌂</a><a href="#gallery" aria-label="Gallery">▦</a><a href="#event" aria-label="Event">◷</a><a href="#rsvp" aria-label="RSVP">✓</a>@if($invitation->music_url)<button id="musicToggle" type="button" aria-label="Musik">♫</button>@endif</nav>
    @if($invitation->music_url)<audio id="classicAudio" loop preload="none" src="{{ $invitation->music_url }}"></audio>@endif
    <script>
        (()=>{
            const gate=document.getElementById('classicGate');
            const open=document.getElementById('openInvitation');
            const audio=document.getElementById('classicAudio');
            open?.addEventListener('click',()=>{gate.classList.add('closed');document.body.classList.remove('locked');audio?.play().catch(()=>{});});
            const box=document.getElementById('classicCountdown');
            if(box){const target=new Date(box.dataset.date).getTime();const tick=()=>{let d=Math.max(0,target-Date.now());const v={days:Math.floor(d/86400000),hours:Math.floor(d/3600000)%24,minutes:Math.floor(d/60000)%60,seconds:Math.floor(d/1000)%60};Object.entries(v).forEach(([k,n])=>{const el=box.querySelector(`[data-unit="${k}"]`);if(el)el.textContent=String(n).padStart(2,'0')})};tick();setInterval(tick,1000)}
            const observer='IntersectionObserver'in window?new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');observer.unobserve(e.target)}}),{threshold:.12}):null;
            document.querySelectorAll('.reveal').forEach(el=>observer?observer.observe(el):el.classList.add('visible'));
            document.getElementById('musicToggle')?.addEventListener('click',()=>{if(!audio)return;if(audio.paused)audio.play().catch(()=>{});else audio.pause()});
        })();
    </script>
</body>
</html>
