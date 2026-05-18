<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCoursesPageContentSettings extends Migration
{
    public function up()
    {
        $settings = [
            [
                'key' => 'courses_banner_title',
                'value' => 'Our Subjects',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'Courses Banner Title',
                'description' => 'Title shown in the page banner',
                'order' => 21
            ],
            [
                'key' => 'courses_olevel_title',
                'value' => "'O' Level Subjects",
                'type' => 'text',
                'group' => 'pages',
                'label' => 'O-Level Section Title',
                'description' => 'Heading for the O-Level subjects section',
                'order' => 22
            ],
            [
                'key' => 'courses_olevel_image',
                'value' => 'images/img-7.png',
                'type' => 'image',
                'group' => 'pages',
                'label' => 'O-Level Section Image',
                'description' => 'Image displayed beside the O-Level subjects',
                'order' => 23
            ],
            [
                'key' => 'courses_olevel_subjects',
                'value' => "Geography\nMathematics\nBiology\nEnglish Language\nPrinciples of Accounts\nChemistry\nFamily and Religious Studies\nHeritage Studies\nCommerce\nComputers\nShona\nPhysical Science",
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'O-Level Subjects (one per line)',
                'description' => 'List of O-Level subjects, enter one subject per line',
                'order' => 24
            ],
            [
                'key' => 'courses_alevel_title',
                'value' => "'A' Level Subjects",
                'type' => 'text',
                'group' => 'pages',
                'label' => 'A-Level Section Title',
                'description' => 'Heading for the A-Level subjects section',
                'order' => 25
            ],
            [
                'key' => 'courses_alevel_subjects_col1',
                'value' => "History\nEconomic History\nShona Literature\nShona Language\nPure Mathematics\nBiology\nPhysics\nChemistry",
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'A-Level Subjects — Column 1 (one per line)',
                'description' => 'First column of A-Level subjects, one per line',
                'order' => 26
            ],
            [
                'key' => 'courses_alevel_subjects_col2',
                'value' => "Geography\nEconomics\nBusiness Studies\nAccounting\nStatistics\nSociology\nLiterature in English\nHeritage Studies\nFamily and Religious Studies",
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'A-Level Subjects — Column 2 (one per line)',
                'description' => 'Second column of A-Level subjects, one per line',
                'order' => 27
            ],
            [
                'key' => 'courses_history_title',
                'value' => 'Academy History',
                'type' => 'text',
                'group' => 'pages',
                'label' => 'Academy History Title',
                'description' => 'Title for the Academy History section',
                'order' => 28
            ],
            [
                'key' => 'courses_history_text_1',
                'value' => 'Then Rose Of Sharon High School will know that he is the Lord God who lives in Zion his holy mountain, Rose of Sharon High School will remain forever and foreign mountains will drip with sweet wine and the hills will flow with milk,',
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'Academy History Text — Part 1',
                'description' => 'First paragraph of the history section',
                'order' => 29
            ],
            [
                'key' => 'courses_history_text_2',
                'value' => 'water will fill the streambeds of Rose of Sharon High School and the fountain will burst forth from the Lord\'s temple watering the arid valleys of Rose of Sharon High School. Rose of Sharon High School will remain forever and Rose of Sharon High School will endure through all future generations.',
                'type' => 'textarea',
                'group' => 'pages',
                'label' => 'Academy History Text — Part 2',
                'description' => 'Second paragraph of the history section',
                'order' => 30
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('website_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('website_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    public function down()
    {
        $keys = [
            'courses_banner_title', 'courses_olevel_title', 'courses_olevel_image',
            'courses_olevel_subjects', 'courses_alevel_title', 'courses_alevel_subjects_col1',
            'courses_alevel_subjects_col2', 'courses_history_title',
            'courses_history_text_1', 'courses_history_text_2',
        ];

        DB::table('website_settings')->whereIn('key', $keys)->delete();
    }
}
