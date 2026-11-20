<?php

$systemFailures = 0;

function statusLine(string $label, bool $ok, string $detail = ''): void {
    global $systemFailures;
    if (!$ok) $systemFailures++;
    echo ($ok ? '[OK]   ' : '[FAIL] ').$label;
    if ($detail !== '') echo ' - '.$detail;
    echo PHP_EOL;
}

$root = __DIR__;
$envPath = $root.DIRECTORY_SEPARATOR.'.env';
$env = is_file($envPath) ? (string) file_get_contents($envPath) : '';

statusLine('artisan file', is_file($root.DIRECTORY_SEPARATOR.'artisan'));
statusLine('vendor autoload', is_file($root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php'));
statusLine('MySQL import file', is_file($root.'/database/school_repository.sql'));
statusLine('School Repository logo', is_file($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'brand-logo.png'));
statusLine('System background image', is_file($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'system-background.png'));
statusLine('Private upload storage', is_dir($root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'private') && is_writable($root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'private'), 'required for activities, submissions, and repository uploads');
statusLine('Profile photo upload folder', is_dir($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'profile-photos') && is_writable($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'profile-photos'), 'required for user profile photos');
statusLine('Compiled view folder', is_dir($root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views') && is_writable($root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views'), 'required by Blade');

statusLine('mbstring extension', extension_loaded('mbstring'), 'required by Laravel');
statusLine('PDO MySQL extension', extension_loaded('pdo_mysql'), 'required by MySQL/MariaDB');
statusLine('OpenSSL extension', extension_loaded('openssl'), 'required for Groq HTTPS');
statusLine('fileinfo extension', extension_loaded('fileinfo'), 'required for upload validation');
statusLine('HTTPS transport support', extension_loaded('curl') || extension_loaded('openssl'), extension_loaded('curl') ? 'cURL available' : 'OpenSSL available');

$zipAvailable = class_exists(ZipArchive::class);
$fallbackAvailable = false;
$fallbackLabel = '';
if (function_exists('shell_exec') && PHP_OS_FAMILY === 'Windows') {
    $where = @shell_exec('where powershell 2>NUL');
    $fallbackAvailable = is_string($where) && trim($where) !== '';
    $fallbackLabel = $fallbackAvailable ? 'PowerShell fallback available' : '';
} elseif (function_exists('shell_exec')) {
    $where = @shell_exec('command -v unzip 2>/dev/null');
    $fallbackAvailable = is_string($where) && trim($where) !== '';
    $fallbackLabel = $fallbackAvailable ? 'unzip fallback available' : '';
}
statusLine('DOCX/PPTX extraction path', $zipAvailable || $fallbackAvailable,
    $zipAvailable ? 'PHP ZipArchive available' : ($fallbackLabel ?: 'enable PHP zip or an archive extractor'));

preg_match('/^GROQ_API_KEY=(.*)$/m', $env, $m);
$key = trim($m[1] ?? '');
echo '[INFO] Groq API key - '.($key !== '' ? 'configured in .env' : 'not bundled; add it in Admin > AI Settings or run php artisan groq:configure').PHP_EOL;

preg_match('/^DB_CONNECTION=(.*)$/m', $env, $dbm);
$dbConnection = trim($dbm[1] ?? '');
statusLine('Database connection', in_array($dbConnection, ['mysql','mariadb'], true), $dbConnection ?: 'not set');

preg_match('/^SESSION_DRIVER=(.*)$/m', $env, $sm);
$sessionDriver = trim($sm[1] ?? '');
statusLine('Session driver', $sessionDriver === 'database', $sessionDriver ?: 'not set');

preg_match('/^CACHE_STORE=(.*)$/m', $env, $cm);
$cacheStore = trim($cm[1] ?? '');
statusLine('Cache store', $cacheStore === 'database', $cacheStore ?: 'not set');

preg_match('/^SESSION_COOKIE=(.*)$/m', $env, $ckm);
$sessionCookie = trim($ckm[1] ?? '');
statusLine('Unique session cookie', $sessionCookie === 'school_repository_premium_v5_session', $sessionCookie ?: 'not set');

echo PHP_EOL.'Run these next for Laravel-level verification:'.PHP_EOL;
echo '  php artisan config:clear'.PHP_EOL;
echo '  php artisan route:clear'.PHP_EOL;
echo '  php artisan view:clear'.PHP_EOL;
echo '  php artisan cache:clear'.PHP_EOL;
echo '  php artisan migrate:status'.PHP_EOL;
echo '  php artisan route:list'.PHP_EOL;
echo '  php artisan groq:status'.PHP_EOL;

if ($systemFailures > 0) {
    echo PHP_EOL.'SYSTEM CHECK FAILED: '.$systemFailures.' required check(s) failed.'.PHP_EOL;
    exit(1);
}
echo PHP_EOL.'SYSTEM CHECK PASSED.'.PHP_EOL;
exit(0);
