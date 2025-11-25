<?php

namespace App\Http\Controllers;

use App\Services\AdminComplaintService;
use App\Services\ExportReportsService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComplaintsExport;



class AdminComplaintController extends Controller
{
    use ApiResponse;

    protected AdminComplaintService $complaintService;
    protected ExportReportsService $exportService;
   public function __construct(AdminComplaintService $complaintService , ExportReportsService $exportService)
{
    $this->complaintService = $complaintService;
    //$this->middleware(['auth:sanctum', 'role:super_admin']);
    $this->exportService = $exportService;   
}

     

    /**
     * List all complaints with full user, attachments, and government entity
     */
    public function index()
    {
        $complaints = $this->complaintService->listAllComplaints();
        return $this->success('Fetched all complaints successfully.', $complaints);
    }

    /**
     * Update status of a complaint
     */
    public function updateStatus(Request $request, int $complaintId)
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,completed,rejected',
            'notes' => 'nullable|string',
        ]);

        $complaint = $this->complaintService->updateComplaintStatus(
            $complaintId,
            $request->status,
            $request->notes
        );

        return $this->success('Complaint status updated successfully.', $complaint);
    }

    /**
     * Add notes to a complaint
     */
    public function addNotes(Request $request, int $complaintId)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $complaint = $this->complaintService->addComplaintNotes(
            $complaintId,
            $request->notes
        );

        return $this->success('Notes added successfully.', $complaint);
    }

    /**
     * Delete a complaint
     */
    public function destroy(int $complaintId)
    {
        $result = $this->complaintService->deleteComplaint($complaintId);
        return $this->success('Complaint deleted successfully.', $result);
    }

    public function listEmployees()
{
    $employees = $this->complaintService->listAllEmployees();
    return $this->success('Fetched all employees successfully.', $employees);
}

/**
 * Fetch all audit logs with details for all complaints.
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
/**
 * Fetch all complaints with audit logs and details.
 */
public function listAllComplaintLogs()
{
    $complaints = $this->complaintService->listAllComplaintLogs();

    return $this->success(
        'Fetched all complaints with audit logs successfully.',
        $complaints
    );
}

/**
 * Return complaints statistics for admin dashboard
 */
public function statistics()
{
    // Delegate the computation to service layer
    $stats = $this->complaintService->getStatistics();

    // Return a clean JSON response
    return $this->success('Fetched complaints statistics successfully.', $stats);
}



  // CSV export
 public function monthlyCsv(Request $request)
{
    $month = $request->month ?? now()->format('Y-m');
    $complaints = $this->exportService->getMonthlyComplaints($month);

    $fileName = "complaints_{$month}.csv";
    $fileUrl = $this->exportService->exportCsv($complaints, $fileName);

    return ApiResponse::success(
        "تم إنشاء تقرير CSV بنجاح",
        ['url' => $fileUrl]
    );
}



public function monthlyPdf(Request $request)
{
    // إذا ما وصل month → ناخد الشهر الحالي
    $month = $request->month ?? now()->format('Y-m');

    // الحصول على البيانات
    $complaints = $this->exportService->getMonthlyComplaints($month);

    // توليد مسار الملف
    $filePath = $this->exportService->exportPdf($complaints, $month);

    // إرسال رابط التحميل (API Friendly)
    return response()->json([
        'success' => true,
        'url' => asset($filePath)
    ]);
}

}