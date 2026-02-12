<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/invoice/{order}', InvoiceController::class)->name('invoice.show');
});
