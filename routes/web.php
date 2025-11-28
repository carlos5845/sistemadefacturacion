<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

    // Companies
    Route::resource('companies', CompanyController::class)
        ->middleware('can:viewAny,App\Models\Company');

    // Customers
    Route::resource('customers', CustomerController::class)
        ->middleware('can:viewAny,App\Models\Customer');

    // Products
    Route::resource('products', ProductController::class)
        ->middleware('can:viewAny,App\Models\Product');

    // Product Categories
    Route::post('product-categories', [\App\Http\Controllers\ProductCategoryController::class, 'store'])
        ->name('product-categories.store');

        // Documents
        Route::resource('documents', DocumentController::class)
            ->middleware('can:viewAny,App\Models\Document');

        Route::post('documents/{document}/send-to-sunat', [DocumentController::class, 'sendToSunat'])
            ->name('documents.send-to-sunat');

        // XML download and view routes
        Route::get('documents/{document}/xml/download', [DocumentController::class, 'downloadXml'])
            ->name('documents.xml.download');
        Route::get('documents/{document}/xml-signed/download', [DocumentController::class, 'downloadXmlSigned'])
            ->name('documents.xml-signed.download');
        Route::get('documents/{document}/xml/view', [DocumentController::class, 'viewXml'])
            ->name('documents.xml.view');
        Route::get('documents/{document}/xml-signed/view', [DocumentController::class, 'viewXmlSigned'])
            ->name('documents.xml-signed.view');
});

require __DIR__ . '/settings.php';
