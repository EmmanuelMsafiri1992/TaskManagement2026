# Laravel Scheduler Setup for Recurring Tasks

Your TaskHub application has **recurring tasks functionality already implemented**! The scheduler is configured to check for tasks that need to be repeated every hour.

## ✅ What's Already Done

- ✅ Recurring tasks logic is implemented in `packages/admin/src/Http/Controllers/RecurringTasks.php`
- ✅ Scheduler is configured in `app/Console/Kernel.php` to run hourly
- ✅ The scheduler checks for tasks with recurring patterns and creates new instances

## 🚀 How to Enable the Scheduler

The Laravel scheduler needs to run continuously. Choose ONE of these methods:

### Option 1: Run Scheduler Worker (Recommended for Development)

Open a **new terminal** and run:

```bash
cd C:\laragon\www\taskhub
php artisan schedule:work
```

Keep this terminal open. The scheduler will run every minute and check for tasks that need to be created.

**Pros:**
- Easy for development
- See logs in real-time
- No additional setup needed

**Cons:**
- Terminal must stay open
- Stops if terminal closes

---

### Option 2: Windows Task Scheduler (Recommended for Production)

This runs the scheduler automatically in the background.

#### Step 1: Open Task Scheduler
1. Press `Win + R`
2. Type `taskschd.msc` and press Enter

#### Step 2: Create a New Task
1. Click **"Create Basic Task..."** in the right panel
2. Name: `TaskHub Laravel Scheduler`
3. Description: `Runs Laravel scheduler for recurring tasks`
4. Click **Next**

#### Step 3: Set Trigger
1. Select **"Daily"**
2. Click **Next**
3. Start: Today's date
4. Start time: `00:00:00`
5. Recur every: `1` days
6. Click **Next**

#### Step 4: Set Action
1. Select **"Start a program"**
2. Click **Next**
3. Program/script: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe`
   (Adjust path based on your Laragon PHP version)
4. Add arguments: `artisan schedule:run`
5. Start in: `C:\laragon\www\taskhub`
6. Click **Next**, then **Finish**

#### Step 5: Configure to Run Every Minute
1. Find your task in the Task Scheduler Library
2. Right-click → **Properties**
3. Go to **Triggers** tab
4. Double-click the trigger
5. Check **"Repeat task every:"**
6. Select **"5 minutes"** or **"1 hour"** (depending on how frequently you want to check)
7. For: **"Indefinitely"**
8. Click **OK** → **OK**

---

### Option 3: Using the Batch File (Quick Test)

I've created a `run-scheduler.bat` file for you. To test:

```bash
cd C:\laragon\www\taskhub
run-scheduler.bat
```

This runs the scheduler once. You can set this up with Windows Task Scheduler to run every minute using the same steps as Option 2.

---

## 📋 How Recurring Tasks Work

1. **User creates a task** with a recurring pattern (daily, weekly, monthly, etc.)
2. **Task is saved** with a `recurring_at` timestamp indicating when the next instance should be created
3. **Scheduler runs** (every hour by default)
4. **RecurringTasks controller** checks for tasks where `recurring_at` matches the current hour
5. **New task instance is created** with the same properties
6. **Next iteration is calculated** and the `recurring_at` field is updated
7. **Process repeats** automatically

## 🧪 Testing Recurring Tasks

### Step 1: Create a Recurring Task
1. Go to your project board
2. Create a task
3. Click on the task to open it
4. Click the **repeat/recurrence icon**
5. Set: "Repeat every **1** day"
6. Click **Save**

### Step 2: Check the Database
```bash
php artisan tinker
```

```php
$task = \App\Models\Task::latest()->first();
echo $task->recurring_at; // Should show next occurrence time
echo json_encode($task->meta['recurring'], JSON_PRETTY_PRINT); // Shows pattern
```

### Step 3: Test the Scheduler
```bash
php artisan tinker --execute="(new \Admin\Http\Controllers\RecurringTasks)(request());"
```

Or use the schedule worker:
```bash
php artisan schedule:work
```

### Step 4: Verify New Task Created
Check your project board - if the time matches, a new task instance should be created.

---

## 📝 Schedule Configuration

Current schedule (in `app/Console/Kernel.php`):

```php
$schedule->call(new RecurringTasks)->hourly();
```

This means recurring tasks are checked **every hour**.

To change frequency, edit `app/Console/Kernel.php`:

- `->everyMinute()` - Every minute (most responsive)
- `->everyFiveMinutes()` - Every 5 minutes
- `->everyTenMinutes()` - Every 10 minutes
- `->hourly()` - Every hour (current)
- `->daily()` - Once per day

---

## 🐛 Troubleshooting

### Scheduler not running?

Check logs:
```bash
tail -f storage/logs/scheduler.log
```

### No tasks being created?

1. Check if task has `recurring_at` set:
```bash
php artisan tinker --execute="\App\Models\Task::whereNotNull('recurring_at')->get(['id', 'title', 'recurring_at']);"
```

2. Run scheduler manually:
```bash
php artisan schedule:run
```

3. Check if time matches (scheduler runs on the hour):
```bash
php artisan tinker --execute="echo now();"
```

### Test specific task:

```bash
php artisan tinker
```

```php
$task = \App\Models\Task::find(1); // Replace 1 with your task ID
$recurring = new \Admin\Http\Controllers\RecurringTasks();
$recurring->createTask($task);
```

---

## ✅ Verify It's Working

1. **Start the scheduler**:
   ```bash
   php artisan schedule:work
   ```

2. **Create a recurring task** in your app

3. **Wait for the scheduled time** or adjust the task's `recurring_at` to the current hour:
   ```bash
   php artisan tinker
   ```

   ```php
   $task = \App\Models\Task::latest()->first();
   $task->recurring_at = now()->startOfHour();
   $task->save();
   ```

4. **Run scheduler manually**:
   ```bash
   php artisan schedule:run
   ```

5. **Check your board** - new task should appear!

---

## 📚 More Information

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/10.x/scheduling)
- [Setting up Cron Jobs](https://laravel.com/docs/10.x/scheduling#running-the-scheduler)

---

**Your recurring tasks feature is fully implemented and ready to use!** Just choose one of the methods above to enable the scheduler. 🎉
