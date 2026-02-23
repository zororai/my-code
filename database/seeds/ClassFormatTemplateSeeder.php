<?php

use Illuminate\Database\Seeder;
use App\ClassFormatTemplate;

class ClassFormatTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name' => 'Color Names',
                'type' => 'names',
                'values' => 'Blue, White, Green, Red, Yellow, Orange',
                'description' => 'Standard color-based class names',
                'is_active' => true,
            ],
            [
                'name' => 'Letters (A-F)',
                'type' => 'custom',
                'values' => 'A, B, C, D, E, F',
                'description' => 'Alphabetical class naming',
                'is_active' => true,
            ],
            [
                'name' => 'Greek Letters',
                'type' => 'custom',
                'values' => 'Alpha, Beta, Gamma, Delta, Epsilon',
                'description' => 'Greek letter class names',
                'is_active' => true,
            ],
            [
                'name' => 'Numeric (3 classes)',
                'type' => 'numeric',
                'values' => '3',
                'description' => 'Creates .1, .2, .3 format',
                'is_active' => true,
            ],
            [
                'name' => 'Numeric (4 classes)',
                'type' => 'numeric',
                'values' => '4',
                'description' => 'Creates .1, .2, .3, .4 format',
                'is_active' => true,
            ],
            [
                'name' => 'Numeric (5 classes)',
                'type' => 'numeric',
                'values' => '5',
                'description' => 'Creates .1, .2, .3, .4, .5 format',
                'is_active' => true,
            ],
            [
                'name' => 'Streams',
                'type' => 'custom',
                'values' => 'Science, Arts, Commerce',
                'description' => 'Academic stream divisions',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            ClassFormatTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Class format templates seeded successfully!');
    }
}
