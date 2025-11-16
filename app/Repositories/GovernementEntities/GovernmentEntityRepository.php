<?php

namespace App\Repositories\GovernementEntities;

use App\Models\GovernmentEntities;

class GovernmentEntityRepository
{
    public function getCodeById($id)
    {
        $entity = GovernmentEntities::where('id', $id)->firstOrFail();
        return $entity;
    }
}
