<?php

// Read-only hosting preflight. Keep this file outside the public web directory.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$failures = 0;
$check = function (string $label, bool $ok) use (&$failures): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL;
    if (!$ok) $failures++;
};

$check('PHP 8.2 or newer', PHP_VERSION_ID >= 80200);
foreach (['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl', 'dom', 'xml', 'zip'] as $extension) {
    $check('PHP extension: '.$extension, extension_loaded($extension));
}
foreach ([storage_path(), storage_path('app/private'), storage_path('framework/views'), base_path('bootstrap/cache'), public_path('uploads/profile-photos')] as $directory) {
    $check('Writable: '.$directory, is_dir($directory) && is_writable($directory));
}
$check('Application encryption key configured', filled(config('app.key')));
$check('Production frontend build', is_file(public_path('build/manifest.json')));
$check('No development Vite server marker', !is_file(public_path('hot')));
$check('Public rewrite rules', is_file(public_path('.htaccess')));
$check('Private configuration outside public directory', !is_file(public_path('.env')));

try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    $check('Database connection', true);
    foreach (['users', 'subjects', 'rooms', 'enrollments', 'activities', 'submissions', 'grades', 'ai_evaluations', 'system_settings', 'sessions', 'cache', 'cache_locks', 'chat_conversations', 'chat_messages', 'app_notifications', 'audit_logs'] as $table) {
        $check('Database table: '.$table, Illuminate\Support\Facades\Schema::hasTable($table));
    }
} catch (Throwable $exception) {
    $check('Database connection (check host, credentials, and imported schema)', false);
}

if (in_array('--production', $argv, true)) {
    $check('APP_ENV=production', $app->environment('production'));
    $check('APP_DEBUG=false', !config('app.debug'));
    $host = parse_url((string) config('app.url'), PHP_URL_HOST);
    $check('Public HTTPS APP_URL', str_starts_with((string) config('app.url'), 'https://') && $host && !in_array($host, ['localhost', '127.0.0.1', 'example.com', 'yourdomain.com'], true));
    $check('Secure session cookies', config('session.secure') === true);
    $check('Synchronous queue for shared hosting', config('queue.default') === 'sync');
}

echo PHP_EOL.($failures ? "Preflight failed: $failures check(s)." : 'Preflight passed. Browser and hosting smoke tests are still required.').PHP_EOL;
exit($failures ? 1 : 0);
