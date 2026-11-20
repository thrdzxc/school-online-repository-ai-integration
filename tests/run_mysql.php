<?php

// Run only from the CLI. This runner creates and removes its own isolated database.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if ($app->configurationIsCached()) {
    fwrite(STDERR, "Clear the configuration cache before isolated database testing.\n");
    exit(1);
}

$connection = config('database.connections.mysql');
$name = 'school_deploy_test_'.bin2hex(random_bytes(6));
$pdo = new PDO(
    'mysql:host='.$connection['host'].';port='.$connection['port'].';charset=utf8mb4',
    $connection['username'], $connection['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

try {
    $process = new Symfony\Component\Process\Process([
        PHP_BINARY, 'vendor/phpunit/phpunit/phpunit', '--log-junit', 'output/deployment-mysql.xml',
    ], dirname(__DIR__), [
        'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => $name,
        'DB_HOST' => $connection['host'], 'DB_PORT' => (string) $connection['port'],
        'DB_USERNAME' => $connection['username'], 'DB_PASSWORD' => $connection['password'], 'DB_URL' => '',
        'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
        'BCRYPT_ROUNDS' => '4', 'MAIL_MAILER' => 'array',
    ]);
    $process->setTimeout(600);
    $exitCode = $process->run(function ($type, $buffer) { echo $buffer; });
} finally {
    // $name is generated above, never supplied by a caller or taken from .env.
    $pdo->exec('DROP DATABASE `'.$name.'`');
    echo "Isolated test database removed. Existing application database was not used.\n";
}
exit($exitCode);
