<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateAuditDetailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $complaint, protected $original, protected $dirty)
    {
        $this->complaint = $complaint;
        $this->original = $original;
        $this->dirty = $dirty;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $auditLog = $this->complaint->auditLogs()->latest()->first();

        foreach ($this->dirty as $field => $newValue) {
            $notes = null;
            if ($field === 'status' && isset($this->dirty['notes'])) {
                $notes = $this->dirty['notes'];
            }

            $originalValue = $this->original[$field] ?? null;

            // تحويل القيم إلى JSON فقط إذا كانت arrays أو objects
            // القيم النصية والرقمية تبقى كما هي
            $originalValue = $this->normalizeValue($originalValue);
            $normalizedValue = $this->normalizeValue($newValue);


            $auditLog->details()->create([
                'field_name' => $field,
                'data' => json_encode($this->original, JSON_UNESCAPED_UNICODE),
                'old_value' => $originalValue,
                'new_value' => $normalizedValue,
                'notes' => $notes ?? null,
            ]);
        }
    }

    private function normalizeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // إذا كانت JSON string، نحاول تحويلها إلى array ثم إرجاعها كـ JSON مرة أخرى
        // لضمان التنسيق الموحد
        if (is_string($value) && $this->isJsonString($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        return $value;
    }

    private function isJsonString(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
