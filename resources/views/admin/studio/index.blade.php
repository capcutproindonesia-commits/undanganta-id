@extends('layouts.app')

@section('content')
<style>
    .studio-page{max-width:1180px;margin:0 auto;padding:34px 22px 54px;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .studio-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}.studio-kicker{font-size:11px;color:#64748b;letter-spacing:.05em;text-transform:uppercase}
    .studio-title{margin:4px 0 0;font-size:32px;line-height:1.05;letter-spacing:-.035em;color:#172033}.studio-sub{margin:8px 0 0;color:#64748b;font-size:13px}
    .studio-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border-radius:10px;border:1px solid #2563eb;background:#2563eb;color:#fff;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none}
    .studio-note{margin-bottom:16px;padding:11px 13px;border:1px solid #cfe1ff;border-radius:11px;background:#f4f8ff;color:#4b6280;font-size:11px}
    .studio-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.studio-card{border:1px solid #e2e8f0;border-radius:14px;background:#fff;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.04)}
    .studio-thumb{aspect-ratio:4/3;background:linear-gradient(135deg,#edf5ff,#cfe4ff);display:grid;place-items:center;color:#45658c;font-size:12px;font-weight:750}.studio-body{padding:15px}
    .studio-row{display:flex;align-items:center;justify-content:space-between;gap:10px}.studio-name{font-size:15px;font-weight:800;color:#172033}.studio-meta{margin-top:6px;color:#7a8798;font-size:10px;line-height:1.5}
    .studio-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:13px}.studio-mini{padding:8px 10px;border-radius:8px;border:1px solid #d8e0eb;background:#fff;font-size:10px;font-weight:750;color:#263449;text-decoration:none;cursor:pointer}.studio-mini.danger{color:#b42318}
    .studio-pill{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:8px;font-weight:850;text-transform:uppercase;letter-spacing:.05em;background:#eef2f6;color:#596579}.studio-pill.live{background:#e9f8ef;color:#18723b}
    .studio-empty{padding:50px 20px;text-align:center;border:1px dashed #b8c5d6;border-radius:14px;color:#64748b;grid-column:1/-1}
    @media(max-width:900px){.studio-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.studio-page{padding:24px 14px 40px}.studio-head{align-items:flex-start;flex-direction:column}.studio-grid{grid-template-columns:1fr}.studio-title{font-size:27px}}
</style>

<div class="studio-page">
    <div class="studio-head">
        <div>
            <div class="studio-kicker">UNDANGANTA Studio</div>
            <h1 class="studio-title">Template visual tanpa coding.</h1>
            <p class="studio-sub">Satu kartu di bawah = satu template master yang berbeda.</p>
        </div>
        <form method="POST" action="{{ route('admin.studio.create') }}">
            @csrf
            <button class="studio-btn" type="submit">+ Template Baru</button>
        </form>
    </div>

    <div class="studio-note">Jika ada dua kartu bernama mirip, itu berarti ada dua draft template terpisah yang pernah dibuat. ID dan waktu dibuat ditampilkan supaya tidak tertukar.</div>

    <div class="studio-grid">
        @forelse($templates as $template)
            <article class="studio-card">
                <div class="studio-thumb">{{ strtoupper($template->min_plan) }} · 390×844</div>
                <div class="studio-body">
                    <div class="studio-row">
                        <div class="studio-name">{{ $template->name }} <span style="color:#94a3b8;font-weight:650">#{{ $template->id }}</span></div>
                        <span class="studio-pill {{ $template->status === 'published' ? 'live' : '' }}">{{ $template->status }}</span>
                    </div>
                    <div class="studio-meta">/{{ $template->slug }} · {{ $template->is_customer_editable ? 'Customer editable' : 'Locked' }}<br>Dibuat {{ optional($template->created_at)->format('d M Y H:i') }}</div>
                    <div class="studio-actions">
                        <a class="studio-mini" href="{{ route('admin.studio.edit', $template) }}">Edit Studio</a>
                        <form method="POST" action="{{ route('admin.studio.duplicate', $template) }}">@csrf<button class="studio-mini" type="submit">Duplicate</button></form>
                        <form method="POST" action="{{ route('admin.studio.publish', $template) }}">@csrf<button class="studio-mini" type="submit">{{ $template->status === 'published' ? 'Unpublish' : 'Publish' }}</button></form>
                        <form method="POST" action="{{ route('admin.studio.destroy', $template) }}" onsubmit="return confirm('Hapus template ini?')">@csrf @method('DELETE')<button class="studio-mini danger" type="submit">Hapus</button></form>
                    </div>
                </div>
            </article>
        @empty
            <div class="studio-empty">Belum ada template. Klik <b>Buat Template</b> untuk mulai.</div>
        @endforelse
    </div>
</div>
@endsection
