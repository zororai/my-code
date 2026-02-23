<?php

namespace App\Http\Controllers;

use App\WebsiteSetting;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Generate dynamic PWA manifest
     */
    public function manifest()
    {
        // Get school name from general settings (School Name (Header))
        $siteName = WebsiteSetting::get('school_name', 'ROSHS Portal');
        $shortName = WebsiteSetting::get('site_short_name', 'ROSHS');
        $description = WebsiteSetting::get('site_description', 'Rose Of Sharon High School Student Portal');
        $themeColor = WebsiteSetting::get('primary_color', '#0f172a');
        $backgroundColor = WebsiteSetting::get('background_color', '#ffffff');
        
        // Get logo from website images settings
        $logo = WebsiteSetting::get('site_logo', 'images/logo.png');
        
        // Convert relative path to absolute URL
        $logoUrl = url($logo);
        
        $manifest = [
            'name' => $siteName,
            'short_name' => $shortName,
            'start_url' => '/logins',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'description' => $description,
            'icons' => [
                [
                    'src' => $logoUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $logoUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }
}
