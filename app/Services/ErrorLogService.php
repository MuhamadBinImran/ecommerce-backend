<?php

namespace App\Services;

use App\Models\ErrorLog;

class ErrorLogService
{
    public function log(\Throwable $exception, array $context = []): void
    {
        ErrorLog::create([
            'level' => method_exists($exception, 'getCode') ? $exception->getCode() : 'ERROR',
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
        ]);
    }
}
