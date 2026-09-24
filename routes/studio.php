<?php

use App\Http\Controllers\Admin\StudioController;
use App\Http\Controllers\StudioCustomerController;
use App\Http\Controllers\StudioInvitationController;
use App\Http\Controllers\StudioPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin/studio')
    ->name('admin.studio.')
    ->group(function () {
        Route::get('/', [StudioController::class, 'index'])->name('index');
        Route::post('/templates', [StudioController::class, 'create'])->name('create');
        Route::get('/templates/{template}', [StudioController::class, 'edit'])->name('edit');
        Route::put('/templates/{template}', [StudioController::class, 'update'])->name('update');
        Route::post('/templates/{template}/duplicate', [StudioController::class, 'duplicate'])->name('duplicate');
        Route::post('/templates/{template}/publish', [StudioController::class, 'togglePublish'])->name('publish');
        Route::post('/templates/{template}/preview-instance', [StudioController::class, 'ensurePreviewInstance'])->name('preview-instance.ensure');
        Route::post('/templates/{template}/preview-instance/reset', [StudioController::class, 'resetPreviewInstance'])->name('preview-instance.reset');
        Route::put('/instances/{instance}', [StudioController::class, 'updatePreviewInstance'])->name('instances.update');
        Route::delete('/templates/{template}', [StudioController::class, 'destroy'])->name('destroy');
        Route::post('/templates/{template}/assets', [StudioController::class, 'uploadAsset'])->name('assets.upload');
        Route::get('/assets/{asset}/file', [StudioController::class, 'assetFile'])->name('assets.file');
        Route::delete('/assets/{asset}', [StudioController::class, 'deleteAsset'])->name('assets.destroy');
        Route::post('/fonts', [StudioController::class, 'uploadFont'])->name('fonts.upload');
        Route::get('/fonts/{font}/file', [StudioController::class, 'fontFile'])->name('fonts.file');
        Route::delete('/fonts/{font}', [StudioController::class, 'deleteFont'])->name('fonts.destroy');
    });

Route::middleware(['auth'])
    ->prefix('studio/customer')
    ->name('studio.customer.')
    ->group(function () {
        Route::get('/instances/{instance}', [StudioCustomerController::class, 'edit'])->name('edit');
        Route::put('/instances/{instance}/quick', [StudioCustomerController::class, 'quickUpdate'])->name('quick.update');
        Route::put('/instances/{instance}/full', [StudioCustomerController::class, 'fullUpdate'])->name('full.update');
        Route::post('/instances/{instance}/media', [StudioCustomerController::class, 'mediaUpload'])->name('media.upload');
        Route::post('/instances/{instance}/assets', [StudioCustomerController::class, 'assetUpload'])->name('assets.upload');
        Route::post('/instances/{instance}/fonts', [StudioCustomerController::class, 'fontUpload'])->name('fonts.upload');
    });

Route::get('/i/{token}', [StudioPublicController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('studio.public.show');

Route::post('/i/{token}/rsvp', [StudioPublicController::class, 'rsvp'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('studio.public.rsvp');

Route::post('/i/{token}/photo', [StudioPublicController::class, 'photo'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('studio.public.photo');

Route::get('/i/{token}/assets/{asset}/file', [StudioPublicController::class, 'asset'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('studio.public.asset');

Route::get('/i/{token}/fonts/{font}/file', [StudioPublicController::class, 'font'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->name('studio.public.font');

Route::get('/i/{token}/media/{key}', [StudioPublicController::class, 'media'])
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->where('key', '[A-Za-z0-9_]+')
    ->name('studio.public.media');

Route::middleware(['auth'])
    ->prefix('invitations/{invitation}/studio')
    ->name('studio.invitation.')
    ->group(function () {
        Route::get('/', [StudioInvitationController::class, 'manage'])->name('manage');
        Route::post('/templates/{template}', [StudioInvitationController::class, 'attach'])->name('attach');
        Route::get('/open', [StudioInvitationController::class, 'open'])->name('open');
        Route::get('/preview', [StudioInvitationController::class, 'preview'])->name('preview');
        Route::delete('/', [StudioInvitationController::class, 'detach'])->name('detach');
    });
