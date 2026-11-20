@echo off
cd /d "%~dp0"
TITLE School Repository - Configure Groq
if not exist artisan (
  echo ERROR: Run this file from the Laravel project folder.
  pause
  exit /b 1
)
php artisan groq:configure
if errorlevel 1 (
  echo.
  echo Groq setup did not complete successfully. Read the error above.
  pause
  exit /b 1
)
echo.
php artisan groq:status
pause
