<?php

return [
    'templates' => [
        'complaint_status_changed' => [
            'title' => 'تم تحديث حالة الشكوى {{reference_number}}',
            'body' => 'تم تغيير حالة شكواك إلى "{{new_status}}". يرجى تسجيل الدخول للتحقق من التفاصيل.'
        ],
        'RequestAdditionalInformation' =>[
            'title' => 'طلب معلومات حول شكوى',
            'body' => 'نرجو منك تزويدنا بمعلومات إضافية عن الشكوى التي الرقم المرجعي لها.{{reference_number}}'
        ],
        'updateByUser' => [
            'title' => 'تعديل شكوى',
            'body' => 'تم تعديل الشكوى بنجاح'
        ],
        'account_locked' => [
            'title' => 'تم إيقاف حسابك',
            'body' => 'عزيزي {{name}}، تم إيقاف حسابك بسبب {{attempts}} محاولات فاشلة لتسجيل الدخول. يرجى التواصل مع الدعم الفني لإعادة تفعيل الحساب.'
        ]
    ]
];
