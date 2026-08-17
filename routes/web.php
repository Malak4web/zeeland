<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\LandingController;
use App\Http\Controllers\Site\LeadIntakeController;
use App\Http\Controllers\Site\SeoController;
use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------------
| Public
|----------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/feed.xml', [SeoController::class, 'feed'])->name('feed');

// The landing page form posts here. Throttled because it is unauthenticated.
Route::post('/api/leads', [LeadIntakeController::class, 'store'])
    ->middleware('throttle:12,1')
    ->name('api.leads.store');

// Browsers probe this whatever the <link> tags say; answering keeps the 404s
// out of the log without shipping a second icon file.
Route::get('/favicon.ico', fn () => redirect(asset('favicon.svg'), 301));

/*
|----------------------------------------------------------------------------
| Dashboard
|----------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Outside the auth group on purpose: Chrome fetches a manifest without
    // credentials, so behind auth it would receive the login page and fail to
    // parse. It holds no data worth protecting.
    Route::get('manifest.webmanifest', [DashboardController::class, 'manifest'])->name('manifest');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'show'])->name('login');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.attempt');
    });

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('admin')
        ->name('logout');

    Route::middleware('admin')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        /* -------------------------------------------------------- leads */
        Route::middleware('can_:leads.view')->group(function () {
            Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
            Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        });
        Route::middleware('can_:leads.edit')->group(function () {
            Route::patch('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
            Route::post('leads/{lead}/notes', [LeadController::class, 'storeNote'])->name('leads.notes.store');
            Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
            Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        });

        /* ---------------------------------------------------- customers */
        // `create` is registered before `{customer}` on purpose — otherwise the
        // wildcard swallows /customers/create and model binding 404s.
        Route::middleware('can_:customers.edit')->group(function () {
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        });
        Route::middleware('can_:customers.view')->group(function () {
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
        });
        Route::middleware('can_:customers.edit')->group(function () {
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::patch('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        });

        /* ------------------------------------------------------- orders */
        Route::middleware('can_:orders.edit')->group(function () {
            Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        });
        Route::middleware('can_:orders.view')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        });
        Route::middleware('can_:orders.edit')->group(function () {
            Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
            Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        });

        /* ----------------------------------------------------- payments */
        Route::middleware('can_:payments.view')->group(function () {
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        });
        Route::middleware('can_:payments.edit')->group(function () {
            Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::patch('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
            Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        });

        /* ----------------------------------------------------- products */
        Route::middleware('can_:products.view')->group(function () {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
        });
        Route::middleware('can_:products.edit')->group(function () {
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });

        /* --------------------------------------------------------- blog */
        Route::middleware('can_:blog.view')->group(function () {
            Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        });
        Route::middleware('can_:blog.edit')->group(function () {
            Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
            Route::post('posts', [PostController::class, 'store'])->name('posts.store');
            Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
            Route::patch('posts/{post}', [PostController::class, 'update'])->name('posts.update');
            Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
            Route::post('posts/seo-check', [PostController::class, 'seoCheck'])->name('posts.seo');
            Route::post('media', [PostController::class, 'upload'])->name('media.store');

            Route::get('taxonomy', [TaxonomyController::class, 'index'])->name('taxonomy.index');
            Route::post('categories', [TaxonomyController::class, 'storeCategory'])->name('categories.store');
            Route::patch('categories/{category}', [TaxonomyController::class, 'updateCategory'])->name('categories.update');
            Route::delete('categories/{category}', [TaxonomyController::class, 'destroyCategory'])->name('categories.destroy');
            Route::post('tags', [TaxonomyController::class, 'storeTag'])->name('tags.store');
            Route::delete('tags/{tag}', [TaxonomyController::class, 'destroyTag'])->name('tags.destroy');
        });

        /* ---------------------------------------------------------- seo */
        Route::middleware('can_:seo.view')->group(function () {
            Route::get('redirects', [RedirectController::class, 'index'])->name('redirects.index');
        });
        Route::middleware('can_:seo.edit')->group(function () {
            Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
            Route::patch('redirects/{redirect}', [RedirectController::class, 'update'])->name('redirects.update');
            Route::delete('redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');
        });

        /* ------------------------------------------------------ reports */
        Route::middleware('can_:reports.view')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/receivables', [ReportController::class, 'receivables'])->name('reports.receivables');
        });

        /* ----------------------------------------------- admin only ---- */
        Route::middleware('can_:settings.edit')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::get('activity', [UserController::class, 'activity'])->name('activity.index');
        });
    });
});
