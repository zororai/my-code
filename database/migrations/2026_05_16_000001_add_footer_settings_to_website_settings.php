<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFooterSettingsToWebsiteSettings extends Migration
{
    public function up()
    {
        $settings = [
            [
                'key' => 'footer_vision_text',
                'value' => 'Our Vision is provide a well-groomed, enriched (in ideas) and productive learner given a firm foundation for tertiary and life challenges.',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Footer Vision Text',
                'description' => 'Vision statement shown in the footer',
                'order' => 20,
            ],
            [
                'key' => 'footer_quick_link_1',
                'value' => 'Join Us',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Quick Link 1',
                'description' => 'First quick link label in footer',
                'order' => 21,
            ],
            [
                'key' => 'footer_quick_link_2',
                'value' => 'Maintenance',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Quick Link 2',
                'description' => 'Second quick link label in footer',
                'order' => 22,
            ],
            [
                'key' => 'footer_quick_link_3',
                'value' => 'Language Packs',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Quick Link 3',
                'description' => 'Third quick link label in footer',
                'order' => 23,
            ],
            [
                'key' => 'footer_quick_link_4',
                'value' => 'LearnPress',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Quick Link 4',
                'description' => 'Fourth quick link label in footer',
                'order' => 24,
            ],
            [
                'key' => 'footer_quick_link_5',
                'value' => 'Release Status',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Quick Link 5',
                'description' => 'Fifth quick link label in footer',
                'order' => 25,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('website_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Move footer_logo to the general group so it appears in general settings
        DB::table('website_settings')
            ->where('key', 'footer_logo')
            ->update(['group' => 'general', 'order' => 10, 'updated_at' => now()]);
    }

    public function down()
    {
        DB::table('website_settings')->whereIn('key', [
            'footer_vision_text',
            'footer_quick_link_1',
            'footer_quick_link_2',
            'footer_quick_link_3',
            'footer_quick_link_4',
            'footer_quick_link_5',
        ])->delete();

        DB::table('website_settings')
            ->where('key', 'footer_logo')
            ->update(['group' => 'images', 'order' => 3, 'updated_at' => now()]);
    }
}
