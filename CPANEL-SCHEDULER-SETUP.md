# Setting Up Laravel Scheduler on cPanel

This guide will help you set up the Laravel scheduler for recurring tasks on your cPanel hosting.

## 📋 Prerequisites

- Access to your cPanel account
- Your application deployed on cPanel
- PHP CLI access (most cPanel accounts have this)

---

## 🚀 Step-by-Step Setup

### Step 1: Access Cron Jobs in cPanel

1. Log in to your **cPanel account**
2. Scroll down to the **Advanced** section
3. Click on **"Cron Jobs"**

   ![cPanel Cron Jobs](https://via.placeholder.com/600x100/4CAF50/FFFFFF?text=Advanced+%3E+Cron+Jobs)

---

### Step 2: Configure Cron Email (Optional)

At the top of the Cron Jobs page, you'll see **"Cron Email"**:

1. Enter your email address if you want to receive cron execution reports
2. Or leave it blank if you don't want email notifications
3. Click **"Update Email"**

> **Tip:** During testing, use an email to catch any errors. Once working, you can disable it to avoid spam.

---

### Step 3: Add New Cron Job

Scroll down to **"Add New Cron Job"** section.

#### Settings:

**Common Settings:** Select **"Once Per Minute (* * * * *)"** from the dropdown

This will automatically set:
- **Minute:** `*`
- **Hour:** `*`
- **Day:** `*`
- **Month:** `*`
- **Weekday:** `*`

#### Command:

In the **"Command"** field, enter ONE of these (choose based on your setup):

**Option 1: Standard cPanel Setup**
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
```

**Option 2: If you're in a subdirectory**
```bash
/usr/local/bin/php /home/yourusername/public_html/taskhub/artisan schedule:run >> /dev/null 2>&1
```

**Option 3: With logging (for debugging)**
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /home/yourusername/public_html/storage/logs/scheduler.log 2>&1
```

**Option 4: If PHP is in a different location**
```bash
/usr/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
```

> ⚠️ **Important:** Replace `yourusername` with your actual cPanel username!

#### Click **"Add New Cron Job"**

---

### Step 4: Find Your PHP Path (If needed)

If you're not sure where PHP is located on your server:

1. In cPanel, go to **"Terminal"** (under Advanced)
2. Or use SSH to connect to your server
3. Run this command:

```bash
which php
```

This will show the full path to PHP (e.g., `/usr/local/bin/php` or `/usr/bin/php`)

Use this path in your cron job command.

---

### Step 5: Verify PHP Version

Make sure you're using the correct PHP version:

```bash
php -v
```

If you need a specific PHP version, use its full path:

```bash
/usr/local/bin/php81 /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
```

Common PHP paths on cPanel:
- PHP 8.1: `/usr/local/bin/php81`
- PHP 8.0: `/usr/local/bin/php80`
- PHP 7.4: `/usr/local/bin/php74`

---

## 📝 Understanding the Cron Command

Let's break down the command:

```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
```

| Part | Description |
|------|-------------|
| `/usr/local/bin/php` | Path to PHP executable |
| `/home/yourusername/public_html/artisan` | Path to your Laravel artisan file |
| `schedule:run` | Laravel command to run scheduled tasks |
| `>> /dev/null 2>&1` | Suppresses output (no email spam) |

---

## ✅ Verify It's Working

### Method 1: Check Cron Job List

1. In cPanel, scroll down to **"Current Cron Jobs"**
2. You should see your newly added cron job listed
3. It should show: `* * * * * /usr/local/bin/php ...`

### Method 2: Check Laravel Logs

If you added logging to your cron command:

```bash
tail -f /home/yourusername/public_html/storage/logs/scheduler.log
```

Or via cPanel File Manager:
1. Navigate to `public_html/storage/logs/`
2. Open `scheduler.log`
3. Check for entries

### Method 3: Create a Test Task

1. Log in to your TaskHub application
2. Create a recurring task (repeat every 1 day)
3. In your database, check the `tasks` table:
   - The task should have `recurring_at` set
   - Wait for the scheduled time
   - A new task should be created

### Method 4: SSH Testing (Advanced)

If you have SSH access:

```bash
# Connect to your server via SSH
ssh yourusername@yourdomain.com

# Navigate to your application
cd public_html

# Test the scheduler manually
php artisan schedule:run

# Check for recurring tasks
php artisan tinker
```

Then in Tinker:
```php
\App\Models\Task::whereNotNull('recurring_at')->get(['id', 'title', 'recurring_at']);
exit
```

---

## 🐛 Troubleshooting

### Cron Job Not Running?

**1. Check PHP Path**
```bash
which php
/usr/local/bin/php -v
```

**2. Test Command Manually (via SSH)**
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run
```

**3. Check File Permissions**
```bash
ls -la /home/yourusername/public_html/artisan
# Should show: -rwxr-xr-x or similar
```

If not executable:
```bash
chmod +x /home/yourusername/public_html/artisan
```

**4. Check Laravel Logs**
```bash
tail -n 50 /home/yourusername/public_html/storage/logs/laravel.log
```

**5. Verify Cron Service is Running**
```bash
service crond status
```

### No Tasks Being Created?

**1. Check Database**

Via phpMyAdmin in cPanel:
- Open your database
- Go to `tasks` table
- Check if `recurring_at` column has values

**2. Check Recurring Tasks Setup**

Make sure tasks have recurring patterns set:
```sql
SELECT id, title, recurring_at, meta
FROM tasks
WHERE recurring_at IS NOT NULL;
```

**3. Verify Time Zone**

In your `.env` file:
```env
APP_TIMEZONE=UTC
```

Or your preferred timezone:
```env
APP_TIMEZONE=America/New_York
```

**4. Test Recurring Handler Directly**

Via SSH/Terminal:
```bash
cd public_html
php artisan tinker --execute="(new \Admin\Http\Controllers\RecurringTasks)(request());"
```

---

## 📧 Email Notifications

### Disable Cron Emails

Add this at the start of your command:
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
```

The `>> /dev/null 2>&1` part suppresses all output.

### Enable Cron Emails (for debugging)

Remove the redirect:
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run
```

Or log to file:
```bash
/usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /home/yourusername/scheduler.log 2>&1
```

---

## 🔐 Security Best Practices

### 1. Protect Artisan File

Add to `.htaccess` in your public directory:
```apache
<Files "artisan">
    Order Allow,Deny
    Deny from all
</Files>
```

### 2. Keep Storage Logs Private

Ensure `storage/logs/` is not publicly accessible.

### 3. Use Environment Variables

Never hardcode sensitive data in cron jobs. Use `.env` file.

---

## 📊 Monitoring

### Check Last Cron Run

In cPanel Cron Jobs page, you can see:
- Last run time
- Next run time
- Run count

### Laravel Horizon (Optional)

For advanced monitoring, consider installing Laravel Horizon:
```bash
composer require laravel/horizon
```

---

## 🎯 Common cPanel Hosting Providers

### Shared Hosting Limitations

Some shared hosting providers limit cron jobs:
- **Minimum interval:** Usually 1 minute (some allow 5 minutes)
- **Maximum jobs:** Usually 10-20 cron jobs
- **Execution time:** May have timeouts (30-60 seconds)

### Providers & Their Limits

| Provider | Min Interval | Max Jobs |
|----------|--------------|----------|
| Bluehost | 5 minutes | 20 |
| HostGator | 1 minute | 10 |
| SiteGround | 1 minute | Unlimited |
| GoDaddy | 1 minute | 10 |
| A2 Hosting | 1 minute | Unlimited |

> Check your hosting plan for specific limits.

---

## ✅ Final Checklist

- [ ] Added cron job in cPanel
- [ ] Used correct PHP path
- [ ] Used correct artisan path
- [ ] Set to run every minute (`* * * * *`)
- [ ] Tested command manually via SSH
- [ ] Created a test recurring task
- [ ] Verified new task is created
- [ ] Checked logs for errors
- [ ] Disabled email notifications (after testing)

---

## 📞 Need Help?

If you're still having issues:

1. **Check your hosting documentation** - Each provider may have specific requirements
2. **Contact hosting support** - They can help verify cron jobs are running
3. **Check Laravel logs** - `storage/logs/laravel.log`
4. **Enable email notifications** temporarily to see error messages

---

## 🎉 Success!

Once set up correctly, your cron job will run every minute, and Laravel's scheduler will automatically check for tasks that need to be created based on their recurring patterns.

**Your recurring tasks feature is now live!** 🚀

---

## 📚 Additional Resources

- [Laravel Scheduling Docs](https://laravel.com/docs/10.x/scheduling)
- [cPanel Cron Jobs Documentation](https://docs.cpanel.net/cpanel/advanced/cron-jobs/)
- [Cron Expression Generator](https://crontab.guru/)

---

**Pro Tip:** After deployment, create a test task with daily recurrence and check back in 24 hours to verify it's working! ✨
