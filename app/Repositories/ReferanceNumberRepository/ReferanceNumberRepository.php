<?php

namespace App\Repositories\ReferanceNumberRepository;

use DB;
use Str;
use App\Attributes\Transactional;

class ReferanceNumberRepository
{
    #[Transactional]
    public function generateReferenceNumber($govCode): string
    {
        $year = now()->year;

        $row = DB::table('referance_numbers')
            ->where('year', $year)
            ->where('gov_code', $govCode)
            ->lockForUpdate()
            ->first();

        if ($row) {
            $counter = $row->counter + 1;

            DB::table('referance_numbers')
                ->where('id', $row->id)
                ->update([
                    'counter' => $counter,
                    'updated_at' => now()
                ]);
        } else {
            DB::table('referance_numbers')->insert([
                'year' => $year,
                'gov_code' => $govCode,
                'counter' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = 1;
        }


        $seqPadded = str_pad($counter, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(Str::random(3));
        return "{$year}-{$govCode}-{$seqPadded}-{$random}";
    }
}
