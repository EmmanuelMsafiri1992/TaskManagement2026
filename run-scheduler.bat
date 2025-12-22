@echo off
REM Laravel Task Scheduler Runner for Windows
REM This batch file runs the Laravel scheduler every minute

cd /d "%~dp0"
php artisan schedule:run >> storage/logs/scheduler.log 2>&1
