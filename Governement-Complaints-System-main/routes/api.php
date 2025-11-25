<?php



use App\Http\Controllers\AdminComplaintController;
use App\Http\Controllers\EmployeeComplaintController;
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


// register User "Citizen"
Route::post('/registerUser',[AuthController::class,'RegisterUser']);
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1');
Route::post('/verify-code',[AuthController::class,'VerifyCode']);
Route::post('/resend-code',[AuthController::class,'ResendCode'])->middleware('throttle:3,10');
Route::post('/forget-password',[ForgetPasswordController::class,'forgotPassword']);
Route::post('/check-code',[ForgetPasswordController::class,'checkCode']);
// register "Admin"
Route::post('/registerAdmin',[AuthController::class,'RegisterAdmin']);
// register "employee"
Route::post('/registerEmployee',[AuthController::class,'RegisterEmployee'])->middleware(['auth:sanctum', 'role:super_admin|permission:manage-users']);;


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/reset-password',[ForgetPasswordController::class,'resetPassword']);

        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout',        [AuthController::class, 'logout']);

    });

    ///=========================
    // EMPLOYEE COMPLAINT ROUTES
    //==========================
    Route::middleware(['auth:sanctum', 'role:employee'])->prefix('employee')->group(function () {
    Route::get('/complaints', [EmployeeComplaintController::class, 'index'])->middleware('permission:view-complaint');
    Route::patch('/complaints/{complaintId}/status', [EmployeeComplaintController::class, 'updateStatus'])->middleware('permission:update-complaint');
    Route::post('/complaints/{complaintId}/notes', [EmployeeComplaintController::class, 'addNotes'])->middleware('permission:add-complaint-notes');
});
    ///======================
    // ADMIN COMPLAINT ROUTES
    //=======================
Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/complaints', [AdminComplaintController::class, 'index'])
        ->middleware('permission:view-all-complaints');
    
    Route::get('/employees', [AdminComplaintController::class, 'listEmployees'])
        ->middleware('permission:view-employees');
    
    Route::get('/complaints/{complaintId}/audit-logs', [AdminComplaintController::class, 'complaintAuditLogs'])
        ->middleware('permission:view-complaint-audit-logs');
    
    Route::get('/statistics', [AdminComplaintController::class, 'statistics'])
        ->middleware('permission:view-statistics');
    
    Route::get('/complaint-logs', [AdminComplaintController::class, 'listAllComplaintLogs'])
        ->middleware('permission:view-all-complaint-logs');
        Route::get('/reports/monthly/csv', [AdminComplaintController::class, 'monthlyCsv'])
            ->middleware('permission:export-monthly-csv');
        Route::get('/reports/monthly/pdf1', [AdminComplaintController::class, 'monthlyPdf'])
            ->middleware('permission:export-monthly-pdf');
});

 Route::get('/reports/monthly/csv', [AdminComplaintController::class, 'monthlyCsv']);
  Route::get('/reports/monthly/pdf', [AdminComplaintController::class, 'monthlyPdf']);