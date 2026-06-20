<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\SystemLog;
use App\Models\User;
use App\Services\SystemSettingService;
use App\Services\SystemLogService;
use App\Http\Requests\Admin\UpdateSettingRequest;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    protected $settingService;
    protected $logService;

    // Gọi các file xử lý logic (Service) vào Controller
    public function __construct(SystemSettingService $settingService, SystemLogService $logService)
    {
        $this->settingService = $settingService;
        $this->logService = $logService;
    }

    // Hiển thị giao diện cài đặt và danh sách nhật ký hệ thống
    public function index(Request $request)
    {
        // 1. Lấy danh sách cài đặt hiện tại
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

        // 2. Lấy danh sách lịch sử hoạt động (kèm thông tin user để web load nhanh hơn)
        $query = SystemLog::with('user')->latest();

        // Tìm kiếm theo các ô dữ liệu người dùng nhập
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

        // 3. Nếu người dùng bấm nút Xuất JSON, thì chuyển qua xử lý xuất file
        if ($request->get('export') === 'json') {
            // Gọi hàm xuất file từ Service
            return $this->logService->exportJsonStream($query);
        }

        // 4. Phân trang danh sách lịch sử (30 dòng/trang) và lấy thông tin user
        $logs = $query->paginate(30)->withQueryString();
        $users = User::where('is_active', true)->orderBy('full_name')->get();
        
        // Lấy danh sách tên các module có sẵn
        $modules = SystemLog::MODULES;

        return view('admin.settings.index', compact('settings', 'settingsMeta', 'logs', 'users', 'modules'));
    }

    // Hàm lưu các cài đặt mới
    public function update(UpdateSettingRequest $request)
    {
        try {
            // Đẩy hết dữ liệu xuống Service để xử lý lưu vào CSDL và lưu ảnh
            $this->settingService->updateBulkSettings(
                $request->input('settings', []),
                $request->input('settings_types', []),
                $request->file('logo'),
                Auth::id(),
                $request->boolean('remove_logo')
            );

            return redirect()->route('admin.settings.index')
                             ->with('success', 'Đã lưu cài đặt thành công.');
                             
        } catch (\InvalidArgumentException $e) {
            // Nếu có lỗi do nhập sai định dạng thì báo lỗi trên màn hình
            return back()->with('error', $e->getMessage());
        }
    }
}
