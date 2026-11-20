<?php
namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class Audit
{
    public static function log(?User $user, string $action, ?string $targetType = null, ?int $targetId = null, array $details = []): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details ?: null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
