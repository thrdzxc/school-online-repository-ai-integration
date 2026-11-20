<?php

use App\Models\SystemSetting;
use App\Services\AiGradingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$showAiStatus = function (AiGradingService $ai) {
    $status = $ai->configurationStatus();
    $this->info('School Repository - Groq AI status');
    $this->line('Configured : '.(($status['configured'] ?? false) ? 'YES' : 'NO'));
    $this->line('Model      : '.($status['model'] ?? '-'));
    $this->line('Key source : '.($status['key_source'] ?? 'none'));
    if ($status['database_key_unreadable'] ?? false) {
        $this->warn('A stored database key exists but cannot be decrypted. Re-enter it with php artisan groq:configure.');
    }
    if (!($status['configured'] ?? false)) {
        $this->newLine();
        $this->warn('Groq AI is not configured. Run: php artisan groq:configure');
        return 1;
    }
    return 0;
};

Artisan::command('groq:status', $showAiStatus)->purpose('Show whether the Groq AI checker is configured');
// Backward-compatible alias kept for older documentation/scripts.
Artisan::command('openai:status', $showAiStatus)->purpose('Alias for groq:status');

$configureAi = function (AiGradingService $ai) {
    if (!Schema::hasTable('system_settings')) {
        $this->error('The system_settings table does not exist yet. Run: php artisan migrate --seed');
        return 1;
    }

    $this->info('School Repository - Groq AI configuration');
    $this->line('The API key is entered with hidden input and stored encrypted in the local database.');
    $this->newLine();

    $key = trim((string) $this->secret('Paste your Groq API key'));
    if ($key === '') {
        $this->error('No API key was entered. Nothing was changed.');
        return 1;
    }

    $defaultModel = trim((string) SystemSetting::getValue('groq_model', 'openai/gpt-oss-20b'));
    $model = trim((string) $this->ask('Groq model', $defaultModel ?: 'openai/gpt-oss-20b'));
    if ($model === '') $model = 'openai/gpt-oss-20b';

    SystemSetting::setSecret('groq_api_key', $key);
    SystemSetting::setValue('groq_model', $model);

    $this->info('AI settings saved. Testing the connection...');
    try {
        $result = $ai->testConnection();
        $this->info(($result['message'] ?? 'AI connection successful.').' Model: '.($result['model'] ?? $model));
        return 0;
    } catch (Throwable $e) {
        $this->error('AI connection test failed: '.$e->getMessage());
        $this->warn('The key/model were saved. Correct them by running php artisan groq:configure again.');
        return 1;
    }
};

Artisan::command('groq:configure', $configureAi)->purpose('Securely save a Groq API key/model and test the connection');
Artisan::command('openai:configure', $configureAi)->purpose('Alias for groq:configure');

$testAi = function (AiGradingService $ai) {
    try {
        $result = $ai->testConnection();
        $this->info(($result['message'] ?? 'AI connection successful.').' Model: '.($result['model'] ?? '-'));
        return 0;
    } catch (Throwable $e) {
        $this->error($e->getMessage());
        return 1;
    }
};

Artisan::command('groq:test', $testAi)->purpose('Test the configured Groq AI API connection');
Artisan::command('openai:test', $testAi)->purpose('Alias for groq:test');
