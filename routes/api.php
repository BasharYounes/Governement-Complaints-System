<?php



use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\GovernmentEntitiesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgetPasswordController;



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



Route::post('/register',[AuthController::class,'Register']);
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1');
Route::post('/verify-code',[AuthController::class,'VerifyCode']);
Route::post('/resend-code',[AuthController::class,'ResendCode'])->middleware('throttle:3,10');
Route::post('/forget-password',[ForgetPasswordController::class,'forgotPassword']);
Route::post('/check-code',[ForgetPasswordController::class,'checkCode']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/reset-password',[ForgetPasswordController::class,'resetPassword']);

        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout',        [AuthController::class, 'logout']);
        Route::post('/edit-profile',       [AuthController::class, 'EditInformation']);

        Route::prefix('complaints')->group(function () {
            Route::post('create', [ComplaintController::class, 'create']);
            Route::get('show/{id}', [ComplaintController::class, 'show']);
            Route::post('update/{id}', [ComplaintController::class, 'update']);
            Route::delete('delete/{id}', [ComplaintController::class, 'destroy']);
            Route::post('add-attachment', [ComplaintController::class, 'addAttachment']);
            Route::get('get-user-complaints', [ComplaintController::class, 'getComplaintsforUser']);
        });

        Route::prefix('government-entities')->group(function () {
            Route::get('/all-entities', [GovernmentEntitiesController::class, 'index']);
        });



    });


