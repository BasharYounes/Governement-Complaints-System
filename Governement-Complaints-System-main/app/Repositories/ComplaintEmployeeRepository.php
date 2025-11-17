<?php

namespace App\Repositories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Collection;

class ComplaintEmployeeRepository
{
    /**
     * Get all complaints for a specific government entity.
     */
    public function getComplaintsByGovernmentEntity(int $governmentEntityId): Collection
    {
        return Complaint::where('government_entity_id', $governmentEntityId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find a single complaint by ID and government entity.
     */
    public function findComplaintById(int $complaintId, int $governmentEntityId): Complaint
    {
        return Complaint::where('id', $complaintId)
            ->where('government_entity_id', $governmentEntityId)
            ->firstOrFail();
    }

    /**
     * Update a complaint's status.
     */
    public function updateStatus(Complaint $complaint, string $status): Complaint
    {
        $complaint->update(['status' => $status]);
        return $complaint->fresh();
    }

    /**
     * Add notes to a complaint.
     */
    public function addNotes(Complaint $complaint, string $notes): Complaint
    {
        $complaint->update(['description' => $complaint->description . "\nNote: " . $notes]);
        return $complaint->fresh();
    }
}
