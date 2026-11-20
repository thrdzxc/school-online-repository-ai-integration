@echo off
setlocal
cd /d "%~dp0"
TITLE School Repository Server

if not exist artisan (
  echo ERROR: artisan is not in this folder.
  pause
  exit /b 1
)
if not exist vendor\autoload.php (
  echo ERROR: vendor\autoload.php is missing. Run setup_windows.bat first.
  pause
  exit /b 1
)

echo Clearing stale Laravel configuration...
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo Starting School Repository at:
echo http://127.0.0.1:8000
echo.
echo IMPORTANT: Use 127.0.0.1, not localhost, in every browser tab.
echo Press Ctrl+C to stop the server.
echo.
php artisan serve --host=127.0.0.1 --port=8000
