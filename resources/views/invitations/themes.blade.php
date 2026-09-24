@extends('layouts.app')

@section('content')
@include('invitations._wizard-styles')

<style>
.studio-template-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.studio-template-card{display:flex;min-height:330px;flex-direction:column;overflow:hidden;border:1px solid #dbe5f3;border-radius:16px;background:#fff}
.studio-template-preview{position:relative;height:190px;overflow:hidden;border-bottom:1px solid #edf1f6;background:#f8fafc}
.studio-template-phone{position:absolute;left:50%;top:16px;width:78px;height:158px;transform:translateX(-50%);overflow:hidden;border:1px solid #cfd9e8;border-radius:13px;background:#fff}
.studio-template-phone::before{content:'';position:absolute;left:50%;top:7px;width:22px;height:3px;transform:translateX(-50%);border-radius:99px;background:#d8e0eb}
.studio-template-phone-body{position:absolute;inset:18px 6px 6px;display:flex;align-items:center;justify-content:center;text-align:center;padding:8px;background:#fff;color:#29364c;font-family:Georgia,serif;font-size:9px;line-height:1.25}
.studio-template-body{display:flex;flex:1;flex-direction:column;padding:16px}
.studio-template-name{font-size:16px;font-weight:850;color:#172033}
.studio-template-meta{margin-top:5px;color:#748198;font-size:10px}
.studio-template-copy{margin:12px 0 0;color:#657289;font-size:11px;line-height:1.55}
.studio-template-actions{display:flex;margin-top:auto;padding-top:18px}
.studio-template-actions .ua-btn{width:100%}
.studio-empty{padding:28px;border:1px dashed #cbd9eb;border-radius:14px;background:#fbfcfe;text-align:center;color:#66758e}
@media(max-width:850px){.studio-template-grid{grid-template-columns:1fr}}
</style>

<main class="ua-flow">
    <header class="ua-flow-head">
        <div>
            <div class="ua-eyebrow">Langkah 2 dari 4</div>
            <h1 class="ua-title">Pilih template Studio</h1>
            <p class="ua-copy">
                Basic memilih template published admin. Premium / Intimate dan Royal juga dapat memulai dari blank canvas.
                Pilihan disimpan sebelum pembayaran dan tidak akan dipilih ulang setelah verifikasi.
            </p>
        </div>
        <a class="ua-back" href="{{ route('invitations.create') }}">Kembali ke paket</a>
    </header>

    <ol class="ua-steps">
        <li class="ua-step">1. Paket</li>
        <li class="ua-step active">2. Template</li>
        <li class="ua-step">3. Data awal</li>
        <li class="ua-step">4. Aktivasi</li>
    </ol>

    @if($errors->any())
        <div class="ua-error">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    @if($templates->isEmpty() && !($blankAllowed ?? false))
        <div class="studio-empty">
            <strong>Belum ada template Studio published.</strong><br>
            Admin perlu mem-publish minimal satu template Studio untuk paket {{ $plan->name }}.
        </div>
    @else
        <section class="studio-template-grid">
            @if($blankAllowed ?? false)
                <article class="studio-template-card">
                    <div class="studio-template-preview">
                        <div class="studio-template-phone"><div class="studio-template-phone-body">Blank Canvas</div></div>
                    </div>
                    <div class="studio-template-body">
                        <div class="studio-template-name">Mulai dari Blank Canvas</div>
                        <div class="studio-template-meta">Premium / Intimate & Royal · Full Studio</div>
                        <p class="studio-template-copy">Mulai dari canvas kosong lalu susun teks, gambar, shape, frame, grid, layer dan animasi sendiri.</p>
                        <div class="studio-template-actions">
                            <a class="ua-btn" href="{{ route('invitations.initial', ['plan'=>$plan->code,'template'=>'blank']) }}">Pilih Blank Canvas</a>
                        </div>
                    </div>
                </article>
            @endif
            @foreach($templates as $template)
                @php
                    $canvas = $template->canvas ?? [];
                    $opening = $canvas['openingCover'] ?? [];
                    $nameText = ($opening['bindNames'] ?? true)
                        ? 'Nama & Nama'
                        : ($opening['names'] ?? 'Nama & Nama');
                    $minPlan = strtoupper((string) $template->min_plan);
                @endphp

                <article class="studio-template-card">
                    <div class="studio-template-preview">
                        <div class="studio-template-phone">
                            <div class="studio-template-phone-body">
                                {{ $nameText }}
                            </div>
                        </div>
                    </div>

                    <div class="studio-template-body">
                        <div class="studio-template-name">{{ $template->name }}</div>
                        <div class="studio-template-meta">
                            Minimum paket: {{ $minPlan }} · Studio Template
                        </div>
                        <p class="studio-template-copy">
                            Template ini akan langsung menjadi desain awal undangan setelah pembayaran terverifikasi.
                        </p>

                        <div class="studio-template-actions">
                            <a class="ua-btn"
                               href="{{ route('invitations.initial', ['plan' => $plan->code, 'template' => $template->id]) }}">
                                Gunakan template
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</main>
@endsection
