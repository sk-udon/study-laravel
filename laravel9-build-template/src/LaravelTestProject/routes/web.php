<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

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
    return view('welcome');
});

Route::group(['middleware' => 'auth'], function() {
    /* home page */
    Route::get('/', [HomeController::class,"index"])->name('home');

    /* folders new create page */
    Route::get('/folders/create', [FolderController::class,"showCreateForm"])->name('folders.create');
    Route::post('/folders/create', [FolderController::class,"create"]);

    Route::group(['middleware' => 'can:view,folder'], function() {
        /* index page */
        Route::get("/folders/{folder}/tasks", [TaskController::class,"index"])->name("tasks.index");

        /* folders new edit page */
        Route::get('/folders/{folder}/edit', [FolderController::class,"showEditForm"])->name('folders.edit');
        Route::post('/folders/{folder}/edit', [FolderController::class,"edit"]);

        /* folders new delete page */
        Route::get('/folders/{folder}/delete', [FolderController::class,"showDeleteForm"])->name('folders.delete');
        Route::post('/folders/{folder}/delete', [FolderController::class,"delete"]);

        /* tasks new create page */
        Route::get('/folders/{folder}/tasks/create', [TaskController::class,"showCreateForm"])->name('tasks.create');
        Route::post('/folders/{folder}/tasks/create', [TaskController::class,"create"]);

        /* tasks new edit page */
        Route::get('/folders/{folder}/tasks/{task}/edit', [TaskController::class,"showEditForm"])->name('tasks.edit');
        Route::post('/folders/{folder}/tasks/{task}/edit', [TaskController::class,"edit"]);

        /* tasks new delete page */
        Route::get('/folders/{folder}/tasks/{task}/delete', [TaskController::class,"showDeleteForm"])->name('tasks.delete');
        Route::post('/folders/{folder}/tasks/{task}/delete', [TaskController::class,"delete"]);
    });
});
/* certification page （会員登録・ログイン・ログアウト・パスワード再設定など） */
Auth::routes();
