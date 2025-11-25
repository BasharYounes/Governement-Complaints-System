<?php

namespace App\Services;

use App\Exports\ComplaintsExport;
use App\Models\Complaint;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Storage;
use Maatwebsite\Excel\Excel as ExcelType;

class ExportReportsService
{
    public function getMonthlyComplaints($month = null)
    {
        $month = $month ?? now()->format('Y-m');

        return Complaint::whereMonth('created_at', date('m', strtotime($month)))
                                    ->whereYear('created_at', date('Y', strtotime($month)))
                                    ->get();
    }

    
    public function exportCsv($complaints, $filename)
    {
     $filePath = "tmp/{$filename}";

    Excel::store(new ComplaintsExport($complaints), $filePath, 'public', ExcelType::CSV, [
        'bom' => true,           // مهم لإظهار العربي في Excel
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => "\r\n",
    ]);

    $url = url(Storage::url($filePath));

    return $url;   
     }

     
    // public function exportExcel($complaints, $filename)
    // {
    //     return Excel::download(new ComplaintsExport($complaints), $filename, \Maatwebsite\Excel\Excel::XLSX);
    // }

   public function exportPdf($complaints, $month)
    {
        // تجهيز HTML
        $html = view('exports.complaints_pdf', [
            'complaints' => $complaints,
            'month' => $month
        ])->render();

        $mpdf = new Mpdf(['default_font' => 'dejavusans']);

        $mpdf->WriteHTML($html);

        $fileName = "complaints_{$month}.pdf";
        $filePath = "storage/reports/" . $fileName;

        $mpdf->Output(storage_path('app/public/reports/' . $fileName), 'F');

        return $filePath;
    }
}
