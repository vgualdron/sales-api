<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
                        AuthController,
                        CategoryController,
                        RoleController,
                        PermissionController,
                        UserController,
                        FileController,
                        ConfigurationController,
                        ReportController,
                    };

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function (Request $request) {
    return 'Health...';
});

Route::get('/download-image-from-url', [FileController::class, 'downloadImageFromUrl'])->name('file.downloadImageFromUrl');

Route::group(["prefix" => "/auth"], function () {
    Route::get('/get-active-token', [AuthController::class, 'getActiveToken'])->name('auth.getActiveToken');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/create', [NovelController::class, 'create'])->name('new.create');
    Route::middleware(['middleware' => 'auth:api'])->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/session"], function () {
    Route::get('/status', function (Request $request) {
        return 'OK';
    });
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/role"], function () {
    Route::get('/list', [RoleController::class, 'list'])->middleware('can:role.list')->name('role.list');
    Route::post('/create', [RoleController::class, 'create'])->middleware('can:role.create')->name('role.create');
    Route::put('/update/{id}', [RoleController::class, 'update'])->middleware('can:role.update')->name('role.update');
    Route::delete('/delete/{id}', [RoleController::class, 'delete'])->middleware('can:role.delete')->name('role.delete');
    Route::get('/get/{id}', [RoleController::class, 'get'])->middleware('can:role.get')->name('role.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/permission"], function () {
    Route::get('/list', [PermissionController::class, 'list'])->name('permission.list');
});

Route::get('/user/get/{id}', [UserController::class, 'get'])->name('user.get');
Route::group(['middleware' => 'auth:api' , "prefix" => "/user"], function () {
    Route::get('/list/{displayAll}', [UserController::class, 'list'])->name('user.list');
    Route::get('/list-by-role-name/{displayAll}/{name}/{city}', [UserController::class, 'listByRoleName'])->name('user.listByRoleName');
    Route::get('/list-by-area/{area}', [UserController::class, 'listByArea'])->name('user.listByArea');
    Route::post('/create', [UserController::class, 'create'])->middleware('can:user.create')->name('user.create');
    Route::put('/update/{id}', [UserController::class, 'update'])->middleware('can:user.update')->name('user.update');
    Route::delete('/delete/{id}', [UserController::class, 'delete'])->middleware('can:user.delete')->name('user.delete');
    Route::put('/update-profile/{id}', [UserController::class, 'updateProfile'])->name('user.updateProfile');
    Route::put('/update-push-token', [UserController::class, 'updatePushToken'])->name('user.updatePushToken');
    Route::put('/update-location', [UserController::class, 'updateLocation'])->name('user.updateLocation');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/file"], function () {
    Route::post('/create', [FileController::class, 'create'])->name('file.create');
    Route::delete('/delete/{id}', [FileController::class, 'delete'])->name('file.delete');
    Route::post('/get', [FileController::class, 'get'])->name('file.get');
    Route::put('/update/{id}', [FileController::class, 'update'])->name('file.update');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/configuration"], function () {
    Route::get('/', [ConfigurationController::class, 'index'])->name('parameter.list');
    Route::get('/{id}', [ConfigurationController::class, 'show'])->middleware('can:parameter.list')->name('parameter.get');
    Route::post('/', [ConfigurationController::class, 'store'])->middleware('can:parameter.list')->name('parameter.create');
    Route::put('/{id}', [ConfigurationController::class, 'update'])->middleware('can:parameter.list')->name('parameter.update');
    Route::delete('/{id}', [ConfigurationController::class, 'destroy'])->middleware('can:parameter.list')->name('parameter.delete');
});

Route::group(['middleware' => 'auth:api', "prefix" => "/report"], function () {
    Route::get('/', [ReportController::class, 'list'])->name('report.list');
    Route::get('/{id}', [ReportController::class, 'execute'])->name('report.execute');
});

Route::group(['middleware' => 'auth:api', 'prefix'=>'/category'], function () {
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:api', 'prefix'=>'/product'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:api', 'prefix'=>'/image'], function () {
    Route::get('/product/{id}', [ImageController::class, 'index']);
    Route::get('/{id}', [ImageController::class, 'show']);
    Route::post('/', [ImageController::class, 'store']);
    Route::put('/{id}', [ImageController::class, 'update']);
    Route::delete('/{id}', [ImageController::class, 'destroy']);
});
