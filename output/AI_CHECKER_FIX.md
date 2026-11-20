# Groq JSON assessment recovery

The reported failure was HTTP 400: `Failed to generate JSON`. The active application uses `AiGradingService` with `openai/gpt-oss-20b`.

## Changes

- Explicit JSON schema and formatting instructions accompany every assessment prompt.
- The checker starts with strict structured output. For JSON generation or unsupported-format failures, it retries with JSON object mode, then plain text containing JSON, with a maximum of three requests.
- Every successful response is validated locally before saving: required feedback fields, numeric score bounds, rubric breakdown, and confidence. Truncated responses are rejected. Provider `failed_generation` content is never saved as a score.
- Credentials, rate limits, unrelated request errors, and refusals do not trigger format retries.
- Output token budget defaults to 8192; `GROQ_MAX_COMPLETION_TOKENS` can override it (bounded to 1024–16384).
- Admin's Test AI Connection now checks a structured assessment using a synthetic math example, rather than merely requesting a plain-text greeting. It saves no grades or evaluations.
- No database schema changes or new API key are required. Final grades remain under adviser control.

## Verification

- Full PHP suite: **64 tests passed, 496 assertions**.
- Nine regression tests cover the reported error, both fallbacks, invalid scores, exhausted retries, non-retryable HTTP errors, unsupported schema mode, truncation, structured connection testing, and refusal.
- Live `php artisan groq:test`: passed with `openai/gpt-oss-20b` and the existing configured key.
- After explicit user approval, real submission #1 was retried successfully: evaluation #2 completed on the first request using strict JSON schema mode, with five rubric criteria. A separate database read confirmed the submission is now `ai_reviewed` and the recommendation is saved. No final grade was published.

Reference: [Groq structured output documentation](https://console.groq.com/docs/structured-outputs).
