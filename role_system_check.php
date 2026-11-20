<?php

declare(strict_types=1);

function out(string $status, string $label, string $detail = ''): void
{
    $line = sprintf('[%-5s] %s', $status, $label);
    if ($detail !== '') $line .= ' - '.$detail;
    echo $line.PHP_EOL;
}

function fail(array &$failures, string $label, string $detail = ''): void
{
    $failures[] = $label.($detail !== '' ? ': '.$detail : '');
    out('FAIL', $label, $detail);
}

$root = __DIR__;
chdir($root);
$failures = [];

if (!is_file($root.'/vendor/autoload.php')) {
    fail($failures, 'Composer autoload', 'vendor/autoload.php is missing');
    exit(1);
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1) Core route and controller verification.
$routes = $app['router']->getRoutes();
$routeNames = [];
$controllerIssues = [];
foreach ($routes as $route) {
    if ($route->getName()) $routeNames[$route->getName()] = $route;
    $action = $route->getActionName();
    if ($action === 'Closure' || !str_contains($action, '@')) continue;
    [$class, $method] = explode('@', $action, 2);
    if (!class_exists($class) || !method_exists($class, $method)) {
        $controllerIssues[] = $action.' -> '.$route->uri();
    }
}
if ($controllerIssues) fail($failures, 'Route/controller methods', implode('; ', $controllerIssues));
else out('OK', 'Route/controller methods', count($routes).' routes loaded');

$required = [
    'shared' => ['dashboard','profile.show','profile.update','notifications.index','notifications.read-all','notifications.read','logout'],
    'admin' => ['admin.dashboard','admin.users','admin.users.store','admin.users.update','admin.users.reset-password','admin.users.toggle','admin.subjects','admin.subjects.store','admin.subjects.enroll','admin.subjects.unenroll','admin.activities','admin.activities.show','admin.submissions','admin.submissions.show','admin.submissions.ai','admin.repository','admin.monitor','admin.ai-settings','admin.ai-settings.save','admin.ai-monitor','admin.ai-monitor.test','admin.audit'],
    'adviser' => ['adviser.dashboard','adviser.rooms.index','adviser.rooms.store','adviser.rooms.show','adviser.rooms.regenerate-key','adviser.rooms.toggle','adviser.rooms.students.remove','adviser.activities.index','adviser.activities.create','adviser.activities.store','adviser.activities.show','adviser.activities.edit','adviser.activities.update','adviser.activities.toggle','adviser.activities.submissions','adviser.submissions.show','adviser.submissions.ai','adviser.submissions.grade','adviser.scores','adviser.reports','chat.index','chat.room.student','chat.show','chat.send','chat.messages'],
    'student' => ['student.dashboard','student.rooms','student.rooms.join','student.rooms.leave','student.activities.index','student.activities.show','student.activities.submit','student.submissions.index','student.submissions.show','student.grades','chat.index','chat.room','chat.show','chat.send','chat.messages'],
];
foreach ($required as $group => $names) {
    $missing = array_values(array_filter($names, fn($n) => !isset($routeNames[$n])));
    if ($missing) fail($failures, ucfirst($group).' required routes', implode(', ', $missing));
    else out('OK', ucfirst($group).' required routes', count($names).' verified');
}

// 2) Blade route references and CSRF form checks.
$viewFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/resources/views', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (str_ends_with($file->getFilename(), '.blade.php')) $viewFiles[] = $file->getPathname();
}
$routeRefIssues = [];
$csrfIssues = [];
foreach ($viewFiles as $file) {
    $content = (string) file_get_contents($file);
    preg_match_all('/route\(\s*[\'\"]([^\'\"]+)[\'\"]/', $content, $matches);
    foreach ($matches[1] as $name) {
        if (!isset($routeNames[$name])) $routeRefIssues[] = basename($file).' -> '.$name;
    }
    preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', $content, $forms, PREG_SET_ORDER);
    foreach ($forms as $form) {
        $open = strstr($form[0], '>', true) ?: $form[0];
        $method = 'GET';
        if (preg_match('/method=[\'\"]([^\'\"]+)/i', $open, $mm)) $method = strtoupper($mm[1]);
        if (!in_array($method, ['GET', 'DIALOG'], true) && !str_contains($form[0], '@csrf')) $csrfIssues[] = basename($file).' -> '.$open;
    }
}
if ($routeRefIssues) fail($failures, 'Blade named-route references', implode('; ', $routeRefIssues));
else out('OK', 'Blade named-route references', 'all references resolve');
if ($csrfIssues) fail($failures, 'CSRF-protected forms', implode('; ', $csrfIssues));
else out('OK', 'CSRF-protected forms', 'all non-GET forms include @csrf');

// 3) Compile every Blade file without rendering role-specific data.
$bladeErrors = [];
try {
    $compiler = $app->make('blade.compiler');
    foreach ($viewFiles as $file) {
        try { $compiler->compileString((string) file_get_contents($file)); }
        catch (Throwable $e) { $bladeErrors[] = basename($file).': '.$e->getMessage(); }
    }
} catch (Throwable $e) {
    $bladeErrors[] = $e->getMessage();
}
if ($bladeErrors) fail($failures, 'Blade syntax compilation', implode('; ', $bladeErrors));
else out('OK', 'Blade syntax compilation', count($viewFiles).' views compiled');

// 4) Use the configured database, including MySQL/MariaDB.
{
    try {
        $connection = Illuminate\Support\Facades\DB::connection();
        $pdo = $connection->getPdo();
        out('OK', 'Database connection', $connection->getDriverName());
        $expectedTables = ['users','subjects','rooms','enrollments','activities','submissions','ai_evaluations','grades','chat_conversations','chat_messages','app_notifications','repository_items','audit_logs','system_settings','sessions','cache'];
        $actualTables = Illuminate\Support\Facades\Schema::getTableListing(null, false);
        $missingTables = array_values(array_diff($expectedTables, $actualTables));
        if ($missingTables) fail($failures, 'Required database tables', implode(', ', $missingTables));
        else out('OK', 'Required database tables', count($expectedTables).' verified');

        foreach (['admin','adviser','student'] as $role) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
            $stmt->execute([$role]);
            $count = (int) $stmt->fetchColumn();
            if (!$count) fail($failures, ucfirst($role).' accounts', 'Import database/school_repository.sql');
            else out('OK', ucfirst($role).' accounts', $count.' preserved account(s)');
        }

        $dupKeys = $pdo->query("SELECT enrollment_key, COUNT(*) c FROM rooms GROUP BY enrollment_key HAVING COUNT(*) > 1")->fetchAll(PDO::FETCH_ASSOC);
        if ($dupKeys) fail($failures, 'Unique room enrollment keys', count($dupKeys).' duplicate key(s)');
        else out('OK', 'Unique room enrollment keys', 'no duplicates');

        $roomMismatch = (int)$pdo->query("SELECT COUNT(*) FROM enrollments e JOIN rooms r ON r.id=e.room_id WHERE e.room_id IS NOT NULL AND e.subject_id<>r.subject_id")->fetchColumn();
        if ($roomMismatch) fail($failures, 'Room/enrollment relationships', $roomMismatch.' mismatch(es)');
        else out('OK', 'Room/enrollment relationships', 'consistent');

        $activityMismatch = (int)$pdo->query("SELECT COUNT(*) FROM activities a JOIN subjects s ON s.id=a.subject_id LEFT JOIN rooms r ON r.id=a.room_id WHERE a.adviser_id<>s.adviser_id OR (a.room_id IS NOT NULL AND (r.id IS NULL OR r.subject_id<>a.subject_id))")->fetchColumn();
        if ($activityMismatch) fail($failures, 'Activity/adviser/room relationships', $activityMismatch.' mismatch(es)');
        else out('OK', 'Activity/adviser/room relationships', 'consistent');

        $chatMismatch = (int)$pdo->query("SELECT COUNT(*) FROM chat_conversations cc JOIN rooms r ON r.id=cc.room_id JOIN subjects s ON s.id=r.subject_id JOIN users u ON u.id=cc.student_id WHERE u.role<>'student' OR s.adviser_id<>cc.adviser_id")->fetchColumn();
        if ($chatMismatch) fail($failures, 'Chat participant relationships', $chatMismatch.' mismatch(es)');
        else out('OK', 'Chat participant relationships', 'consistent');
    } catch (Throwable $e) {
        fail($failures, 'Database role/data checks', $e->getMessage());
    }
}

// 5) UI asset and readability checks.
$css = is_file($root.'/public/css/app.css') ? (string)file_get_contents($root.'/public/css/app.css') : '';
$uiChecks = [
    'School Repository logo' => is_file($root.'/public/images/brand-logo.png'),
    'System background' => is_file($root.'/public/images/system-background.png'),
    'Premium collapsible sidebar' => str_contains($css, 'body.sidebar-collapsed .sidebar'),
    'Mobile sidebar drawer' => str_contains($css, 'body.sidebar-mobile-open .sidebar'),
    'Login layout styles' => str_contains($css, 'body.login-page') && str_contains($css, '.login-card'),
    'Readable sidebar brand' => str_contains($css, '.brand{display:block;color:#fff'),
    'Form label styles' => (bool) preg_match('/label\s*\{[^}]*font-size:/', $css),
    'Non-intercepting navigation script' => is_file($root.'/public/js/app.js') && !str_contains((string)file_get_contents($root.'/public/js/app.js'), 'preventDefault()'),
];
foreach ($uiChecks as $label => $ok) {
    if (!$ok) fail($failures, $label);
    else out('OK', $label);
}

// 6) Groq is intentionally optional until a key is configured.
try {
    $ai = $app->make(App\Services\AiGradingService::class);
    $status = $ai->configurationStatus();
    out('INFO', 'Groq configuration', ($status['configured'] ?? false) ? 'configured' : 'not configured; add a fresh key in Admin > AI Settings');
} catch (Throwable $e) {
    fail($failures, 'Groq service initialization', $e->getMessage());
}

echo PHP_EOL;
if ($failures) {
    echo 'ROLE/SYSTEM CHECK FAILED: '.count($failures).' issue(s) found.'.PHP_EOL;
    foreach ($failures as $failure) echo ' - '.$failure.PHP_EOL;
    exit(1);
}

echo 'ROLE/SYSTEM CHECK PASSED: Admin, Adviser, Student routes/data/UI checks are consistent.'.PHP_EOL;
exit(0);
