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
        // Get school name from general settings (Site Name)
        $siteName = WebsiteSetting::get('site_name', WebsiteSetting::get('school_name', 'ROSHS Portal'));
        $shortName = WebsiteSetting::get('site_short_name', 'ROSHS');
        $description = WebsiteSetting::get('site_description', 'Student Portal');
        $themeColor = WebsiteSetting::get('primary_color', '#0f172a');
        $backgroundColor = WebsiteSetting::get('background_color', '#ffffff');
        
        // Get logo from website images settings
        $logo = WebsiteSetting::get('site_logo', 'images/logo.png');
        
        // Convert relative path to absolute URL using asset()
        $logoUrl = asset($logo);
        
        // Determine image type based on extension
        $extension = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        $imageType = $mimeTypes[$extension] ?? 'image/png';
        
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
                    'type' => $imageType,
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $logoUrl,
                    'sizes' => '512x512',
                    'type' => $imageType,
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }
}
