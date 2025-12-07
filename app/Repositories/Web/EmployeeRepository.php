<?php

namespace App\Repositories\Web;

use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository
{
     public function searchForEmployee(?string $keyword, int $governmentEntityId)
{
    return Complaint::query()
        ->with(['user','attachments','governmentEntity'])
        ->where('government_entity_id', $governmentEntityId)
        ->when($keyword, function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'like', "%{$keyword}%")
                  ->orWhere('status','like',"%{$keyword}%")
                  ->orWhere('type','like',"%{$keyword}%")
                  ->orWhere('reference_number','like',"%{$keyword}%")
                  ->orWhere('location','like',"%{$keyword}%")
                  ->orWhereHas('governmentEntity', function ($q2) use ($keyword) {
                      $q2->where('name', 'like', "%{$keyword}%");
                  });
            });
        })
        ->orderByDesc('created_at')
        ->get();
}

}