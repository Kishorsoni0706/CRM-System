<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\MergeContactsController;
use App\Http\Controllers\HomeController;
use App\Models\Contact;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ContactController::class, 'index']);

Route::resource('contacts', ContactController::class);

Route::resource('custom-fields', CustomFieldController::class)
    ->only(['index', 'store']);

Route::post('contacts/merge', [MergeContactsController::class, 'merge'])
    ->name('contacts.merge');

Route::get('contacts/{contact}/merge-history', function (Contact $contact) {
    return $contact->mergeLogs()->latest()->get();
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
