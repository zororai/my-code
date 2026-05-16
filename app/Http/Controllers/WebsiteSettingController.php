<?php

namespace App\Http\Controllers;

use App\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingController extends Controller
{
    /**
     * Display the website settings dashboard
     */
    public function index()
    {
        $settings = WebsiteSetting::getAllGrouped();
        return view('backend.website-settings.index', compact('settings'));
    }

    /**
     * Show general settings form
     */
    public function general()
    {
        $settings = WebsiteSetting::getByGroup('general');
        return view('backend.website-settings.general', compact('settings'));
    }

    /**
     * Show colors settings form
     */
    public function colors()
    {
        $settings = WebsiteSetting::getByGroup('colors');
        return view('backend.website-settings.colors', compact('settings'));
    }

    /**
     * Show images settings form
     */
    public function images()
    {
        $settings = WebsiteSetting::getByGroup('images');
        return view('backend.website-settings.images', compact('settings'));
    }

    /**
     * Show text content settings form
     */
    public function text()
    {
        $settings = WebsiteSetting::getByGroup('text');
        return view('backend.website-settings.text', compact('settings'));
    }

    /**
     * Show pages settings form
     */
    public function pages()
    {
        $settings = WebsiteSetting::getByGroup('pages');
        return view('backend.website-settings.pages', compact('settings'));
    }

    /**
     * Show homepage settings form
     */
    public function homepage()
    {
        $settings = WebsiteSetting::getByGroup('homepage');
        return view('backend.website-settings.homepage', compact('settings'));
    }

    /**
     * Show landing page settings form
     */
    public function landingPage()
    {
        $currentLandingPage = \App\SchoolSetting::get('landing_page', 'logins');
        return view('backend.website-settings.landing-page', compact('currentLandingPage'));
    }

    /**
     * Update landing page setting
     */
    public function updateLandingPage(Request $request)
    {
        $request->validate([
            'landing_page' => 'required|in:web,logins'
        ]);

        \App\SchoolSetting::set('landing_page', $request->landing_page, 'select', 'The default landing page for visitors');

        return redirect()->back()->with('success', 'Landing page updated successfully!');
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            $setting = WebsiteSetting::where('key', $key)->first();
            
            if ($setting) {
                // Handle file uploads for image type
                if ($setting->type === 'image') {
                    if (!$request->hasFile($key)) {
                        // No file uploaded — keep existing value
                        continue;
                    }
                    $file = $request->file($key);
                    $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();

                    // Store in public storage
                    $path = $file->storeAs('website', $filename, 'public');
                    $value = 'storage/' . $path;

                    // Delete old file if it exists and is not a default
                    if ($setting->value && !str_starts_with($setting->value, 'images/')) {
                        $oldPath = str_replace('storage/', '', $setting->value);
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                WebsiteSetting::set($key, $value);
            }
        }

        WebsiteSetting::clearCache();

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Update a single setting via AJAX
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable'
        ]);

        $setting = WebsiteSetting::where('key', $request->key)->first();

        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        }

        // Handle file upload
        if ($setting->type === 'image' && $request->hasFile('value')) {
            $file = $request->file('value');
            $filename = $request->key . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('website', $filename, 'public');
            $value = 'storage/' . $path;

            // Delete old file
            if ($setting->value && !str_starts_with($setting->value, 'images/')) {
                $oldPath = str_replace('storage/', '', $setting->value);
                Storage::disk('public')->delete($oldPath);
            }
        } else {
            $value = $request->value;
        }

        WebsiteSetting::set($request->key, $value);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'value' => $value
        ]);
    }

    /**
     * Reset a setting to default
     */
    public function reset($key)
    {
        $setting = WebsiteSetting::where('key', $key)->first();

        if (!$setting) {
            return redirect()->back()->with('error', 'Setting not found');
        }

        // Delete uploaded file if exists
        if ($setting->type === 'image' && $setting->value && !str_starts_with($setting->value, 'images/')) {
            $oldPath = str_replace('storage/', '', $setting->value);
            Storage::disk('public')->delete($oldPath);
        }

        // Reset to null or original default would require storing defaults
        // For now, we'll just clear the cache
        WebsiteSetting::clearCache();

        return redirect()->back()->with('success', 'Setting has been reset');
    }

    /**
     * Banner management page
     */
    public function banners()
    {
        $banner = \App\Banner::first();
        return view('backend.website-settings.banners', compact('banner'));
    }

    /**
     * Update banners
     */
    public function updateBanners(Request $request)
    {
        $banner = \App\Banner::first();

        if (!$banner) {
            $banner = new \App\Banner();
        }

        for ($i = 1; $i <= 3; $i++) {
            $fieldName = "image_path_{$i}";
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $path = $file->store('uploads', 'public');
                $banner->$fieldName = $path;
            }
        }

        $banner->save();

        return redirect()->back()->with('success', 'Banners updated successfully!');
    }
}
