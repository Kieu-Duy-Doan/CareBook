<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\SystemLog;
use App\Models\User;
use App\Services\SystemSettingService;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    protected $settingService;
    protected $logService;

    public function __construct(SystemSettingService $settingService, SystemLogService $logService)
    {
        $this->settingService = $settingService;
        $this->logService = $logService;
    }

    public function index(Request $request)
    {
        $settingsCollection = SystemSetting::all();
        $settings = [];
        $settingsMeta = [];

        foreach ($settingsCollection as $setting) {
            $settings[$setting->key] = $this->settingService->get($setting->key);
            $settingsMeta[$setting->key] = [
                'data_type' => $setting->data_type,
                'description' => $setting->description
            ];
        }

        // Logs Logic
        $query = SystemLog::with('user')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action_search')) {
            $query->where('action', 'like', '%' . $request->action_search . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // export json
        if ($request->get('export') === 'json') {
            $exportLogs = $query->get()->map(function ($log) {
                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'user' => $log->user ? $log->user->full_name : 'Hệ thống',
                    'action' => $log->action,
                    'module' => $log->module,
                    'ref_type' => $log->ref_type,
                    'ref_id' => $log->ref_id,
                    'old_data' => $log->old_data,
                    'new_data' => $log->new_data,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent
                ];
            });

            return response()->streamDownload(function () use ($exportLogs) {
                echo json_encode($exportLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, 'system_logs_' . date('Ymd_His') . '.json', [
                'Content-Type' => 'application/json',
            ]);
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        $modules = [
            'auth',
            'users',
            'doctors',
            'specialties',
            'rooms',
            'work-schedules',
            'appointments',
            'cms',
            'faq',
            'chatbot',
            'notifications',
            'settings'
        ];

        return view('admin.settings.index', compact('settings', 'settingsMeta', 'logs', 'users', 'modules'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $settingsData = $request->input('settings', []);
        $settingsTypes = $request->input('settings_types', []);
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings', 'public');
            $settingsData['logo'] = $logoPath;
            $settingsTypes['logo'] = 'string';
        }

        $oldSettings = SystemSetting::all()->keyBy('key')->toArray();
        $changedData = [];

        foreach ($settingsData as $key => $value) {
            $dataType = $settingsTypes[$key] ?? 'string';

            try {
                $this->settingService->set($key, $value, $dataType, null, $userId);
                if (!isset($oldSettings[$key]) || $oldSettings[$key]['value'] !== (string)$value) {
                    $changedData[$key] = [
                        'old' => $oldSettings[$key]['value'] ?? null,
                        'new' => $value
                    ];
                }
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', "Lỗi định dạng cho cài đặt: {$key}");
            }
        }

        if (!empty($changedData)) {
            $this->logService->log(
                action: 'SETTINGS_UPDATED',
                module: 'settings',
                oldData: array_map(fn($item) => $item['old'], $changedData),
                newData: array_map(fn($item) => $item['new'], $changedData),
                userId: $userId
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Đã lưu cài đặt thành công.');
    }
}
