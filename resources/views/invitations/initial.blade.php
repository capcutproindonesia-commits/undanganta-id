@extends('layouts.app')

@section('content')
@include('invitations._wizard-styles')

<main class="ua-flow">
    <header class="ua-flow-head">
        <div>
            <div class="ua-eyebrow">Langkah 3 dari 4</div>
            <h1 class="ua-title">Isi data awal</h1>
            <p class="ua-copy">
                Data ini langsung disinkronkan ke template Studio setelah paket aktif.
            </p>
        </div>
        <a class="ua-back"
           href="{{ route('invitations.themes', ['plan' => $plan->code]) }}">
            Kembali ke template
        </a>
    </header>

    <ol class="ua-steps">
        <li class="ua-step">1. Paket</li>
        <li class="ua-step">2. Template</li>
        <li class="ua-step active">3. Data awal</li>
        <li class="ua-step">4. Aktivasi</li>
    </ol>

    @if($errors->any())
        <div class="ua-error">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="ua-summary">
        <div>
            <strong>{{ $plan->name }}</strong>
            <span> · Rp{{ number_format((int) $plan->price, 0, ',', '.') }}</span>
        </div>
        <div>
            <strong>{{ ($isBlank ?? false) ? 'Blank Canvas' : $template->name }}</strong>
            <span> · {{ ($isBlank ?? false) ? 'Full Studio dari canvas kosong' : 'UNDANGANTA Studio' }}</span>
        </div>
    </div>

    <section class="ua-panel">
        <form class="ua-form" method="POST" action="{{ route('invitations.store') }}">
            @csrf
            <input type="hidden" name="selected_plan" value="{{ $plan->code }}">
            <input type="hidden" name="studio_template_id" value="{{ $template->id }}">

            <div class="ua-fields">
                <div class="ua-field full">
                    <label for="title">Judul undangan</label>
                    <input class="ua-input" id="title" name="title" required maxlength="150"
                           value="{{ old('title') }}" placeholder="Pernikahan Arif & Siti">
                </div>

                <div class="ua-field">
                    <label for="groom_name">Nama pria</label>
                    <input class="ua-input" id="groom_name" name="groom_name" required maxlength="100"
                           value="{{ old('groom_name') }}" placeholder="Arif">
                </div>

                <div class="ua-field">
                    <label for="bride_name">Nama wanita</label>
                    <input class="ua-input" id="bride_name" name="bride_name" required maxlength="100"
                           value="{{ old('bride_name') }}" placeholder="Siti">
                </div>

                <div class="ua-field">
                    <label for="event_date">Tanggal & waktu acara</label>
                    <input class="ua-input" id="event_date" type="datetime-local" name="event_date" required
                           value="{{ old('event_date') }}">
                </div>

                <div class="ua-field">
                    <label for="venue_name">Nama lokasi</label>
                    <input class="ua-input" id="venue_name" name="venue_name" required maxlength="160"
                           value="{{ old('venue_name') }}" placeholder="Gedung / Rumah / Ballroom">
                </div>

                <div class="ua-field full">
                    <label for="venue_address">Alamat lokasi</label>
                    <textarea class="ua-textarea" id="venue_address" name="venue_address" maxlength="500"
                              placeholder="Alamat lengkap acara">{{ old('venue_address') }}</textarea>
                </div>

                <div class="ua-field full">
                    <label for="slug">Link khusus (opsional)</label>
                    <input class="ua-input" id="slug" name="slug" maxlength="160"
                           pattern="[a-z0-9-]+"
                           value="{{ old('slug') }}"
                           placeholder="arif-siti">
                </div>
            </div>

            <div class="ua-submit">
                <button class="ua-btn" type="submit">Lanjut ke pembayaran</button>
            </div>
        </form>
    </section>
</main>

<script>
(() => {
    const slug = document.getElementById('slug');
    if (!slug) return;
    slug.addEventListener('input', () => {
        slug.value = slug.value
            .toLowerCase()
            .replace(/[^a-z0-9-]+/g, '-')
            .replace(/-{2,}/g, '-')
            .replace(/^-|-$/g, '');
    });
})();
</script>
@endsection
