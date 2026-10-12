<?php
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ContentController;
use Illuminate\Support\Facades\Route;
Route::get('/', [SchoolController::class, 'home'])->name('home');
Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
Route::get('/resources', [App\Http\Controllers\ResourceController::class, 'index'])->name('resources');
Route::view('/contacts', 'contacts')->name('contacts');
Route::get('/about', fn (ContentController $controller) => $controller->page('about'))->name('about');
Route::get('/pages/{slug}', [ContentController::class, 'page'])->name('pages.show');
Route::get('/materials/{article}/download', [ContentController::class, 'download'])->name('materials.download');
Route::get('/{category}', [ContentController::class, 'index'])->whereIn('category', array_keys(config('portal.categories')))->name('content.index');
Route::get('/{category}/{article}', [ContentController::class, 'show'])->whereIn('category', array_keys(config('portal.categories')))->name('content.show');
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->middleware('throttle:portal-login')->name('login.store');
});
Route::post('/admin/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', 'can:manage-portal'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('index');
    Route::patch('/schools/{school}/publication', [Admin\SchoolController::class, 'publication'])->name('schools.publication');
    Route::resource('schools', Admin\SchoolController::class)->except('show');
    Route::resource('articles', Admin\ArticleController::class)->except('show');
    Route::resource('links', Admin\ResourceLinkController::class)->except('show');
    Route::resource('pages', Admin\PageController::class)->only(['index', 'edit', 'update']);
    Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [Admin\AccountController::class, 'update'])->middleware('throttle:5,1')->name('account.update');
});
