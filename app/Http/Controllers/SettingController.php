<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        return $this->renderInertiaPage(
            'pages/SettingsPage',
            view: 'settings.index',
            scriptId: 'svelte-settings-props',
            viewData: compact('settings'),
        );
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan!');
    }
}
