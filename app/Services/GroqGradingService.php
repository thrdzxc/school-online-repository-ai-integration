<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqGradingService
{
    public function testConnection(): array
    {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model', 'openai/gpt-oss-20b');
        $baseUrl = rtrim(
            config(
                'services.groq.base_url',
                'https://api.groq.com/openai/v1'
            ),
            '/'
        );

        $timeout = (int) config('services.groq.timeout', 120);

        if (!$apiKey) {
            throw new RuntimeException(
                'Groq API key is not configured.'
            );
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post(
                $baseUrl . '/chat/completions',
                [
                    'model' => $model,

                    'messages' => [
                        [
                            'role' => 'user',
                            'content' =>
                                'Reply only with: Groq connected successfully.',
                        ],
                    ],

                    'temperature' => 0,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Groq connection failed (HTTP '
                . $response->status()
                . '): '
                . $response->body()
            );
        }

        return $response->json();
    }

    public function evaluate(
        string $instructions,
        string $rubric,
        string $studentAnswer,
        float $maxScore = 100
    ): array {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model', 'openai/gpt-oss-20b');

        $baseUrl = rtrim(
            config(
                'services.groq.base_url',
                'https://api.groq.com/openai/v1'
            ),
            '/'
        );

        $timeout = (int) config('services.groq.timeout', 120);

        if (!$apiKey) {
            throw new RuntimeException(
                'Groq API key is not configured.'
            );
        }

        $prompt = <<<PROMPT
You are an academic assessment assistant.

Evaluate the student's submission ONLY according to:
1. The adviser's instructions.
2. The adviser's rubric.

Do not create additional grading criteria.

The AI score is only a recommendation.
The adviser has final authority over the official grade.

ADVISER INSTRUCTIONS:
{$instructions}

ADVISER RUBRIC:
{$rubric}

MAXIMUM SCORE:
{$maxScore}

STUDENT SUBMISSION:
{$studentAnswer}

Return valid JSON only in this format:

{
    "recommended_score": 0,
    "max_score": {$maxScore},
    "summary": "",
    "strengths": [],
    "improvements": [],
    "criterion_results": []
}
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post(
                $baseUrl . '/chat/completions',
                [
                    'model' => $model,

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' =>
                                'You are an academic rubric assessment assistant.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],

                    'temperature' => 0.2,

                    'response_format' => [
                        'type' => 'json_object',
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Groq API error (HTTP '
                . $response->status()
                . '): '
                . $response->body()
            );
        }

        $content = $response->json(
            'choices.0.message.content'
        );

        if (!$content) {
            throw new RuntimeException(
                'Groq returned an empty response.'
            );
        }

        $result = json_decode($content, true);

        if (!is_array($result)) {
            throw new RuntimeException(
                'Groq returned invalid JSON: ' . $content
            );
        }

        return $result;
    }
}