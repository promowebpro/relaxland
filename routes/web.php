<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');

Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contacts', [PublicPageController::class, 'contacts'])->name('contacts');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/privacy', [LegalDocumentController::class, 'index'])->name('legal.index');
Route::get('/privacy/{slug}', [LegalDocumentController::class, 'show'])->name('legal.show');
Route::get('/thanks', [PublicPageController::class, 'success'])->name('success');
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:lead-submissions')
    ->name('leads.store');
