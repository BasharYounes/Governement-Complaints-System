<?php

namespace App\Http\Controllers;

use App\Http\Requests\Complaints\ExportComplaintsRequest;
use App\Repositories\Complaints\ComplaintRepository;
use App\Services\AdminComplaintService;
use App\Services\EmployeeComplaintService;
use App\Services\ExportReportsService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;


class AdminComplaintController extends Controller
{
    protected AdminComplaintService $complaintService;
    protected ExportReportsService $exportService;
    protected EmployeeComplaintService $employeeComplaintService;

   public function __construct(AdminComplaintService $complaintService ,
    ExportReportsService $exportService,
    EmployeeComplaintService $employeeComplaintService,
    protected ComplaintRepository $complaintRepository
    )
    {
        $this->complaintService = $complaintService;
        $this->employeeComplaintService = $employeeComplaintService;

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
     * Delete a complaint
     */
    public function destroy(int $complaintId)
    {
        $result = $this->complaintService->deleteComplaint($complaintId);
        return $this->success('Complaint deleted successfully.', $result);
    }

    public function listUsers()
    {
        $employees = $this->complaintService->listAllEmployees()->paginate(10);
        $users = $this->complaintService->getAllUsers()->paginate(10);
        return $this->success('Fetched all employees successfully.', [
            'Employees' => $employees,
            'Users' => $users
        ]);
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
        return $this->success(
            'Fetched all complaints with audit logs successfully.',
            $this->complaintRepository->getallComplaintsWithLogsDetails(),
            200
        );
    }

    public function complaintAuditLogs($complaintId)
    {
        $complaintAuditLogs = $this->complaintService->complaintAuditLogs($complaintId);
        return $this->success(
            'Fetched complaints audit logs successfully.',
            $complaintAuditLogs,
            200
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
        return $this->success(
            'Fetched complaints statistics successfully.',
            $stats,
            200
        );
    }




    public function monthlyCsv(ExportComplaintsRequest $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $month = $request->month ?? now()->format('Y-m');

        if ($fromDate && $toDate) {
            $complaints = $this->exportService->getComplaintsByDateRange($fromDate, $toDate);
            $fileName = "complaints_" . str_replace('-', '_', $fromDate) . "_to_" . str_replace('-', '_', $toDate) . ".csv";
        } else {
            $complaints = $this->exportService->getMonthlyComplaints($month);
            $fileName = "complaints_{$month}.csv";
        }

        $fileUrl = $this->exportService->exportCsv($complaints, $fileName);

        return ApiResponse::success(
            "تم إنشاء تقرير CSV بنجاح",
            ['url' => $fileUrl]
        );
    }



    public function monthlyPdf(ExportComplaintsRequest $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $month = $request->month ?? now()->format('Y-m');

        if ($fromDate && $toDate) {
            $complaints = $this->exportService->getComplaintsByDateRange($fromDate, $toDate);
            $dateRange = $fromDate . " to " . $toDate;
        } else {
            $complaints = $this->exportService->getMonthlyComplaints($month);
            $dateRange = $month;
        }

        $filePath = $this->exportService->exportPdf($complaints, $dateRange);

        return response()->json([
            'success' => true,
            'url' => asset($filePath)
        ]);
    }

    public function searchComplaints(Request $request)
    {
        $keyword = $request->input('keyword')??'';
        $complaints =$this->complaintService->searchComplaints($keyword);
        return $this->success('Fetched Search Results successfully.',$complaints);
    }

    public function searchEmployees(Request $request)
    {
        $keyword = $request->input('keyword') ?? '';
        $employees = $this->complaintService->searchEmployees($keyword);

        return $this->success('Fetched Employee Results successfully', $employees);
    }

}
