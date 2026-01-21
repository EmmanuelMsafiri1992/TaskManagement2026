<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Testing Database Connection ===\n";
try {
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Testing All Models ===\n";

$models = [
    'App\Models\User',
    'App\Models\Task',
    'App\Models\Project',
    'App\Models\ProjectList',
    'App\Models\Comment',
    'App\Models\Attachment',
    'App\Models\Attendance',
    'App\Models\Checklist',
    'App\Models\ChecklistItem',
    'App\Models\FavoriteProject',
    'App\Models\Invitation',
    'App\Models\JobShare',
    'App\Models\Label',
    'App\Models\Option',
    'App\Models\Permission',
    'App\Models\Priority',
    'App\Models\RecentProject',
    'App\Models\Role',
    'App\Models\ShortenedUrl',
    'App\Models\TaskCompletion',
    'App\Models\TimeLog',
    'App\Models\UserAssignment',
    'App\Models\UserFocus',
    'App\Models\Website',
    'App\Models\EmployeeRecord',
    'App\Models\Leave',
    'App\Models\Holiday',
    'App\Models\Payroll',
    'App\Models\PayrollItem',
    'App\Models\Client',
    'App\Models\Quotation',
    'App\Models\QuotationItem',
    'App\Models\Expense',
    'App\Models\Income',
    'App\Models\GeneratorFuel',
    'App\Models\GeneratorFuelLog',
    'App\Models\UserCountry',
    'App\Models\UserWebsite',
    'App\Models\UserTarget',
    'App\Models\VideoEnhancer',
    'App\Models\AuditTrail',
    'App\Models\AdSenseReport',
    'App\Models\ServiceProvider',
    'App\Models\ServiceProviderAgreement',
    'App\Models\Subject',
    'App\Models\Topic',
    'App\Models\RecordingSession',
    'App\Models\LessonPlan',
    'App\Models\SessionResource',
    'App\Models\Payment',
    'App\Models\UserWorkingHours',
    'App\Models\InactivityReport',
    'App\Models\UserActivityLog',
    'App\Models\UserActivitySession',
    'App\Models\Lead',
    'App\Models\LeadActivity',
    'App\Models\LeadEmailTemplate',
    'App\Models\AdvanceRequest',
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($models as $modelClass) {
    try {
        if (!class_exists($modelClass)) {
            throw new Exception("Class not found");
        }

        $model = new $modelClass();
        $table = $model->getTable();

        if (!Schema::hasTable($table)) {
            throw new Exception("Table '{$table}' does not exist");
        }

        // Try to run a count query
        $count = $modelClass::count();

        $shortName = class_basename($modelClass);
        echo "✓ {$shortName} (table: {$table}, records: {$count})\n";
        $passed++;
    } catch (Exception $e) {
        $shortName = class_basename($modelClass);
        echo "✗ {$shortName}: " . $e->getMessage() . "\n";
        $errors[] = "{$shortName}: " . $e->getMessage();
        $failed++;
    }
}

echo "\n=== Model Test Results ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if (count($errors) > 0) {
    echo "\n=== Errors ===\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}

// Overall Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "=== SUMMARY ===\n";
echo str_repeat("=", 50) . "\n";
$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
echo "Total Models Tested: {$total}\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Success Rate: {$percentage}%\n";
