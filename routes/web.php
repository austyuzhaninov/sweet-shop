<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Logging\Telegram\Exceptions\TelegramBotApiException;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', HomeController::class)->name('home');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('login');           // Форма входа
    Route::post('/login', 'signIn')->name('signIn');        // Роут авторизации пользователя

    Route::get('/sign-up', 'signUp')->name('signUp');       // Форма регистрации
    Route::post('/sign-up', 'store')->name('store');        // Создание пользователя

    Route::delete('/logout', 'logOut')->name('logOut');     // Выход

    // Восстановление пароля
    Route::get('/forgot-password', 'forgot')->middleware('guest')->name('password.request');
    Route::post('/forgot-password', 'forgotPassword')->middleware('guest')->name('password.email');

    // Восстановление пароля (проход по токену)
    Route::get('/reset-password/{token}', 'reset')->middleware('guest')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->middleware('guest')->name('password.update');

    // Вход по GitHub (Socialite)
    Route::get('/auth/socialite/github', 'github')->name('socialite.github');
    Route::get('/auth/socialite/github/callback', 'githubCallback')->name('socialite.github.callback');

});

Route::get('/test', function () {
    dd(route('store'));
});
