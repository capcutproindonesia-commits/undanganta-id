@extends('layouts.app')

@section('content')
@php
    $planCopy = [
        'basic' => [
            'label' => 'Basic',
            'note' => 'Template siap pakai',
            'features' => ['Template published dari admin', 'Edit data & media', 'RSVP dan buku tamu'],
        ],
        'premium' => [
            'label' => 'Premium / Intimate',
            'note' => 'Full UNDANGANTA Studio',
            'features' => ['Template admin atau blank canvas', 'Full Canva-like Studio', 'Guest photo & amplop digital'],
        ],
        'royal' => [
            'label' => 'Royal',
            'note' => 'Full Studio + akses Royal',
            'features' => ['Semua fitur Premium / Intimate', 'Template & fitur Royal', 'Prioritas layanan'],
        ],
    ];
@endphp

<style>
.plan-page{width:min(1080px,calc(100% - 32px));margin:0 auto;padding:34px 0 56px;color:#172033}
.plan-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px}
.plan-eyebrow{margin-bottom:6px;color:#61708a;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.plan-title{margin:0;font-size:32px;line-height:1.1;letter-spacing:-.035em}
.plan-copy{max-width:620px;margin:8px 0 0;color:#68758b;font-size:13px;line-height:1.6}
.plan-back{color:#315fca;text-decoration:none;font-size:12px;font-weight:750}
.plan-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.plan-card{display:flex;flex-direction:column;min-height:330px;padding:20px;border:1px solid #dbe5f3;border-radius:16px;background:#fff}
.plan-card.featured{border-color:#8db6ff;box-shadow:0 0 0 2px rgba(47,111,237,.08)}
.plan-name{font-size:18px;font-weight:800}.plan-note{margin-top:3px;color:#748198;font-size:11px}
.plan-price{margin-top:20px;font-size:27px;font-weight:850;letter-spacing:-.035em}
.plan-duration{margin-top:4px;color:#738098;font-size:11px}.plan-features{display:grid;gap:8px;margin:20px 0 24px;padding:0;list-style:none;color:#45526a;font-size:12px}
.plan-features li::before{content:'✓';margin-right:8px;color:#2f6fed;font-weight:900}.plan-action{margin-top:auto}
.plan-button{display:flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:10px;background:#2f6fed;color:#fff;text-decoration:none;font-size:12px;font-weight:800}
.plan-empty{padding:22px;border:1px solid #dbe5f3;border-radius:14px;background:#fff;color:#66758e}
@media(max-width:800px){.plan-grid{grid-template-columns:1fr}.plan-card{min-height:0}.plan-head{align-items:flex-start;flex-direction:column}.plan-title{font-size:28px}}
</style>

<main class="plan-page">
<header class="plan-head"><div><div class="plan-eyebrow">Langkah 1 dari 4</div><h1 class="plan-title">Pilih paket undangan</h1>
<p class="plan-copy">Basic memakai template published admin. Premium / Intimate dan Royal dapat memilih template atau memulai dari blank canvas.</p></div>
<a class="plan-back" href="{{ route('dashboard') }}">Kembali ke dashboard</a></header>

@if($plans->isEmpty())
<div class="plan-empty">Belum ada paket final aktif. Jalankan migration repair.</div>
@else
<section class="plan-grid">
@foreach($plans as $plan)
@php $code=strtolower((string)$plan->code); $copy=$planCopy[$code]??['label'=>$plan->name,'note'=>'Paket undangan','features'=>['Undangan digital']]; @endphp
<article class="plan-card {{ $code === 'premium' ? 'featured' : '' }}">
<div class="plan-name">{{ $copy['label'] }}</div><div class="plan-note">{{ $copy['note'] }}</div>
<div class="plan-price">Rp{{ number_format((int)$plan->price,0,',','.') }}</div>
<div class="plan-duration">Aktif {{ (int)$plan->duration_days }} hari</div>
<ul class="plan-features">@foreach($copy['features'] as $feature)<li>{{ $feature }}</li>@endforeach</ul>
<div class="plan-action"><a class="plan-button" href="{{ route('invitations.themes',['plan'=>$plan->code]) }}">Pilih {{ $copy['label'] }}</a></div>
</article>
@endforeach
</section>
@endif
</main>
@endsection
