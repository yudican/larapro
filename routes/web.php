<?php

use App\Http\Controllers\AuthController;
use App\Http\Livewire\CrudGenerator;
use App\Http\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::post('login', [AuthController::class, 'login'])->name('admin.login');
Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
    // Crud Generator Route
    Route::get('/crud-generator', CrudGenerator::class)->name('crud.generator');

    // App Route
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
