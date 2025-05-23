<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->name('user.login');
Route::get('/projects', [ProjectController::class, 'index'])->name('project.get.all');
Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('project.find');
Route::post('/messages', [MessageController::class, 'store'])->name('message.store');
Route::get('/images', [ImageController::class,'index'])->name('images.get.all');
Route::get('images/{imageId}', [ImageController::class, 'show'])->name('images.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('category.get.all');
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('category.find');




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user'); 
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('message.unread.count');
    Route::get('/messages', [MessageController::class, 'index'])->name('message.get.all');
    Route::get('/messages/{id}', [MessageController::class, 'show'])->name('message.find');
    Route::post('/projects', [ProjectController::class, 'store'])->name('project.store');
    Route::put('/projects/{id}', [ProjectController::class,'update'])->name('project.update');
    Route::delete('/projects/{id}', [ProjectController::class,'destroy'])->name('project.destroy');
    Route::post('/projects/{id}/update-image', [ProjectController::class, 'updateImage']);
    Route::post('projects/{projectId}/bulk-upload', [ImageController::class, 'bulkUpload'])->name('image.bulk.upload');
   Route::post('projects/{projectId}/upload', [ImageController::class, 'upload'])->name('image.upload'); 
    Route::delete('/images/{imageId}', [ImageController::class, 'destroy'])->name('image.destroy');
    Route::put('images/{imageId}', [ImageController::class,'update'])->name('image.edit');
    Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('message.delete');
    Route::put('/messages/{id}/read', [MessageController::class, 'read'])->name('message.read');
    Route::put('/messages/read', [MessageController::class, 'readAll'])->name('message.read.all');
    Route::post('/categories', [CategoryController::class, 'store'])->name('category.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
});
