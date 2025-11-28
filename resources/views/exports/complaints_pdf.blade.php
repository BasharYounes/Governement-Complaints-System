<!doctype html>
<html lang="ar">
<head>
    <meta charset="utf-8">
    <style>
        body { direction: rtl; font-family: sans-serif; }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: right; font-size: 12px; }
        th { background: #f4f4f4; }
        .header { text-align: center; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>تقرير الشكاوى الشهري</h2>
        <div>الشهر: {{ $month }}</div>
        <div>عدد الشكاوى: {{ $complaints->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>الرقم المرجعي</th>
                <th>الوصف</th>
                <th>الحالة</th>
                <th>الجهة</th>
                <th>المقدم</th>
                <th>تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            @foreach($complaints as $c)
                <tr>
                    <td>{{ $c->reference_number }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($c->description, 80) }}</td>
                    <td>{{ $c->status }}</td>
                    <td>{{ $c->governmentEntity->name ?? '—' }}</td>
                    <td>{{ $c->user->name ?? '—' }}</td>
                    <td>{{ optional($c->created_at)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
