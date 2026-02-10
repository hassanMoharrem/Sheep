<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // عرض جميع الإعدادات
    public function index()
    {
        $data = Setting::paginate(10);
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $data,
        ]);
    }
    public function storeSingle(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'type' => 'required|in:currency,duration',
            'label' => 'nullable|string',
            'value' => 'required|string',
        ]);
        $setting = Setting::create($data);
        return response()->json([
            'status' => 200,
            'message' => 'تم الحفظ بنجاح',
            'success' => true,
            'data' => $setting
        ]);
    }

    // حفظ أو تحديث إعداد
    public function store(Request $request)
    {
        // إدخال أكثر من إعداد في نفس الوقت
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|distinct',
            'settings.*.type' => 'required|in:currency,duration',
            'settings.*.label' => 'nullable|string',
            'settings.*.value' => 'required|string',
        ]);
        foreach ($data['settings'] as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'type' => $settingData['type'],
                    'label' => $settingData['label'] ?? null,
                    'value' => $settingData['value'],
                ]
            );
        }
        return response()->json([
            'status' => 200,
            'message' => 'تم الحفظ بنجاح',
            'success' => true,
        ]);
    }
    // عرض إعداد معين
    public function show($id)
    {
        $setting = Setting::findOrFail($id);
        return response()->json([
            'status' => 200,
            'message' => 'Data Retrieved',
            'success' => true,
            'data' => $setting
        ]);
    }

    // تعديل إعداد
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $data = $request->validate([
            'key' => 'required|string|unique:settings,key,' . $id,
            'type' => 'required|in:currency,duration',
            'label' => 'nullable|string',
            'value' => 'required|string',
        ]);
        $setting->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'تم التعديل بنجاح',
            'success' => true,
            'data' => $setting
        ]);
    }

    // حذف إعداد
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        return response()->json([
            'status' => 200,
            'message' => 'تم الحذف بنجاح',
            'success' => true
        ]);
    }
}
