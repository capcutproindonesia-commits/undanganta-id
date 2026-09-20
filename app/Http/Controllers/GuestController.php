<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class GuestController extends Controller
{
    public function index(
        Request $request,
        Invitation $invitation
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        if ($invitation->plan === 'pending') {
            return redirect()
                ->route('orders.checkout', $invitation)
                ->with(
                    'ok',
                    'Aktifkan paket sebelum mengelola tamu.'
                );
        }

        $query = $invitation
            ->guests()
            ->latest();

        if ($request->filled('q')) {
            $search = trim(
                (string) $request->q
            );

            $query->where(
                function ($builder) use ($search) {
                    $builder
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'category',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        if ($request->filled('rsvp')) {
            if ($request->rsvp === 'belum') {
                $query->whereNull(
                    'rsvp_status'
                );
            } else {
                $query->where(
                    'rsvp_status',
                    $request->rsvp
                );
            }
        }

        $guests = $query
            ->paginate(40)
            ->withQueryString();

        $categories = $invitation
            ->guests()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $stats = [
            'total' => $invitation
                ->guests()
                ->count(),

            'hadir' => $invitation
                ->guests()
                ->where(
                    'rsvp_status',
                    'hadir'
                )
                ->count(),

            'tidak_hadir' => $invitation
                ->guests()
                ->where(
                    'rsvp_status',
                    'tidak_hadir'
                )
                ->count(),

            'checkin' => $invitation
                ->guests()
                ->whereNotNull(
                    'checked_in_at'
                )
                ->count(),
        ];

        return view(
            'guests.index',
            compact(
                'invitation',
                'guests',
                'categories',
                'stats'
            )
        );
    }

    public function store(
        Request $request,
        Invitation $invitation
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'category' => [
                'nullable',
                'string',
                'max:80',
            ],
        ]);

        $data['name'] = trim(
            $data['name']
        );

        if (
            array_key_exists(
                'phone',
                $data
            )
        ) {
            $data['phone'] = trim(
                (string) $data['phone']
            );
        }

        if (
            array_key_exists(
                'category',
                $data
            )
        ) {
            $data['category'] = trim(
                (string) $data['category']
            );
        }

        $invitation
            ->guests()
            ->create($data);

        return back()->with(
            'ok',
            'Tamu berhasil ditambahkan dan link personal sudah dibuat.'
        );
    }

    public function destroy(
        Invitation $invitation,
        Guest $guest
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        abort_unless(
            $guest->invitation_id
                ===
                $invitation->id,
            404
        );

        $guest->delete();

        return back()->with(
            'ok',
            'Tamu berhasil dihapus.'
        );
    }

    public function qr(
        Invitation $invitation,
        Guest $guest
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        abort_unless(
            $guest->invitation_id === $invitation->id,
            404
        );

        $checkinUrl = URL::temporarySignedRoute(
            'public.checkin',
            now()->addYears(5),
            [
                'invitation' => $invitation,
                'guest' => $guest,
            ]
        );

        $result = (new SvgWriter())->write(
            new QrCode(
                data: $checkinUrl,
                size: 360,
                margin: 16
            )
        );

        return response(
            $result->getString(),
            200,
            [
                'Content-Type' => $result->getMimeType(),
                'Content-Disposition' => 'inline; filename="'
                    . $invitation->slug
                    . '-guest-'
                    . $guest->id
                    . '-qr.svg"',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    public function checkinDesk(
        Request $request,
        Invitation $invitation
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        $query = $invitation
            ->guests()
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = trim((string) $request->q);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'checked') {
            $query->whereNotNull('checked_in_at');
        }

        if ($request->status === 'not_checked') {
            $query->whereNull('checked_in_at');
        }

        $guests = $query
            ->paginate(40)
            ->withQueryString();

        $totalGuests = $invitation->guests()->count();
        $checkedInCount = $invitation
            ->guests()
            ->whereNotNull('checked_in_at')
            ->count();
        $notCheckedInCount = $totalGuests - $checkedInCount;
        $totalPeopleCheckedIn = (int) $invitation
            ->guests()
            ->whereNotNull('checked_in_at')
            ->sum('party_size');

        return view(
            'guests.checkin',
            compact(
                'invitation',
                'guests',
                'totalGuests',
                'checkedInCount',
                'notCheckedInCount',
                'totalPeopleCheckedIn'
            )
        );
    }

    public function checkinManual(
        Invitation $invitation,
        Guest $guest
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        abort_unless(
            $guest->invitation_id === $invitation->id,
            404
        );

        if ($guest->checked_in_at) {
            return back()->with(
                'ok',
                $guest->name . ' sudah check-in sebelumnya.'
            );
        }

        $guest->update([
            'checked_in_at' => now(),
        ]);

        return back()->with(
            'ok',
            $guest->name . ' berhasil check-in.'
        );
    }

    public function export(
        Invitation $invitation
    ) {
        $this->authorize(
            'update',
            $invitation
        );

        $this->ensureActive($invitation);

        return response()->streamDownload(
            function () use ($invitation) {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv(
                    $handle,
                    [
                        'Nama',
                        'WhatsApp',
                        'Kategori',
                        'RSVP',
                        'Jumlah',
                        'Check-in',
                        'Personal Link',
                    ]
                );

                $invitation
                    ->guests()
                    ->orderBy('name')
                    ->chunk(
                        200,
                        function ($guests) use (
                            $handle,
                            $invitation
                        ) {
                            foreach ($guests as $guest) {
                                $url = route(
                                    'public.invitation',
                                    [
                                        'slug' =>
                                            $invitation->slug,

                                        'g' =>
                                            $guest->token,
                                    ]
                                );

                                fputcsv(
                                    $handle,
                                    [
                                        $guest->name,
                                        $guest->phone,
                                        $guest->category,
                                        $guest->rsvp_status,
                                        $guest->party_size,
                                        optional(
                                            $guest->checked_in_at
                                        )->format(
                                            'Y-m-d H:i:s'
                                        ),
                                        $url,
                                    ]
                                );
                            }
                        }
                    );

                fclose($handle);
            },
            $invitation->slug
                .
                '-tamu.csv',
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    private function ensureActive(
        Invitation $invitation
    ): void {
        abort_if(
            $invitation->plan === 'pending',
            403,
            'Undangan belum aktif.'
        );
    }
}
