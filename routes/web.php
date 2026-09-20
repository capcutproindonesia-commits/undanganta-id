<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicInvitationController;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function (Request $request) {

    $host = $request->getHost();

    $invitation = Invitation::where(
        'custom_domain',
        $host
    )
        ->where(
            'is_published',
            true
        )
        ->first();

    if ($invitation) {

        $invitation->increment(
            'views_count'
        );

        $hasPremiumFeatures = in_array(
            strtolower((string) $invitation->plan),
            ['premium', 'pro'],
            true
        );

        if (!$hasPremiumFeatures) {
            $invitation->setAttribute('gift_accounts', []);
            $invitation->setAttribute('music_url', null);
        }

        return view(
            'public.invitation',
            [
                'invitation' =>
                    $invitation,

                'guest' =>
                    null,

                'wishes' =>
                    $invitation
                        ->wishes()
                        ->latest()
                        ->take(30)
                        ->get(),

                'photos' =>
                    $invitation
                        ->photos()
                        ->where(function ($query) {
                            $query
                                ->where('status', 'approved')
                                ->orWhere('is_approved', true);
                        })
                        ->take(50)
                        ->get(),
            ]
        );
    }

    return view('welcome');

})->name('home');


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')
    ->group(function () {

        Route::get(
            '/login',
            [
                AuthController::class,
                'loginForm'
            ]
        )->name('login');

        Route::post(
            '/login',
            [
                AuthController::class,
                'login'
            ]
        );

        Route::get(
            '/register',
            [
                AuthController::class,
                'registerForm'
            ]
        )->name('register');

        Route::post(
            '/register',
            [
                AuthController::class,
                'register'
            ]
        );
    });


Route::post(
    '/logout',
    [
        AuthController::class,
        'logout'
    ]
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| PUBLIC INVITATION
|--------------------------------------------------------------------------
*/

Route::get(
    '/u/{slug}',
    [
        PublicInvitationController::class,
        'show'
    ]
)->name('public.invitation');


Route::post(
    '/u/{invitation}/rsvp',
    [
        PublicInvitationController::class,
        'rsvp'
    ]
)->name('public.rsvp');


Route::post(
    '/u/{invitation}/photo',
    [
        PublicInvitationController::class,
        'photo'
    ]
)->name('public.photo');


Route::get(
    '/checkin/{invitation}/{guest}',
    [
        PublicInvitationController::class,
        'checkin'
    ]
)
    ->middleware('signed')
    ->name('public.checkin');


/*
|--------------------------------------------------------------------------
| USER AREA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->group(function () {

        Route::get(
            '/dashboard',
            DashboardController::class
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | INVITATIONS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/invitations',
            [
                InvitationController::class,
                'index'
            ]
        )->name('invitations.index');


        Route::get(
            '/invitations/create',
            [
                InvitationController::class,
                'create'
            ]
        )->name('invitations.create');


        Route::get(
            '/invitations/create/themes',
            [
                InvitationController::class,
                'themes'
            ]
        )->name('invitations.themes');


        Route::get(
            '/invitations/create/details',
            [
                InvitationController::class,
                'initial'
            ]
        )->name('invitations.initial');


        Route::get(
            '/invitations/themes/{theme}/preview',
            [
                InvitationController::class,
                'previewTheme'
            ]
        )->name('invitations.theme.preview');


        Route::post(
            '/invitations',
            [
                InvitationController::class,
                'store'
            ]
        )->name('invitations.store');


        Route::get(
            '/invitations/{invitation}/edit',
            [
                InvitationController::class,
                'edit'
            ]
        )->name('invitations.edit');


        Route::match(
            [
                'put',
                'patch'
            ],
            '/invitations/{invitation}',
            [
                InvitationController::class,
                'update'
            ]
        )->name('invitations.update');


        Route::delete(
            '/invitations/{invitation}',
            [
                InvitationController::class,
                'destroy'
            ]
        )->name('invitations.destroy');


        Route::post(
            '/invitations/{invitation}/publish',
            [
                InvitationController::class,
                'publish'
            ]
        )->name('invitations.publish');


        /*
        |--------------------------------------------------------------------------
        | MEDIA
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/invitations/{invitation}/cover',
            [
                InvitationController::class,
                'deleteCover'
            ]
        )->name(
            'invitations.cover.destroy'
        );


        Route::delete(
            '/invitations/{invitation}/gallery/{index}',
            [
                InvitationController::class,
                'deleteGallery'
            ]
        )
            ->whereNumber('index')
            ->name(
                'invitations.gallery.destroy'
            );


        /*
        |--------------------------------------------------------------------------
        | GUESTS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/invitations/{invitation}/guests',
            [
                GuestController::class,
                'index'
            ]
        )->name('guests.index');


        Route::post(
            '/invitations/{invitation}/guests',
            [
                GuestController::class,
                'store'
            ]
        )->name('guests.store');


        Route::get(
            '/invitations/{invitation}/guests/{guest}/qr',
            [
                GuestController::class,
                'qr'
            ]
        )->name('guests.qr');


        Route::delete(
            '/invitations/{invitation}/guests/{guest}',
            [
                GuestController::class,
                'destroy'
            ]
        )->name('guests.destroy');


        Route::get(
            '/invitations/{invitation}/guests-export',
            [
                GuestController::class,
                'export'
            ]
        )->name('guests.export');


        /*
        |--------------------------------------------------------------------------
        | CHECK-IN DESK
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/invitations/{invitation}/checkin',
            [
                GuestController::class,
                'checkinDesk'
            ]
        )->name('guests.checkin');


        Route::post(
            '/invitations/{invitation}/checkin/{guest}',
            [
                GuestController::class,
                'checkinManual'
            ]
        )->name(
            'guests.checkin.manual'
        );


        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/invitations/{invitation}/checkout',
            [
                OrderController::class,
                'checkout'
            ]
        )->name('orders.checkout');

        Route::post(
            '/invitations/{invitation}/orders',
            [
                OrderController::class,
                'store'
            ]
        )->name('orders.store');
    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'admin'
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/',
            [
                AdminController::class,
                'index'
            ]
        )->name('index');


        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/orders/{order}/verify',
            [
                AdminController::class,
                'verify'
            ]
        )->name(
            'orders.verify'
        );

        Route::patch(
            '/orders/{order}',
            [
                AdminController::class,
                'updateOrder'
            ]
        )->name(
            'orders.update'
        );

        Route::post(
            '/orders/{order}/reject',
            [
                AdminController::class,
                'reject'
            ]
        )->name(
            'orders.reject'
        );

        Route::patch(
            '/themes/{theme}',
            [
                AdminController::class,
                'updateTheme'
            ]
        )->name('themes.update');


        /*
        |--------------------------------------------------------------------------
        | PHOTOS
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/photos/{photo}/{status}',
            [
                AdminController::class,
                'photo'
            ]
        )->name(
            'photos.moderate'
        );
    });
