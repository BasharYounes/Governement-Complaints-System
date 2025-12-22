<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use App\Attributes\Transactional;

class TransactionAspect
{
    public static function handle(object $service, string $method, array $arguments)
    {
        $reflection = new ReflectionMethod($service, $method);

        $attributes = $reflection->getAttributes(Transactional::class);

        if (empty($attributes)) {
            return $reflection->invokeArgs($service, $arguments);
        }

        return DB::transaction(function () use ($reflection, $service, $arguments) {
            return $reflection->invokeArgs($service, $arguments);
        });
    }
}
