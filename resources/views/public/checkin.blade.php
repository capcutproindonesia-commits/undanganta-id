<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Check-in · {{ $invitation->title }}
    </title>


    <style>

        *,
        *::before,
        *::after {
            box-sizing:border-box;
        }

        body {
            margin:0;

            min-height:100vh;

            display:grid;
            place-items:center;

            padding:24px;

            background:#f5f5f7;

            color:#1d1d1f;

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Helvetica,
                Arial,
                sans-serif;
        }

        .checkin-card {
            width:100%;
            max-width:420px;

            padding:28px;

            border:1px solid #e5e5e7;

            border-radius:20px;

            background:#fff;

            text-align:center;

            box-shadow:
                0 12px 40px
                rgba(0,0,0,.05);
        }

        .checkin-icon {
            width:58px;
            height:58px;

            display:grid;
            place-items:center;

            margin:0 auto 18px;

            border-radius:50%;

            background:#edf7ef;

            color:#248a3d;
        }

        .checkin-icon svg {
            width:27px;
            height:27px;

            fill:none;

            stroke:currentColor;

            stroke-width:2;

            stroke-linecap:round;
            stroke-linejoin:round;
        }

        .checkin-overline {
            margin-bottom:7px;

            color:#8e8e93;

            font-size:10px;
            font-weight:650;

            text-transform:uppercase;

            letter-spacing:.08em;
        }

        .checkin-title {
            margin:0;

            color:#1d1d1f;

            font-size:26px;
            line-height:1.1;

            letter-spacing:-.035em;
        }

        .checkin-copy {
            margin:10px 0 22px;

            color:#86868b;

            font-size:12px;
            line-height:1.6;
        }

        .checkin-data {
            overflow:hidden;

            margin-top:18px;

            border:1px solid #e5e5e7;

            border-radius:13px;

            text-align:left;
        }

        .checkin-row {
            display:flex;
            align-items:center;
            justify-content:space-between;

            gap:20px;

            padding:12px 14px;

            border-bottom:
                1px solid #eeeeef;
        }

        .checkin-row:last-child {
            border-bottom:0;
        }

        .checkin-label {
            color:#8e8e93;

            font-size:10px;
        }

        .checkin-value {
            color:#1d1d1f;

            font-size:11px;
            font-weight:650;

            text-align:right;
        }

        .checkin-notice {
            margin-top:16px;

            padding:10px 12px;

            border-radius:10px;

            background:#f5f5f7;

            color:#6e6e73;

            font-size:10px;

            line-height:1.5;
        }

        .checkin-notice.warning {
            background:#fff8e8;

            color:#8a6000;
        }

    </style>

</head>


<body>


<div class="checkin-card">


    <div class="checkin-icon">

        <svg viewBox="0 0 24 24">

            <path d="M5 12.5l4 4L19 7"></path>

        </svg>

    </div>


    <div class="checkin-overline">

        {{ $invitation->title }}

    </div>


    <h1 class="checkin-title">

        @if($alreadyCheckedIn)

            Sudah Check-in

        @else

            Check-in Berhasil

        @endif

    </h1>


    <p class="checkin-copy">

        @if($alreadyCheckedIn)

            QR tamu ini sebelumnya sudah digunakan untuk check-in.

        @else

            Kehadiran tamu berhasil dicatat.

        @endif

    </p>


    <div class="checkin-data">


        <div class="checkin-row">

            <span class="checkin-label">
                Nama
            </span>

            <span class="checkin-value">
                {{ $guest->name }}
            </span>

        </div>


        <div class="checkin-row">

            <span class="checkin-label">
                Kategori
            </span>

            <span class="checkin-value">
                {{ $guest->category ?: '—' }}
            </span>

        </div>


        <div class="checkin-row">

            <span class="checkin-label">
                Jumlah
            </span>

            <span class="checkin-value">
                {{ $guest->party_size ?: 1 }}
            </span>

        </div>


        <div class="checkin-row">

            <span class="checkin-label">
                Waktu Check-in
            </span>

            <span class="checkin-value">

                {{ $guest->checked_in_at
                    ? $guest->checked_in_at->format('d M Y · H:i')
                    : '—'
                }}

            </span>

        </div>


    </div>


    @if($alreadyCheckedIn)

        <div class="checkin-notice warning">

            Check-in pertama tetap dipertahankan.
            Scan ulang tidak mengubah waktu kehadiran.

        </div>

    @else

        <div class="checkin-notice">

            Data sudah tersimpan di daftar tamu UNDANGANTA.ID.

        </div>

    @endif


</div>


</body>

</html>