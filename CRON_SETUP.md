# Recurring Tasks - Cron Job Setup Guide

This guide will help you set up the cron job required for recurring tasks to work properly in production.

## Overview

The TaskHub application includes a recurring tasks feature that automatically creates new task instances based on defined patterns (daily, weekly, monthly, yearly). For this feature to work, you **MUST** set up a system cron job that triggers Laravel's scheduler.

## How It Works

1. **User configures recurring task** - Sets pattern (daily/weekly/monthly/yearly) via the UI
2. **System calculates next occurrence** - The `recurring_at` timestamp is stored in the database
3. **Cron job runs every minute** - Executes Laravel's scheduler
4. **Scheduler checks hourly** - Runs `tasks:process-recurring` command every hour
5. **Command processes tasks** - Creates new task instances for tasks due this hour

## Prerequisites

- PHP CLI access on your server
- Crontab access (Linux/Unix) or Task Scheduler (Windows)
- Write permissions to Laravel storage directory

---

## Production Setup (Linux/Unix)

### Step 1: Access Server Crontab

SSH into your production server and open the crontab editor:

```bash
crontab -e
```

### Step 2: Add Laravel Scheduler Entry

Add this line at the bottom (replace path with your actual project path):

```bash
* * * * * cd /var/www/taskhub && php artisan schedule:run >> /dev/null 2>&1
```

**Important Notes:**
- Replace `/var/www/taskhub` with your actual project path
- This runs every minute (Laravel's scheduler will determine what actually runs)
- Errors are suppressed (`>> /dev/null 2>&1`)

### Step 3: Verify Crontab

List your crontab entries to verify:

```bash
crontab -l
```

### Step 4: Test the Setup

Test the scheduler manually:

```bash
cd /var/www/taskhub
php artisan schedule:list
php artisan schedule:run --verbose
```

---

## Development Setup (Windows/Laragon)

### Option 1: Windows Task Scheduler (Recommended)

1. **Open Task Scheduler**
   - Press `Win + R`, type `taskschd.msc`, press Enter

2. **Create Basic Task**
   - Click "Create Basic Task" in the right panel
   - Name: "TaskHub Laravel Scheduler"
   - Description: "Runs Laravel scheduler every minute"

3. **Set Trigger**
   - Trigger: "Daily"
   - Start date/time: Today, any time
   - Recur every: 1 day
   - Click "Next"

4. **Set Action**
   - Action: "Start a program"
   - Program/script: `C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe` (adjust your PHP version)
   - Add arguments: `artisan schedule:run`
   - Start in: `C:\laragon\www\taskhub`

5. **Advanced Settings**
   - After creating, right-click the task → "Properties"
   - Go to "Triggers" tab → Edit trigger
   - Check "Repeat task every" → Select "1 minute"
   - Duration: "Indefinitely"
   - Check "Enabled"
   - Click OK

6. **Test the Task**
   - Right-click the task → "Run"
   - Check "Last Run Result" - should be "0x0" (success)

### Option 2: Keep-Alive Command (Development Only)

For development, you can run this command in a terminal (keeps running):

```bash
php artisan schedule:work
```

**Note:** This command must stay running. Close the terminal and it stops.

---

## Verification & Testing

### 1. Check Scheduled Commands

```bash
php artisan schedule:list
```

Expected output:
```
0 * * * *  tasks:process-recurring ............... Next Due: X minutes from now
```

### 2. Run Scheduler Manually

```bash
php artisan schedule:run --verbose
```

### 3. Test Recurring Tasks Command Directly

```bash
php artisan tasks:process-recurring
```

Expected output:
```
Starting recurring tasks processor at 2025-01-15 10:00:00
Found X task(s) to process
Processing task ID: 123 - Daily Standup
  → Processed successfully
...
```

### 4. Check Laravel Logs

Monitor the logs for any errors:

```bash
tail -f storage/logs/laravel.log
```

Look for log entries like:
```
[2025-01-15 10:00:00] local.INFO: Recurring tasks processed {"processed":2,"skipped":0,"failed":0}
```

---

## Troubleshooting

### Problem: "No scheduled commands are ready to run"

**Solution:** This is normal if no tasks are due to run at this hour. The scheduler only runs the command on the hour (00 minutes).

### Problem: Cron job not executing

**Linux:**
```bash
# Check cron service status
sudo service cron status

# Check cron logs
grep CRON /var/log/syslog
```

**Windows:**
- Open Event Viewer → Task Scheduler History
- Look for errors or warnings

### Problem: PHP not found

**Solution:** Use absolute path to PHP:

Linux:
```bash
* * * * * /usr/bin/php /var/www/taskhub/artisan schedule:run >> /dev/null 2>&1
```

Windows Task Scheduler:
- Use full path: `C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe`

### Problem: Permission denied

**Solution:** Ensure the web server user can write to storage:

```bash
cd /var/www/taskhub
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Problem: Tasks not being created

**Check these:**

1. **Verify tasks exist with recurring_at:**
   ```bash
   php artisan tinker
   \App\Models\Task::whereNotNull('recurring_at')->count();
   ```

2. **Check recurring_at timestamps:**
   ```bash
   php artisan tinker
   \App\Models\Task::whereNotNull('recurring_at')->get(['id', 'name', 'recurring_at']);
   ```

3. **Verify meta data:**
   ```bash
   php artisan tinker
   \App\Models\Task::find(YOUR_TASK_ID)->meta;
   ```

---

## Alternative: Supervisor (Production)

For production environments, consider using Supervisor to keep the scheduler running:

### Install Supervisor (Ubuntu/Debian)

```bash
sudo apt-get install supervisor
```

### Create Configuration

Create file: `/etc/supervisor/conf.d/taskhub-scheduler.conf`

```ini
[program:taskhub-scheduler]
process_name=%(program_name)s
command=php /var/www/taskhub/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/taskhub/storage/logs/scheduler.log
```

### Start Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start taskhub-scheduler
```

---

## Monitoring & Maintenance

### Enable Logging

Logs are already enabled in the command. Check:
- `storage/logs/laravel.log` for recurring task processing logs

### Create Monitoring Alerts

Consider setting up monitoring for:
1. Cron job failures
2. Command execution time
3. Failed task cloning

### Regular Checks

Periodically verify:
```bash
# Check last scheduler run
php artisan schedule:list

# Check for stuck tasks
php artisan tinker
\App\Models\Task::whereNotNull('recurring_at')
    ->where('recurring_at', '<', now()->subHour())
    ->get(['id', 'name', 'recurring_at']);
```

---

## Need Help?

If you encounter issues:

1. Check logs: `storage/logs/laravel.log`
2. Run command manually: `php artisan tasks:process-recurring`
3. Verify cron is running: `crontab -l`
4. Test scheduler: `php artisan schedule:run --verbose`

## Summary

✅ **Cron job runs every minute** - Executes Laravel scheduler
✅ **Scheduler runs command hourly** - Processes recurring tasks
✅ **Command has logging** - Track success/failures
✅ **Prevention mechanisms** - Won't overlap or duplicate

Remember: The cron job is **CRITICAL** - without it, recurring tasks will never execute!
