<?php

namespace App\Repositories\Web;

use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class AdminRepository
{
    public function createAdmin(array $data): Admin
    {
        return Admin::create($data);
    }

    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->firstOrFail();
    }

    public function search(string $keyword)
    {
        return Complaint::query()->with(['user','attachments','governmentEntity'])->where(function (Builder $query)use($keyword)
        {
            $query->where('description', 'like', "%{$keyword}%")
                ->orWhere('status','like',"%$keyword%")
                ->orWhere('type','like',"%$keyword%")
                ->orWhere('reference_number','like',"%$keyword%")
                ->orWhere('location','like',"%$keyword%")
                ->orWhereHas('governmentEntity',function (Builder $q) use ($keyword)
                {
                 $q->where('name','like',"%$keyword%");
                });
        })->orderByDesc('created_at')->get();
    }

     public function searchEmployees(string $keyword)
    {
        $query = Employee::query();
        if(!empty($keyword)) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                  ->orWhere('email', 'LIKE', "%$keyword%")
                  ->orWhere('phone', 'LIKE', "%$keyword%");
            });
        }
        return $query->get();
    }

}
