<?php
namespace App\Support;
use App\Models\AppNotification;
class Notifier
{
    public static function send(int $userId, string $title, string $message, ?string $url = null): void
    {
        AppNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'url' => $url ? WorkspaceUrlGenerator::destination($url) : null,
        ]);
    }
}
