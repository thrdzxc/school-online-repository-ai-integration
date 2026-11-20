@echo off
setlocal EnableExtensions
cd /d "%~dp0"

TITLE School Repository Setup - Fixed

echo ============================================================
echo  SCHOOL ONLINE REPOSITORY - FIXED WINDOWS SETUP
echo ============================================================
echo Project folder: %CD%
echo.

if not exist composer.json (
  echo ERROR: composer.json is not in this folder.
  echo Open the folder where artisan and composer.json are directly visible.
  pause
  exit /b 1
)

where php >nul 2>nul
if errorlevel 1 (
  echo ERROR: PHP was not found in PATH.
  echo Run: where php
  echo Then use the XAMPP PHP installation you intend to use.
  pause
  exit /b 1
)

if not exist .env (
  echo [1/10] Creating .env from .env.example...
  copy /Y .env.example .env >nul
) else (
  echo [1/10] Existing .env found.
)

echo [2/10] MySQL setup: start MySQL in XAMPP and import database\school_repository.sql
 echo into an empty school_repository database in phpMyAdmin before continuing.

if not exist storage\app\private mkdir storage\app\private
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\cache\data mkdir storage\framework\cache\data
if not exist bootstrap\cache mkdir bootstrap\cache
if not exist public\uploads\profile-photos mkdir public\uploads\profile-photos

if not exist vendor\autoload.php (
  echo [3/10] vendor is missing. Running Composer...
  where composer >nul 2>nul
  if errorlevel 1 (
    echo ERROR: Composer was not found and vendor\autoload.php is missing.
    pause
    exit /b 1
  )
  set COMPOSER_PROCESS_TIMEOUT=2000
  call composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
  if errorlevel 1 (
    echo ERROR: Composer install failed.
    pause
    exit /b 1
  )
) else (
  echo [3/10] vendor\autoload.php already exists.
)

if not exist artisan (
  echo ERROR: artisan is missing.
  pause
  exit /b 1
)

echo [4/10] Checking APP_KEY...
findstr /B /C:"APP_KEY=base64:" .env >nul
if errorlevel 1 (
  php artisan key:generate --force
  if errorlevel 1 (
    echo ERROR: Could not generate APP_KEY.
    pause
    exit /b 1
  )
) else echo APP_KEY: OK

echo [5/10] Checking required PHP extensions...
php -m | findstr /I /C:"mbstring" >nul
if errorlevel 1 (echo ERROR: Enable extension=mbstring in the php.ini shown by: php --ini & pause & exit /b 1) else echo mbstring: OK
php -m | findstr /I /C:"pdo_mysql" >nul
if errorlevel 1 (echo ERROR: Enable pdo_mysql in php.ini. & pause & exit /b 1) else echo PDO MySQL: OK
php -m | findstr /I /C:"openssl" >nul
if errorlevel 1 (echo ERROR: Enable OpenSSL. Groq HTTPS needs it. & pause & exit /b 1) else echo OpenSSL: OK
php -m | findstr /I /C:"fileinfo" >nul
if errorlevel 1 (echo ERROR: Enable fileinfo for uploads. & pause & exit /b 1) else echo Fileinfo: OK
php -m | findstr /I /C:"zip" >nul
if errorlevel 1 (echo ZIP: not enabled. DOCX/PPTX will use the Windows fallback when available.) else echo ZIP: OK

echo [6/10] Clearing configuration, routes, and compiled views...
php artisan config:clear
if errorlevel 1 goto :laravel_error
php artisan route:clear
if errorlevel 1 goto :laravel_error
php artisan view:clear
if errorlevel 1 goto :laravel_error

echo [7/10] Running database migrations...
php artisan migrate --force
if errorlevel 1 (
  echo ERROR: Database migration failed.
  pause
  exit /b 1
)

echo [8/10] Clearing database-backed application cache...
php artisan cache:clear
if errorlevel 1 (
  echo WARNING: Cache clear failed, but migrations completed. Continuing with route verification.
)

echo [9/10] Verifying adviser room routes...
php artisan route:list --name=adviser.rooms
if errorlevel 1 (
  echo ERROR: Adviser room routes failed to load.
  pause
  exit /b 1
)

echo [10/10] Running system and role checks...
php system_check.php
if errorlevel 1 (
  echo ERROR: Core system check failed.
  pause
  exit /b 1
)
php role_system_check.php
if errorlevel 1 (
  echo ERROR: Admin/Adviser/Student role check failed.
  pause
  exit /b 1
)

echo.
echo ============================================================
echo  SETUP COMPLETE
echo ============================================================
echo Session storage : MySQL database
 echo Cache storage   : MySQL database
 echo Login URL       : http://127.0.0.1:8000/login
 echo Adviser rooms   : http://127.0.0.1:8000/adviser/rooms
 echo.
echo Existing accounts and passwords are preserved by the SQL import.
echo.
echo IMPORTANT: Always use 127.0.0.1:8000. Do not switch to localhost:8000.
echo Role test: TEST_ALL_ROLES.bat
echo Next: run start_windows.bat
pause
exit /b 0

:laravel_error
echo ERROR: Laravel could not load. Check PHP extensions and .env.
pause
exit /b 1
