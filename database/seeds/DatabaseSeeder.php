<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SuperAdminSeeder::class);
        // $this->call(StudentParentDemoSeeder::class); // Commented out - don't create demo students

        $user = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name'          => 'Admin',
                'password'      => bcrypt('codeastro.com'),
                'created_at'    => date("Y-m-d H:i:s")
            ]
        );
        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        $user2 = User::firstOrCreate(
            ['email' => 'teacher@mail.com'],
            [
                'name'          => 'Teacher',
                'password'      => bcrypt('codeastro.com'),
                'created_at'    => date("Y-m-d H:i:s")
            ]
        );
        if (!$user2->hasRole('Teacher')) {
            $user2->assignRole('Teacher');
        }

        $user3 = User::firstOrCreate(
            ['email' => 'parent@mail.com'],
            [
                'name'          => 'Parent',
                'password'      => bcrypt('codeastro.com'),
                'created_at'    => date("Y-m-d H:i:s")
            ]
        );
        if (!$user3->hasRole('Parent')) {
            $user3->assignRole('Parent');
        }

        $user4 = User::firstOrCreate(
            ['email' => 'student@mail.com'],
            [
                'name'          => 'Student',
                'password'      => bcrypt('codeastro.com'),
                'created_at'    => date("Y-m-d H:i:s")
            ]
        );
        if (!$user4->hasRole('Student')) {
            $user4->assignRole('Student');
        }


        if (!DB::table('teachers')->where('user_id', $user2->id)->exists()) {
            DB::table('teachers')->insert([
                [
                    'user_id'           => $user2->id,
                    'gender'            => 'male',
                    'phone'             => '6969540014',
                    'dateofbirth'       => '1990-04-11',
                    'current_address'   => '63 Walnut Hill Drive',
                    'permanent_address' => '385 Emma Street',
                    'created_at'        => date("Y-m-d H:i:s")
                ]
            ]);
        }

        if (!DB::table('parents')->where('user_id', $user3->id)->exists()) {
            DB::table('parents')->insert([
                [
                    'user_id'           => $user3->id,
                    'gender'            => 'male',
                    'phone'             => '0147854545',
                    'current_address'   => '46 Custer Street',
                    'permanent_address' => '46 Custer Street',
                    'created_at'        => date("Y-m-d H:i:s")
                ]
            ]);
        }

        if (!DB::table('grades')->where('class_name', 'One')->exists()) {
            DB::table('grades')->insert([
                'teacher_id'        => 1,
                'class_numeric'     => 1,
                'class_name'        => 'One',
                'class_description' => 'class one'
            ]);
        }

        if (!DB::table('students')->where('user_id', $user4->id)->exists()) {
            DB::table('students')->insert([
                [
                    'user_id'           => $user4->id,
                    'parent_id'         => 1,
                    'class_id'          => 1,
                    'roll_number'       => 1,
                    'gender'            => 'male',
                    'phone'             => '7801256654',
                    'dateofbirth'       => '2007-04-11',
                    'current_address'   => '103 Pine Tree Lane',
                    'permanent_address' => '103 Pine Tree Lane',
                    'created_at'        => date("Y-m-d H:i:s")
                ]
            ]);
        }

    }
}
