<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Messaging\CloudMessage;

Route::get('/', function () {
    // return view('welcome');
            $messaging = app('firebase.messaging');
            $message = CloudMessage::withTarget('token','fP6LrX5CRs-9hBsbKhGF1u:APA91bGaHyVsWNQqk1fwEXujpHe4AZHoeaaNkf_3c9DfmhDtkI3YgsKbgP2_VouVUrEOg_JWHU_UQ7BQus-d00KkCNRZC1FELkG33hIRVEeC60LkJxGjUfk' )
                ->withNotification([
                'title' => 'إشعار جديد',
                'body' => 'لديك إشعار جديد'
            ]);
            $messaging->send($message);
            dd($message);
});
