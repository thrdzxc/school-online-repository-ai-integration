@echo off
cd /d "%~dp0"
TITLE School Repository - Verify Groq
echo ============================================================
echo  GROQ STATUS
echo ============================================================
php artisan groq:status
echo.
echo ============================================================
echo  GROQ CONNECTION TEST
echo ============================================================
php artisan groq:test
echo.
pause
