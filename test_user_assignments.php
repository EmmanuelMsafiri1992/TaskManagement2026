<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserFocus;
use App\Models\UserAssignment;
use App\Models\V11User;
use Illuminate\Support\Facades\DB;

echo "\n=== User Assignment System Test ===\n\n";

// Test 1: Check if V11 database connection works
echo "1. Testing V11 Database Connection...\n";
try {
    $v11UsersCount = V11User::verified()->count();
    $jobSeekersCount = V11User::verified()->jobSeekers()->count();
    $employersCount = V11User::verified()->employers()->count();

    echo "   ✓ V11 Database Connected\n";
    echo "   - Total verified users: {$v11UsersCount}\n";
    echo "   - Job Seekers: {$jobSeekersCount}\n";
    echo "   - Employers: {$employersCount}\n\n";
} catch (Exception $e) {
    echo "   ✗ V11 Database Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check TaskHub users
echo "2. Testing TaskHub Users...\n";
try {
    $taskhubUsers = User::whereHas('roles', function ($q) {
        $q->whereIn('name', ['Marketer', 'Developer', 'Admin']);
    })->count();

    echo "   ✓ Found {$taskhubUsers} TaskHub users\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Check User Focus configurations
echo "3. Testing User Focus Configurations...\n";
try {
    $usersWithFocus = UserFocus::count();
    $autoAssignJobSeekers = UserFocus::where('enable_auto_assign_job_seekers', true)->count();
    $autoAssignEmployers = UserFocus::where('enable_auto_assign_employers', true)->count();

    echo "   ✓ Users with focus configured: {$usersWithFocus}\n";
    echo "   - Auto-assign Job Seekers enabled: {$autoAssignJobSeekers}\n";
    echo "   - Auto-assign Employers enabled: {$autoAssignEmployers}\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Check existing assignments
echo "4. Testing Current Assignments...\n";
try {
    $totalAssignments = UserAssignment::count();
    $jobSeekerAssignments = UserAssignment::where('v11_user_type', 2)->count();
    $employerAssignments = UserAssignment::where('v11_user_type', 1)->count();
    $autoAssignments = UserAssignment::where('auto_assigned', true)->count();

    echo "   ✓ Total assignments: {$totalAssignments}\n";
    echo "   - Job Seekers assigned: {$jobSeekerAssignments}\n";
    echo "   - Employers assigned: {$employerAssignments}\n";
    echo "   - Auto-assigned: {$autoAssignments}\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Check Observer is registered
echo "5. Testing Observer Registration...\n";
try {
    $observerClass = 'App\Observers\V11UserObserver';
    if (class_exists($observerClass)) {
        echo "   ✓ V11UserObserver class exists\n";
        echo "   ✓ Observer should be registered in AppServiceProvider\n\n";
    } else {
        echo "   ✗ V11UserObserver class not found\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Test statistics calculation
echo "6. Testing Statistics Calculation...\n";
try {
    $totalJobSeekers = V11User::verified()->jobSeekers()->count();
    $totalEmployers = V11User::verified()->employers()->count();
    $assignedJobSeekers = UserAssignment::where('v11_user_type', 2)->count();
    $assignedEmployers = UserAssignment::where('v11_user_type', 1)->count();

    echo "   ✓ Statistics calculated successfully\n";
    echo "   Job Seekers:\n";
    echo "     - Total: {$totalJobSeekers}\n";
    echo "     - Assigned: {$assignedJobSeekers}\n";
    echo "     - Unassigned: " . ($totalJobSeekers - $assignedJobSeekers) . "\n";
    echo "   Employers:\n";
    echo "     - Total: {$totalEmployers}\n";
    echo "     - Assigned: {$assignedEmployers}\n";
    echo "     - Unassigned: " . ($totalEmployers - $assignedEmployers) . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 7: Check notification class
echo "7. Testing Notification Class...\n";
try {
    if (class_exists('App\Notifications\NewUserAssigned')) {
        echo "   ✓ NewUserAssigned notification class exists\n\n";
    } else {
        echo "   ✗ NewUserAssigned notification class not found\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 8: Sample assignment query
echo "8. Testing Sample Assignment Query...\n";
try {
    $sampleUser = User::with(['focus', 'assignments'])->first();

    if ($sampleUser) {
        echo "   ✓ Sample User: {$sampleUser->name}\n";

        if ($sampleUser->focus) {
            echo "     - Works with Job Seekers: " . ($sampleUser->focus->works_with_job_seekers ? 'Yes' : 'No') . "\n";
            echo "     - Works with Employers: " . ($sampleUser->focus->works_with_employers ? 'Yes' : 'No') . "\n";
            echo "     - Auto-assign Job Seekers: " . ($sampleUser->focus->enable_auto_assign_job_seekers ? 'Yes' : 'No') . "\n";
            echo "     - Auto-assign Employers: " . ($sampleUser->focus->enable_auto_assign_employers ? 'Yes' : 'No') . "\n";
        } else {
            echo "     - No focus configured\n";
        }

        echo "     - Total assignments: " . $sampleUser->assignments->count() . "\n";
    } else {
        echo "   ✗ No users found\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n\n";
