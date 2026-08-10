@echo off
REM Complete Platform Setup Script for Windows

echo.
echo ========================================
echo আইডিয়া প্রকাশন প্ল্যাটফর্ম - সম্পূর্ণ সেটআপ
echo ========================================
echo.

cd /d C:\Users\idea\ideaabd

REM Step 1: Install Composer Dependencies
echo [1/5] Composer ডিপেন্ডেন্সি ইনস্টল করছি...
composer install --no-interaction
if errorlevel 1 goto :error

REM Step 2: Run Database Migrations
echo [2/5] ডাটাবেস মাইগ্রেশন চালাচ্ছি...
php artisan migrate --force
if errorlevel 1 goto :error

REM Step 3: Seed Database
echo [3/5] ডাটাবেসে ডেমো ডেটা যোগ করছি...
php artisan db:seed --class=PlatformSeeder
if errorlevel 1 goto :error

REM Step 4: Optimize
echo [4/5] অ্যাপ্লিকেশন অপ্টিমাইজ করছি...
php artisan optimize
if errorlevel 1 goto :error

REM Step 5: Success Message
echo [5/5] সম্পূর্ণ!
echo.
echo ========================================
echo ✅ সেটআপ সফলভাবে সম্পন্ন হয়েছে!
echo ========================================
echo.
echo পরবর্তী ধাপ:
echo   1. কমান্ড চালান: php artisan serve
echo   2. ব্রাউজার খুলুন: http://127.0.0.1:8000
echo   3. প্ল্যাটফর্ম এক্সপ্লোর করুন!
echo.
echo প্ল্যাটফর্ম হাব: http://127.0.0.1:8000/hub
echo ব্লগ: http://127.0.0.1:8000/blog
echo লেখকরা: http://127.0.0.1:8000/authors
echo ম্যাগাজিন: http://127.0.0.1:8000/webzines
echo গবেষণা: http://127.0.0.1:8000/research
echo.
pause
exit /b 0

:error
echo.
echo ❌ সেটআপে ত্রুটি ঘটেছে!
echo দয়া করে উপরের ত্রুটি বার্তা দেখুন এবং আবার চেষ্টা করুন।
echo.
pause
exit /b 1
