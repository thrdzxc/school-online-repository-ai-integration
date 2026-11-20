@echo off
setlocal
cd /d "%~dp0"
TITLE Fix Login Sessions and Room Routes

echo ============================================================
echo  SCHOOL REPOSITORY - SESSION / ROOM REPAIR
echo ============================================================

echo [1/6] Clearing config...
php artisan config:clear

echo [2/6] Clearing routes...
php artisan route:clear

echo [3/6] Clearing compiled views...
php artisan view:clear

echo [4/6] Applying migrations...
php artisan migrate --force

echo [5/6] Clearing database cache...
php artisan cache:clear

echo [6/6] Checking room routes...
php artisan route:list --name=adviser.rooms

echo.
echo Repair complete.
echo Start with: start_windows.bat
echo Use ONLY: http://127.0.0.1:8000
pause
