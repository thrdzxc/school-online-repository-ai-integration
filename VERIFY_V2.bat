@echo off
setlocal
cd /d "%~dp0"
echo ==========================================
echo SCHOOL REPOSITORY V2 VERIFICATION
echo ==========================================
echo.
php -v
if errorlevel 1 goto :phpfail

echo.
echo [1/4] Clearing Laravel caches...
php artisan optimize:clear
if errorlevel 1 goto :fail

echo.
echo [2/4] Checking migrations...
php artisan migrate --force
if errorlevel 1 goto :fail

echo.
echo [3/4] Checking required V2 routes...
php artisan route:list | findstr /I "student/rooms adviser/rooms chat adviser/scores"
if errorlevel 1 goto :fail

echo.
echo [4/4] V2 checks completed.
echo Start the system with: php artisan serve
echo Then open: http://127.0.0.1:8000
pause
exit /b 0

:phpfail
echo PHP was not found. Use the XAMPP PHP path or add PHP to PATH.
pause
exit /b 1

:fail
echo.
echo A Laravel check failed. Read the error above. Most XAMPP issues are caused by disabled mbstring, pdo_sqlite, sqlite3, fileinfo, openssl, or zip extensions.
pause
exit /b 1
