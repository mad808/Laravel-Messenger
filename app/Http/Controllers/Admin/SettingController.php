<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:120',
            'site_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'site_favicon' => 'nullable|file|mimes:ico,png,svg|max:1024',
            'contact_phone' => 'nullable|string|max:40',
            'contact_address' => 'nullable|string|max:500',
            'trans_site_name_en' => 'nullable|string|max:120',
            'trans_site_name_tk' => 'nullable|string|max:120',
            'trans_site_name_ru' => 'nullable|string|max:120',
            'trans_address_en' => 'nullable|string|max:500',
            'trans_address_tk' => 'nullable|string|max:500',
            'trans_address_ru' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('site_logo')) {
            $oldLogo = Setting::where('key', 'site_logo')->value('value');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $logoPath, 'type' => 'image']);
        }

        if ($request->hasFile('site_favicon')) {
            $oldFavicon = Setting::where('key', 'site_favicon')->value('value');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('site_favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'site_favicon'], ['value' => $faviconPath, 'type' => 'image']);
        }

        Setting::updateOrCreate(
            ['key' => 'site_name'],
            [
                'value' => trim((string) $request->site_name),
                'en_name' => $request->input('trans_site_name_en'),
                'tk_name' => $request->input('trans_site_name_tk'),
                'ru_name' => $request->input('trans_site_name_ru'),
                'type' => 'text',
            ]
        );

        if ($request->has('contact_phone')) {
            Setting::updateOrCreate(
                ['key' => 'contact_phone'],
                ['value' => $request->input('contact_phone'), 'type' => 'text']
            );
        }

        if ($request->has('contact_address') || $request->has('trans_address_en')) {
            Setting::updateOrCreate(
                ['key' => 'contact_address'],
                [
                    'value' => $request->input('contact_address'),
                    'en_name' => $request->input('trans_address_en'),
                    'tk_name' => $request->input('trans_address_tk'),
                    'ru_name' => $request->input('trans_address_ru'),
                    'type' => 'text',
                ]
            );
        }

        return back()->with('success', __('Settings updated successfully.'));
    }
}