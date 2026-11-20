<?php
namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SubmissionContentService
{
    /**
     * Text entered directly by the student. Text entered directly by the student is normalized here. Uploaded files are
     * extracted by AiGradingService before being sent to the configured AI provider.
     */
    public function directText(Submission $submission): string
    {
        return $this->truncate(trim((string) $submission->text_content));
    }

    public function fileDataBase64(Submission $submission): string
    {
        return $this->storedFileBase64(
            $submission->file_path,
            $submission->original_filename,
            $submission->mime_type
        );
    }

    /**
     * Return raw base64 bytes for legacy/helper use. Current Groq grading extracts
     * supported document text locally before sending the prompt.
     */
    public function storedFileBase64(?string $storedPath, ?string $originalFilename, ?string $storedMime = null): string
    {
        if (!$storedPath || !Storage::disk('local')->exists($storedPath)) {
            throw new RuntimeException('Uploaded file is missing from private storage.');
        }

        $bytes = Storage::disk('local')->get($storedPath);
        if ($bytes === '') {
            throw new RuntimeException('Uploaded file is empty.');
        }

        return base64_encode($bytes);
    }

    public function isPdfFilename(?string $filename): bool
    {
        return strtolower(pathinfo($filename ?? '', PATHINFO_EXTENSION)) === 'pdf';
    }

    public function mimeForFilename(?string $filename, ?string $storedMime = null): string
    {
        $ext = strtolower(pathinfo($filename ?? '', PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'md' => 'text/markdown',
            default => $storedMime ?: 'application/octet-stream',
        };
    }

    private function truncate(string $text): string
    {
        $max = max(2000, (int) config('services.ai.max_chars', 80000));
        if ($text === '') return '';
        return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
    }
}
