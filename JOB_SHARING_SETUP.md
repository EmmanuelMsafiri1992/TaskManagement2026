# Job Sharing System - Documentation

## Overview

The Job Sharing System automatically fetches job postings from your V11 (secondary) database and assigns them to users based on their assigned countries. The system creates tasks, generates shortened URLs, and sends notifications to users.

## Features

✅ **Automatic Job Fetching** - Fetches jobs posted in the last 8 hours
✅ **Country-Based Assignment** - Assigns jobs to users based on their country assignments
✅ **Custom URL Shortener** - Generates short, trackable URLs for job links
✅ **Pre-Formatted Content** - Jobs are formatted for social media sharing
✅ **Task Management** - Creates tasks automatically for each assigned job
✅ **Email Notifications** - Users receive email notifications for new job assignments
✅ **Duplicate Prevention** - Prevents assigning the same job to a user twice
✅ **Progress Tracking** - Mark jobs as copied when completed

---

## System Components

### 1. **Models**

- **`App\Models\V11\Post`** - Accesses job posts from V11 database
- **`App\Models\JobShare`** - Tracks job assignments to users
- **`App\Models\ShortenedUrl`** - Custom URL shortening service

### 2. **Services**

- **`App\Services\JobAssignmentService`** - Core job assignment logic
- **`App\Services\UrlShortenerService`** - URL shortening functionality

### 3. **Console Command**

- **`jobs:assign-new`** - Fetches and assigns new jobs
- Runs automatically every hour via Laravel scheduler

### 4. **Notification**

- **`App\Notifications\NewJobAssigned`** - Sent when a job is assigned to a user

### 5. **Controllers**

- **`JobShareController`** - API endpoints for job management
- **`UrlShortenerController`** - Handles URL redirects and statistics

---

## Database Tables

### `job_shares`
Tracks which jobs have been assigned to which users.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users table |
| task_id | bigint | FK to tasks table |
| v11_post_id | bigint | Post ID from V11 database |
| post_title | string | Job title |
| post_url | text | Original job URL |
| country_code | string(2) | Country code (US, UK, etc.) |
| shortened_url | string | Generated short URL |
| formatted_content | text | Pre-formatted social media content |
| copied | boolean | Whether user has copied the content |
| copied_at | timestamp | When the job was marked as copied |
| assigned_at | timestamp | When the job was assigned |

### `shortened_urls`
Custom URL shortening service.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| original_url | text | Full original URL |
| short_code | string(10) | Unique short code |
| clicks | bigint | Number of clicks/redirects |
| created_by | bigint | User who created it |
| expires_at | timestamp | Optional expiration date |

---

## V11 Database Requirements

Your V11 database must have a `posts` table with these fields:

```sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    url VARCHAR(500),
    country_code VARCHAR(2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Field Names Customization

If your V11 `posts` table has different field names, update `App\Models\V11\Post.php`:

```php
// Example: If your table uses 'link' instead of 'url'
protected $casts = [
    'link' => 'string',  // Change field name here
];

// Update the formatForSharing method accordingly
public function formatForSharing($shortUrl)
{
    // Use $this->link instead of $this->url
}
```

---

## Setup Instructions

### 1. **Configure V11 Database Connection**

Your `.env` file should already have V11 database credentials:

```env
V11_DB_HOST=127.0.0.1
V11_DB_PORT=3306
V11_DB_DATABASE=v11
V11_DB_USERNAME=your_username
V11_DB_PASSWORD=your_password
```

### 2. **Assign Countries to Users**

Users must be assigned countries before they can receive job assignments:

1. Navigate to **Settings → Countries**
2. Select a user
3. Assign countries (e.g., US, UK, CA)
4. User will now receive jobs from those countries

### 3. **Run the Scheduler**

The job assignment runs automatically every hour. To enable the scheduler:

**Option A: Cron Job (Recommended for Production)**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Option B: Manual Execution (Testing)**
```bash
php artisan jobs:assign-new
```

**Option C: Keep Scheduler Running (Development)**
```bash
php artisan schedule:work
```

---

## API Endpoints

### Get Job Shares

**GET** `/api/admin/job-shares`

Query parameters:
- `status` - Filter by status: `copied`, `pending`
- `country_code` - Filter by country: `US`, `UK`, etc.
- `per_page` - Items per page (default: 20)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "post_title": "Software Engineer - Remote",
      "country_code": "US",
      "shortened_url": "https://yourdomain.com/s/abc123",
      "formatted_content": "🔥 Software Engineer - Remote\n\nGreat opportunity...",
      "copied": false,
      "assigned_at": "2025-10-24T14:30:00Z",
      "task": {
        "id": 123,
        "title": "Share Job: Software Engineer - Remote"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 50
  }
}
```

### Get Job Share Statistics

**GET** `/api/admin/job-shares/statistics`

Response:
```json
{
  "total_assigned": 150,
  "copied": 120,
  "pending": 30,
  "today_assigned": 10
}
```

### Mark Job as Copied

**POST** `/api/admin/job-shares/{id}/mark-copied`

Response:
```json
{
  "message": "Job marked as copied successfully"
}
```

### Manually Trigger Job Assignment (Admin Only)

**POST** `/api/admin/job-shares/trigger-assignment`

Response:
```json
{
  "message": "Job assignment completed successfully",
  "stats": {
    "posts_fetched": 25,
    "assignments_created": 18,
    "tasks_created": 18,
    "notifications_sent": 18,
    "errors": 0
  }
}
```

### URL Shortener Stats

**GET** `/api/admin/url-shortener/{shortCode}/stats`

Response:
```json
{
  "original_url": "https://example.com/job/12345",
  "short_url": "https://yourdomain.com/s/abc123",
  "clicks": 42,
  "created_at": "2025-10-24T14:00:00Z",
  "expires_at": null,
  "is_expired": false
}
```

---

## Workflow

### 1. **Job Creation in V11 Database**
- A new job is posted in the V11 database `posts` table
- Job must have: `title`, `description`, `url`, `country_code`

### 2. **Automatic Job Assignment (Hourly)**
The scheduler runs every hour and:

1. ✅ Fetches jobs from V11 database (last 8 hours)
2. ✅ Gets all users with country assignments
3. ✅ Matches jobs to users based on country_code
4. ✅ Checks if job already assigned to user (prevents duplicates)
5. ✅ Creates shortened URL for job link
6. ✅ Formats job content for social media
7. ✅ Creates task in "Social Media Tasks" project
8. ✅ Creates `job_shares` record
9. ✅ Sends email notification to user

### 3. **User Receives Notification**
Users receive:
- Email with job details
- In-app notification
- Pre-formatted social media content
- Shortened URL for easy sharing

### 4. **User Completes Task**
1. User opens task in TaskHub
2. Copies the pre-formatted content
3. Shares on social media
4. Marks job as copied (completion)

---

## Testing the System

### 1. **Insert Test Job in V11 Database**

```sql
INSERT INTO posts (title, description, url, country_code, created_at, updated_at)
VALUES (
    'Test Job - Software Engineer',
    'This is a test job posting for a software engineer position.',
    'https://example.com/jobs/12345',
    'US',
    NOW(),
    NOW()
);
```

### 2. **Assign Country to User**

Via API or admin panel:
- Go to Settings → Countries
- Assign "US" to a test user

### 3. **Run Job Assignment Command**

```bash
php artisan jobs:assign-new
```

Expected output:
```
Starting job assignment process...

📊 Assignment Results:
┌──────────────────────────┬───────┐
│ Metric                   │ Count │
├──────────────────────────┼───────┤
│ Posts Fetched            │ 1     │
│ Assignments Created      │ 1     │
│ Tasks Created            │ 1     │
│ Notifications Sent       │ 1     │
│ Errors                   │ 0     │
└──────────────────────────┴───────┘

✅ Successfully assigned 1 job(s) to users

Job assignment process completed successfully!
```

### 4. **Verify Results**

- Check user's email for notification
- Check TaskHub for new task
- Verify database: `SELECT * FROM job_shares;`
- Test shortened URL: `https://yourdomain.com/s/{shortCode}`

---

## Customization

### Change Job Fetch Timeframe

Default: Last 8 hours

Edit `App\Models\V11\Post.php`:

```php
public function scopeRecent($query)
{
    return $query->where('created_at', '>=', now()->subHours(24)); // Change to 24 hours
}
```

### Customize Social Media Format

Edit `App\Models\V11\Post.php`:

```php
public function formatForSharing($shortUrl)
{
    $formattedText = "🚀 {$this->title}\n\n";
    $formattedText .= $this->getShortDescription(200) . "\n\n"; // Longer description
    $formattedText .= "Apply: {$shortUrl}\n\n";
    $formattedText .= "#YourHashtag #Jobs #Career";

    return $formattedText;
}
```

### Change Scheduler Frequency

Edit `App\Console\Kernel.php`:

```php
// Run every 30 minutes
$schedule->command('jobs:assign-new')
         ->everyThirtyMinutes()
         ->withoutOverlapping()
         ->runInBackground();

// Run every 4 hours
$schedule->command('jobs:assign-new')
         ->cron('0 */4 * * *')
         ->withoutOverlapping()
         ->runInBackground();
```

---

## Troubleshooting

### Issue: No jobs being assigned

**Check:**
1. V11 database connection is working:
   ```bash
   php artisan tinker
   >>> App\Models\V11\Post::count()
   ```

2. Users have countries assigned:
   ```bash
   php artisan tinker
   >>> App\Models\UserCountry::count()
   ```

3. Recent posts exist:
   ```bash
   php artisan tinker
   >>> App\Models\V11\Post::recent()->count()
   ```

4. Scheduler is running:
   ```bash
   php artisan schedule:list
   ```

### Issue: Shortened URLs not working

**Check:**
1. Route is registered:
   ```bash
   php artisan route:list | grep "/s/"
   ```

2. URL format is correct: `https://yourdomain.com/s/{shortCode}`

### Issue: Users not receiving notifications

**Check:**
1. Mail configuration in `.env`
2. Queue is running (if using queues):
   ```bash
   php artisan queue:work
   ```

3. User has verified email address

### Issue: Duplicate job assignments

This shouldn't happen due to unique constraint, but if it does:
```sql
-- Check for duplicates
SELECT user_id, v11_post_id, COUNT(*)
FROM job_shares
GROUP BY user_id, v11_post_id
HAVING COUNT(*) > 1;
```

---

## Monitoring

### View Assignment Logs

```bash
tail -f storage/logs/laravel.log | grep "Job assigned"
```

### Database Queries

```sql
-- Jobs assigned today
SELECT COUNT(*) FROM job_shares WHERE DATE(assigned_at) = CURDATE();

-- Jobs by user
SELECT u.name, COUNT(js.id) as total_jobs, SUM(js.copied) as copied_jobs
FROM users u
LEFT JOIN job_shares js ON u.id = js.user_id
GROUP BY u.id, u.name;

-- Most clicked short URLs
SELECT short_code, original_url, clicks
FROM shortened_urls
ORDER BY clicks DESC
LIMIT 10;

-- Pending jobs by country
SELECT country_code, COUNT(*) as pending_jobs
FROM job_shares
WHERE copied = 0
GROUP BY country_code;
```

---

## Production Checklist

- [ ] V11 database credentials configured in `.env`
- [ ] Migrations run successfully
- [ ] Cron job added for Laravel scheduler
- [ ] Mail server configured for notifications
- [ ] Queue worker running (if using queues)
- [ ] Countries assigned to users
- [ ] Test job assignment verified
- [ ] Logs monitoring setup
- [ ] Error tracking configured (e.g., Sentry)

---

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Run diagnostics: `php artisan jobs:assign-new --verbose`
3. Review this documentation
4. Check database connections and data

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         V11 Database                             │
│                    (Secondary Database)                          │
│                                                                   │
│  ┌──────────────────────────────────────────────────────┐       │
│  │              posts table                              │       │
│  │  • id, title, description, url, country_code         │       │
│  │  • created_at (last 8 hours)                         │       │
│  └──────────────────────────────────────────────────────┘       │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ Fetch jobs hourly
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│              JobAssignmentService                                │
│  • Fetches recent posts from V11                                │
│  • Matches with users based on country                          │
│  • Prevents duplicate assignments                               │
│  • Creates tasks and notifications                              │
└───────────────────────┬─────────────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│  Create Task │ │ Shorten URL  │ │ Send Notify  │
│              │ │              │ │              │
│ tasks table  │ │shortened_urls│ │ Email + DB   │
└──────────────┘ └──────────────┘ └──────────────┘
        │               │               │
        └───────────────┼───────────────┘
                        │
                        ▼
        ┌──────────────────────────────┐
        │      job_shares table        │
        │  • Links users to jobs       │
        │  • Tracks completion status  │
        │  • Stores formatted content  │
        └──────────────────────────────┘
                        │
                        ▼
        ┌──────────────────────────────┐
        │          User Tasks          │
        │  • View formatted content    │
        │  • Copy & share on social    │
        │  • Mark as copied            │
        └──────────────────────────────┘
```

---

## License

This feature is part of the TaskHub2025 project.
