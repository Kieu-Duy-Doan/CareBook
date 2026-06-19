<?php

namespace App\Services;

use App\Models\SystemLog;

class SystemLogService
{
    /**
     * Ghi log hệ thống.
     */
    public function log(
        string $action,
        ?string $module = null,
        ?string $refType = null,
        ?int $refId = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?int $userId = null
    ): SystemLog {
        return SystemLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'module' => $module,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
