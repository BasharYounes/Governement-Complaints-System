<?php

namespace App\Repositories\Complaints;

use App\Models\Complaint;

class ComplaintRepository
{
    public function createComplaint(array $data,string $referenceNumber): Complaint
    {
        $data['user_id'] = auth()->id();
        $data['reference_number'] = $referenceNumber;
        return Complaint::create($data);
    }

    public function getComplaintById($id): Complaint
    {
        $complaint = Complaint::find($id);
        if (! $complaint) {
            abort(response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع',
                'errors' => 'الشكوى غير موجودة'
            ], 404));
        }

        return $complaint;
    }

    public function updateComplaint($id, array $data): Complaint
    {
        $complaint = $this->getComplaintById($id);
        $complaint->update($data);
        return $complaint;
    }

    public function deleteComplaint($id): void
    {
        $complaint = $this->getComplaintById($id);
        $complaint->delete();
    }

    public function getComplaintsByUser()
    {
        return Complaint::where('user_id', auth()->id())->get();
    }
}
