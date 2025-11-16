<?php

namespace App\Repositories\ReferanceNumberRepository;

use DB;
use Str;

class ReferanceNumberRepository
{
    public function generateReferenceNumber($govCode): string
    {
        $year = now()->year;

        $counter = DB::transaction(function () use ($year, $govCode) {
            $row = DB::table('referance_numbers')
                ->where('year', $year)
                ->where('gov_code', $govCode)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $new = $row->counter + 1;
                DB::table('referance_numbers')
                    ->where('id', $row->id)
                    ->update(['counter' => $new, 'updated_at' => now()]);
                return $new;
            } else {
                DB::table('referance_numbers')->insert([
                    'year' => $year,
                    'gov_code' => $govCode,
                    'counter' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return 1;
            }
        });

        $seqPadded = str_pad($counter, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(Str::random(3));
        return "{$year}-{$govCode}-{$seqPadded}-{$random}";
    }
}
