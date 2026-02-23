<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Student;
use App\Parents;
use App\Grade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentParentDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing classes
        $classes = Grade::all();
        
        if ($classes->isEmpty()) {
            $this->command->info('No classes found. Please create classes first.');
            return;
        }

        // Demo student data
        $demoStudents = [
            [
                'name' => 'John Smith',
                'gender' => 'male',
                'dateofbirth' => '2008-03-15',
                'phone' => '+27712345678',
                'student_type' => 'day',
                'chair' => 'A12',
                'desk' => 'D15',
                'parents' => [
                    ['name' => 'Robert Smith', 'phone' => '+27712345679', 'gender' => 'male'],
                    ['name' => 'Mary Smith', 'phone' => '+27712345680', 'gender' => 'female']
                ]
            ],
            [
                'name' => 'Sarah Johnson',
                'gender' => 'female',
                'dateofbirth' => '2009-07-22',
                'phone' => '+27712345681',
                'student_type' => 'boarding',
                'chair' => 'B08',
                'desk' => 'D22',
                'parents' => [
                    ['name' => 'David Johnson', 'phone' => '+27712345682', 'gender' => 'male']
                ]
            ],
            [
                'name' => 'Michael Brown',
                'gender' => 'male',
                'dateofbirth' => '2008-11-10',
                'phone' => '+27712345683',
                'student_type' => 'day',
                'chair' => 'C15',
                'desk' => 'D08',
                'parents' => [
                    ['name' => 'James Brown', 'phone' => '+27712345684', 'gender' => 'male'],
                    ['name' => 'Linda Brown', 'phone' => '+27712345685', 'gender' => 'female']
                ]
            ],
            [
                'name' => 'Emily Davis',
                'gender' => 'female',
                'dateofbirth' => '2009-02-28',
                'phone' => '+27712345686',
                'student_type' => 'boarding',
                'chair' => 'A20',
                'desk' => 'D18',
                'parents' => [
                    ['name' => 'William Davis', 'phone' => '+27712345687', 'gender' => 'male'],
                    ['name' => 'Patricia Davis', 'phone' => '+27712345688', 'gender' => 'female']
                ]
            ],
            [
                'name' => 'Daniel Wilson',
                'gender' => 'male',
                'dateofbirth' => '2008-09-05',
                'phone' => '+27712345689',
                'student_type' => 'day',
                'chair' => 'B11',
                'desk' => 'D25',
                'parents' => [
                    ['name' => 'Thomas Wilson', 'phone' => '+27712345690', 'gender' => 'male']
                ]
            ],
            [
                'name' => 'Sophia Martinez',
                'gender' => 'female',
                'dateofbirth' => '2009-05-18',
                'phone' => '+27712345691',
                'student_type' => 'boarding',
                'chair' => 'C07',
                'desk' => 'D12',
                'parents' => [
                    ['name' => 'Carlos Martinez', 'phone' => '+27712345692', 'gender' => 'male'],
                    ['name' => 'Maria Martinez', 'phone' => '+27712345693', 'gender' => 'female']
                ]
            ],
            [
                'name' => 'Alexander Taylor',
                'gender' => 'male',
                'dateofbirth' => '2008-12-01',
                'phone' => '+27712345694',
                'student_type' => 'day',
                'chair' => 'A03',
                'desk' => 'D20',
                'parents' => [
                    ['name' => 'Christopher Taylor', 'phone' => '+27712345695', 'gender' => 'male']
                ]
            ],
            [
                'name' => 'Olivia Anderson',
                'gender' => 'female',
                'dateofbirth' => '2009-08-14',
                'phone' => '+27712345696',
                'student_type' => 'boarding',
                'chair' => 'B18',
                'desk' => 'D05',
                'parents' => [
                    ['name' => 'Matthew Anderson', 'phone' => '+27712345697', 'gender' => 'male'],
                    ['name' => 'Jennifer Anderson', 'phone' => '+27712345698', 'gender' => 'female']
                ]
            ]
        ];

        $this->command->info('Creating demo students and parents...');

        foreach ($demoStudents as $index => $studentData) {
            // Generate roll number
            $rollNumber = 'RSH' . str_pad($index + 1001, 4, '0', STR_PAD_LEFT);
            $studentEmail = strtolower($rollNumber) . '@roshs.co.zw';
            
            // Skip if student already exists
            if (User::where('email', $studentEmail)->exists()) {
                $this->command->info("Skipping existing student: {$studentData['name']} (Roll: {$rollNumber})");
                continue;
            }
            
            // Create student user
            $studentUser = User::create([
                'name' => $studentData['name'],
                'email' => strtolower($rollNumber) . '@roshs.co.zw',
                'password' => Hash::make('12345678'),
                'profile_picture' => 'avatar.png'
            ]);

            // Assign random class
            $randomClass = $classes->random();

            // Create student record
            $student = Student::create([
                'user_id' => $studentUser->id,
                'class_id' => $randomClass->id,
                'roll_number' => $rollNumber,
                'gender' => $studentData['gender'],
                'phone' => $studentData['phone'],
                'dateofbirth' => $studentData['dateofbirth'],
                'current_address' => '123 Demo Street, Harare, Zimbabwe',
                'permanent_address' => '123 Demo Street, Harare, Zimbabwe',
                'student_type' => $studentData['student_type'],
                'chair' => $studentData['chair'],
                'desk' => $studentData['desk'],
            ]);

            // Create parents and link to student
            foreach ($studentData['parents'] as $parentIndex => $parentData) {
                // Generate registration token
                $registrationToken = Str::random(60);
                $tokenExpiresAt = now()->addDays(7);

                // Create temporary email for parent
                $tempEmail = 'pending_' . time() . '_' . $parentIndex . '_' . $student->id . '@temp.parent';

                // Create parent user
                $parentUser = User::create([
                    'name' => $parentData['name'],
                    'email' => $tempEmail,
                    'password' => Hash::make(Str::random(16)),
                    'profile_picture' => 'avatar.png'
                ]);

                // Create parent record
                $parent = Parents::create([
                    'user_id' => $parentUser->id,
                    'gender' => $parentData['gender'],
                    'phone' => $parentData['phone'],
                    'current_address' => '123 Demo Street, Harare, Zimbabwe',
                    'permanent_address' => '123 Demo Street, Harare, Zimbabwe',
                    'registration_token' => $registrationToken,
                    'token_expires_at' => $tokenExpiresAt,
                    'registration_completed' => false,
                ]);

                // Link parent to student (many-to-many relationship)
                $student->parents()->attach($parent->id);
            }

            $this->command->info("Created student: {$studentData['name']} (Roll: {$rollNumber})");
        }

        $this->command->info('Demo data creation completed!');
        $this->command->info('Students can login with email: rollnumber@roshs.co.zw, password: 12345678');
        $this->command->info('Parents need to complete registration using their phone numbers.');
    }
}
