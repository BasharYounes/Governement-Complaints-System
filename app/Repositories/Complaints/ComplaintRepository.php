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
        $complaint = Complaint::with('governmentEntity')->where('id', $id)->first();
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
        \DB::transaction(function () use ($id, $data) {
            $complaint = $this->getComplaintById($id)->lockForUpdate()->firstOrFail();

//            sleep(10);

            $complaint->update($data);

            return $complaint;
        });
        return Complaint::find($id);
    }

    public function deleteComplaint($id): void
    {
        $complaint = $this->getComplaintById($id);
        $complaint->delete();
    }

    public function getComplaintsByUser()
    {
        return auth()->user()->complaints()->with('attachments')->get();
    }
}
