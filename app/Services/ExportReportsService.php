<?php

// namespace App\Services;

// use App\Exports\ComplaintsExport;
// use Barryvdh\DomPDF\Facade\Pdf; // إذا تستخدمين barryvdh/laravel-dompdf
// use Maatwebsite\Excel\Facades\Excel;

// class ExportReportsService
// {
//     public function getMonthlyComplaints($month)
//     {
//         return \App\Models\Complaint::whereMonth('created_at', date('m', strtotime($month)))
//                                     ->whereYear('created_at', date('Y', strtotime($month)))
//                                     ->get();
//     }

//     public function exportCsv($complaints, $filename)
//     {
//         return Excel::download(new ComplaintsExport($complaints), $filename);
//     }

//     public function exportPdf($complaints, $filename)
//     {
//         $pdf = Pdf::loadView('exports.complaints_pdf', [
//             'complaints' => $complaints
//         ]);
//         return $pdf->download($filename);
//     }
// }
