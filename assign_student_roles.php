<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Student;
use Spatie\Permission\Models\Role;

// Get the Student role
$studentRole = Role::where('name', 'Student')->first();

if (!$studentRole) {
    echo "Error: Student role not found!\n";
    exit(1);
}

// Get all students with their users
$students = Student::with('user')->get();

$count = 0;
$alreadyHasRole = 0;

foreach ($students as $student) {
    if ($student->user) {
        if (!$student->user->hasRole('Student')) {
            $student->user->assignRole($studentRole);
            $count++;
            echo "Assigned Student role to: {$student->user->name}\n";
        } else {
            $alreadyHasRole++;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Total students: " . $students->count() . "\n";
echo "Assigned Student role to: {$count} students\n";
echo "Already had Student role: {$alreadyHasRole} students\n";
echo "\nDone!\n";
