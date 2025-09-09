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

    /* index page */
    Route::get("/folders/{id}/tasks", [TaskController::class,"index"])->name("tasks.index");

    /* folders new create page */
    Route::get('/folders/create', [FolderController::class,"showCreateForm"])->name('folders.create');
    Route::post('/folders/create', [FolderController::class,"create"]);

    /* folders new edit page */
    Route::get('/folders/{id}/edit', [FolderController::class,"showEditForm"])->name('folders.edit');
    Route::post('/folders/{id}/edit', [FolderController::class,"edit"]);

    /* folders new delete page */
    Route::get('/folders/{id}/delete', [FolderController::class,"showDeleteForm"])->name('folders.delete');
    Route::post('/folders/{id}/delete', [FolderController::class,"delete"]);

    /* tasks new create page */
    Route::get('/folders/{id}/tasks/create', [TaskController::class,"showCreateForm"])->name('tasks.create');
    Route::post('/folders/{id}/tasks/create', [TaskController::class,"create"]);

    /* tasks new edit page */
    Route::get('/folders/{id}/tasks/{task_id}/edit', [TaskController::class,"showEditForm"])->name('tasks.edit');
    Route::post('/folders/{id}/tasks/{task_id}/edit', [TaskController::class,"edit"]);

    /* tasks new delete page */
    Route::get('/folders/{id}/tasks/{task_id}/delete', [TaskController::class,"showDeleteForm"])->name('tasks.delete');
    Route::post('/folders/{id}/tasks/{task_id}/delete', [TaskController::class,"delete"]);
});

/* certification page （会員登録・ログイン・ログアウト・パスワード再設定など） */
Auth::routes();
