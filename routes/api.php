<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RssController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (readable by Nuxt during SSR with no auth)
|--------------------------------------------------------------------------
*/

Route::get('/test', function(){
    dd("asa");
});


Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{slug}', [TagController::class, 'show']);
Route::get('/search', SearchController::class);
Route::get('/feed', RssController::class);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated routes (bearer token via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/uploads', [UploadController::class, 'store']);

    /*
    |----------------------------------------------------------------------
    | Author/Admin: content management ("manage" avoids clashing with the
    | public GET /posts/{slug} route above).
    |----------------------------------------------------------------------
    */
    Route::get('/manage/posts', [PostController::class, 'manage']);
    Route::post('/manage/posts', [PostController::class, 'store']);
    Route::get('/manage/posts/{post}', [PostController::class, 'edit']);
    Route::put('/manage/posts/{post}', [PostController::class, 'update']);
    Route::delete('/manage/posts/{post}', [PostController::class, 'destroy']);

    Route::post('/tags', [TagController::class, 'store']);

    /*
    |----------------------------------------------------------------------
    | Admin only
    |----------------------------------------------------------------------
    */
    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

        Route::get('/manage/comments/pending', [CommentController::class, 'pending']);
        Route::patch('/manage/comments/{comment}', [CommentController::class, 'moderate']);
    });
});
