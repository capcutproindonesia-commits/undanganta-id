@extends('layouts.app')

@section('content')

<style>
.studio-choice{max-width:1040px;margin:0 auto;padding:34px 22px 50px;color:#1f2937}
.studio-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px}
.studio-head h1{margin:0;font-size:30px;line-height:1.1}.studio-head p{margin:7px 0 0;color:#6b7280;font-size:12px}
.studio-back{display:inline-flex;align-items:center;height:36px;padding:0 12px;border:1px solid #d7e3f3;border-radius:9px;background:#fff;color:#31547d;text-decoration:none;font-size:10px;font-weight:800}
.studio-current{margin-bottom:18px;padding:12px 14px;border:1px solid #cfe0f7;border-radius:12px;background:#f5f9ff;font-size:11px}
.studio-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.studio-card{border:1px solid #dbe5f2;border-radius:13px;background:#fff;padding:14px;min-width:0}
.studio-card h3{margin:0 0 5px;font-size:14px}.studio-meta{font-size:9px;color:#718198;margin-bottom:12px}.studio-card form{margin:0}
.studio-use{width:100%;height:36px;border:0;border-radius:9px;background:#2563eb;color:#fff;font-size:10px;font-weight:850;cursor:pointer}
.studio-empty{padding:50px 18px;text-align:center;border:1px dashed #cbd9ea;border-radius:13px;color:#728198}
.studio-detach{margin-top:9px}.studio-detach button{height:32px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;color:#64748b;font-size:9px;font-weight:750;padding:0 10px}
@media(max-width:760px){.studio-choice{padding:22px 14px 34px}.studio-head{align-items:flex-start;flex-direction:column}.studio-grid{grid-template-columns:1fr}.studio-head h1{font-size:24px}}
</style>

<div class="studio-choice">
    <div class="studio-head">
        <div>
            <h1>Pilih Template Studio</h1>
            <p>{{ $invitation->groom_name }} & {{ $invitation->bride_name }} · Paket {{ ucfirst($invitation->plan) }}</p>
        </div>
        <a class="studio-back" href="{{ route('invitations.index') }}">Kembali</a>
    </div>

    @if($instance)
        <div class="studio-current">
            Template Studio sudah terhubung ke undangan ini.
            <div class="studio-detach">
                <form method="POST" action="{{ route('studio.invitation.detach', $invitation) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Lepas template Studio dari undangan ini?')">Lepas Studio</button>
                </form>
            </div>
        </div>
    @endif

    @if($templates->isEmpty())
        <div class="studio-empty">
            Belum ada template Studio published yang tersedia untuk paket ini.
        </div>
    @else
        <div class="studio-grid">
            @foreach($templates as $template)
                <article class="studio-card">
                    <h3>{{ $template->name }}</h3>
                    <div class="studio-meta">
                        Minimum {{ ucfirst($template->min_plan) }}
                    </div>

                    <form method="POST" action="{{ route('studio.invitation.attach', [$invitation, $template]) }}">
                        @csrf
                        <button class="studio-use" type="submit">
                            {{ $instance && (int)$instance->studio_template_id === (int)$template->id
                                ? 'Gunakan Ulang Template Ini'
                                : 'Gunakan Template'
                            }}
                        </button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</div>

@endsection
