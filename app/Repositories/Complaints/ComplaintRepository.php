<?php

namespace App\Repositories\Complaints;

use App\Models\Complaint;

class ComplaintRepository
{
    public function createComplaint(array $data,string $referenceNumber): Complaint
    {
        // dd(auth()->guard('api')->id());
        $data['user_id'] = auth()->guard('api')->id();
        $data['reference_number'] = $referenceNumber;
        return Complaint::create($data);
    }

    public function getComplaintById($id): Complaint
    {
        $complaint = Complaint::with(['governmentEntity','attachments'])->where('id', $id)->first();
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
        return \DB::transaction(function () use ($id, $data) {
            $complaint = Complaint::where('id', $id)->lockForUpdate()->firstOrFail();

            $complaint->update($data);

            return $complaint->fresh();
        });
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
