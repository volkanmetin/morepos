<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        return Inertia::render('Settings/Index', [
            'groups' => SettingKey::getGroups(),
            'settings' => $this->settingService->all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $this->settingService->bulkSet($validated['settings']);

        return back()->with('success', 'Ayarlar başarıyla güncellendi.');
    }
} 