@echo off
setlocal EnableExtensions
cd /d "%~dp0"
TITLE School Repository - Test All Roles

echo ============================================================
echo  SCHOOL REPOSITORY - ADMIN / ADVISER / STUDENT CHECK
echo ============================================================
echo.

if not exist artisan (
  echo ERROR: artisan is not in this folder.
  pause
  exit /b 1
)

echo [1/7] Clearing safe Laravel caches...
php artisan config:clear || goto :failed
php artisan route:clear || goto :failed
php artisan view:clear || goto :failed

echo.
echo [2/7] Checking migrations...
php artisan migrate:status || goto :failed

echo.
echo [3/7] Checking all routes...
php artisan route:list || goto :failed

echo.
echo [4/7] Checking Admin, Adviser, Student routes/data/UI...
php role_system_check.php || goto :failed

echo.
echo [5/7] Running isolated Laravel feature tests when PHPUnit extensions are available...
set CAN_RUN_TESTS=1
php -m | findstr /I /C:"dom" >nul || set CAN_RUN_TESTS=0
php -m | findstr /I /C:"xmlwriter" >nul || set CAN_RUN_TESTS=0
php -m | findstr /I /C:"mbstring" >nul || set CAN_RUN_TESTS=0
if "%CAN_RUN_TESTS%"=="1" (
  echo PHPUnit prerequisites found. Tests use an in-memory SQLite database and do NOT modify your real database.
  php artisan test --filter=SchoolRepositoryFlowTest --no-ansi || goto :failed
) else (
  echo [INFO] DOM/XMLWriter/mbstring PHPUnit prerequisites are incomplete, so feature tests were skipped.
  echo [INFO] This does not block the application; role_system_check.php already verified routes, data, CSRF, and UI files.
)

echo.
echo [6/7] Checking Groq configuration status...
php artisan groq:status

echo.
echo [7/7] Re-running core system checker...
php system_check.php || goto :failed

echo.
echo ============================================================
echo  ALL AVAILABLE LOCAL ROLE CHECKS PASSED
 echo ============================================================
echo You can now run: .\start_windows.bat
pause
exit /b 0

:failed
echo.
echo ============================================================
echo  TEST FAILED - review the error shown above
 echo ============================================================
pause
exit /b 1
