<?php

use App\Http\Controllers\AdminComplaintController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmployeeComplaintController;
use App\Http\Controllers\GovernmentEntitiesController;
use App\Http\Controllers\NotificationController;
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
Route::post('/register',[AuthController::class,'RegisterUser']);
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1');
Route::post('/verify-code',[AuthController::class,'VerifyCode']);
Route::post('/resend-code',[AuthController::class,'ResendCode'])->middleware('throttle:3,10');
Route::post('/forget-password',[ForgetPasswordController::class,'forgotPassword']);
Route::post('/check-code',[ForgetPasswordController::class,'checkCode']);


// register "Admin"
Route::post('/registerAdmin',[AuthController::class,'registerAdmin']);
Route::post('/loginAdmin',[AuthController::class,'loginAdmin']);

// register "employee"
Route::post('/loginEmployee',[AuthController::class,'loginEmployee']);

Route::middleware(['AuthenticateUser'])->group(function () {
    Route::post('/reset-password',[ForgetPasswordController::class,'resetPassword']);

    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::post('/edit-profile',  [AuthController::class, 'EditInformation']);

    Route::post('/store-fcm-token', [AuthController::class, 'storeFCM_Token']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/show-notification/{id}', [NotificationController::class, 'show']);
    Route::post('/mark-is-read', [NotificationController::class,'markAsRead']);

    Route::prefix('complaints')->group(function () {
        Route::post('create', [ComplaintController::class, 'create']);
        Route::get('show/{id}', [ComplaintController::class, 'show']);
        Route::post('update/{id}', [ComplaintController::class, 'update']);
        Route::delete('delete/{id}', [ComplaintController::class, 'destroy']);
        Route::post('add-attachment/{id}', [ComplaintController::class, 'addAttachment']);
        Route::get('get-user-complaints', [ComplaintController::class, 'getComplaintsforUser']);
    });

    Route::prefix('government-entities')->group(function () {
        Route::get('/all-entities', [GovernmentEntitiesController::class, 'index']);
    });

    Route::prefix('attachments')->group(function () {
        Route::get('/show/{id}', [AttachmentController::class, 'show']);
    });

});

    ///=========================
    // EMPLOYEE COMPLAINT ROUTES
    //==========================
    Route::middleware(['AuthenticateEmployee','role:employee'])->prefix('employee')->group(function () {
    Route::get('/complaints', [EmployeeComplaintController::class, 'index'])->middleware('permission:view-complaint');
    Route::post('/update-complaints/{complaintId}', [EmployeeComplaintController::class, 'updateStatus'])->middleware('permission:update-complaint');
    Route::post('check-editing/{complaintId}',[ComplaintController::class,'edit']);
    Route::post('complaints/{complaintId}/request-information', [EmployeeComplaintController::class, 'RequestAdditionalInformation'])->middleware('permission:RequestAdditionalInformation');
    Route::get('logout', [AuthController::class, 'logoutEmployee'])->middleware('permission:logout-employee');
    Route::get('/all-complaints', [EmployeeComplaintController::class, 'getAllComplaint']);
    Route::get('/show-complaint/{complaintId}', [EmployeeComplaintController::class, 'show'])->middleware('permission:view-complaint');
});
    ///======================
    // ADMIN COMPLAINT ROUTES
    //=======================
Route::middleware(['AuthenticateAdmin','role:super_admin'])->prefix('admin')->group(function () {
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

    Route::get('/reports/monthly/pdf', [AdminComplaintController::class, 'monthlyPdf'])
        ->middleware('permission:export-monthly-pdf');

    Route::get('logout', [AuthController::class, 'logoutAdmin']);

    Route::post('/registerEmployee',[AuthController::class,'registerEmployee'])
         ->middleware(['auth:sanctum', 'role:super_admin|permission:manage-users']);

    Route::get('/government-entities', [GovernmentEntitiesController::class, 'index']);

    // Route::get('/reports/monthly/csv', [AdminComplaintController::class, 'monthlyCsv']);
    // Route::get('/reports/monthly/pdf', [AdminComplaintController::class, 'monthlyPdf']);

    Route::post('/search-complaints',[AdminComplaintController::class,'searchComplaints'])
         ->middleware('permission:view-employees' );

    Route::post('/search-employee',[AdminComplaintController::class,'searchEmployees'])
        ->middleware('permission:manage-users');

});

