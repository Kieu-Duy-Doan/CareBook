<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\CampaignService;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Requests\Admin\CampaignNotificationRequest;

class NotificationController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    // Hàm hiển thị danh sách các chiến dịch thông báo đã gửi
    public function index(Request $request)
    {
        // Nhóm các thông báo gửi cùng lúc lại thành 1 "chiến dịch" để dễ nhìn
        $campaigns = $this->campaignService->getCampaigns($request->all());

        return view('admin.notifications.index', compact('campaigns'));
    }

    public function create()
    {
        // Chỉ tải lại những người dùng đã được chọn trước đó nếu form bị lỗi Validation
        $oldUserIds = old('user_ids', []);
        $users = [];
        if (!empty($oldUserIds)) {
            $users = User::whereIn('id', $oldUserIds)->select('id', 'full_name', 'email', 'role')->get();
        }
        
        return view('admin.notifications.create', compact('users'));
    }

    // Hàm hỗ trợ API tìm kiếm người dùng (để ô chọn người nhận không bị đơ)
    public function searchUsers(Request $request)
    {
        $search = $request->query('q');
        
        $query = User::select('id', 'full_name', 'email', 'role')->where('is_active', true);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Giới hạn chỉ trả về 50 người để web load cực nhanh
        $users = $query->limit(50)->get();
        
        // Trả về dữ liệu chuẩn định dạng cho thư viện TomSelect đọc
        return response()->json([
            'items' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->full_name . ' (' . ($user->email ?? 'Không có email') . ') - ' . ucfirst($user->role)
                ];
            })
        ]);
    }

    // Hàm xử lý khi bấm nút "Phát hành Thông báo"
    public function store(StoreNotificationRequest $request)
    {
        // Validation đã được xử lý tự động trong StoreNotificationRequest
        $this->campaignService->createCampaign($request->validated());

        return redirect()->route('admin.notifications.index')->with('success', 'Đã tạo và đưa vào hàng đợi thông báo thành công.');
    }

    // Hàm xóa cả một cụm thông báo đã gửi
    public function destroy(CampaignNotificationRequest $request)
    {
        // Logic xóa được xử lý ở CampaignService
        $this->campaignService->deleteCampaign($request->batch_id);
            
        return back()->with('success', 'Đã xoá chiến dịch thông báo.');
    }

    // Hàm yêu cầu gửi lại các email bị lỗi
    public function resend(CampaignNotificationRequest $request)
    {
        // Đặt lại trạng thái trong Service
        $this->campaignService->resendCampaign($request->batch_id);

        return back()->with('success', 'Đã đặt lại trạng thái để gửi lại thông báo qua Email.');
    }
}
