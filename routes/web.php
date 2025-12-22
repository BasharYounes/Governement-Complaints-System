<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Messaging\CloudMessage;

// Route::get('/', function () {
//     // return view('welcome');
//             $messaging = app('firebase.messaging');
//             $message = CloudMessage::withTarget('token','fP6LrX5CRs-9hBsbKhGF1u:APA91bGaHyVsWNQqk1fwEXujpHe4AZHoeaaNkf_3c9DfmhDtkI3YgsKbgP2_VouVUrEOg_JWHU_UQ7BQus-d00KkCNRZC1FELkG33hIRVEeC60LkJxGjUfk' )
//                 ->withNotification([
//                 'title' => 'إشعار جديد',
//                 'body' => 'لديك إشعار جديد'
//             ]);
//             $messaging->send($message);
//             dd($message);
// });
Route::prefix('attachments')->group(function () {
            Route::get('/show/{id}', [AttachmentController::class, 'show']);
});


Route::get('/test-server', function() {
    return response()->json([
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'N/A',
        'server_name' => gethostname(),
        'timestamp' => now(),
        'request_ip' => request()->ip()
    ]);
});

Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        return [
            'status' => 'success',
            'message' => '✅ Database connected successfully!',
            'database' => DB::connection()->getDatabaseName(),
            'server' => $_SERVER['SERVER_PORT']
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => '❌ Database connection failed',
            'error' => $e->getMessage()
        ];
    }
});
