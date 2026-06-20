<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use App\Enums\ChatbotActionEnum;
use Illuminate\Validation\Rule;
use App\Http\Requests\Admin\ChatbotIntent\StoreIntentRequest;
use App\Http\Requests\Admin\ChatbotIntent\UpdateIntentRequest;
use App\Http\Requests\Admin\ChatbotIntent\StoreResponseRequest;
use App\Http\Requests\Admin\ChatbotIntent\UpdateResponseRequest;

// Quản lý các Kịch bản (Intents) và Câu trả lời (Responses) của Chatbot
class ChatbotIntentController extends Controller
{
    // Hiển thị danh sách tất cả các kịch bản
    public function index()
    {
        $intents = ChatbotIntent::orderBy('intent_name')->get();
        return view('admin.chatbot.intents.index', compact('intents'));
    }

    // Lưu một kịch bản mới vào hệ thống
    public function store(StoreIntentRequest $request)
    {        ChatbotIntent::create([
            'intent_name' => strtolower($request->intent_name),
            'description' => $request->description,
            'example_phrases' => $request->example_phrases,
            'action' => $request->action,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã thêm Kịch bản (Intent) thành công.');
    }

    // Cập nhật thông tin của một kịch bản có sẵn
    public function update(UpdateIntentRequest $request, $id)
    {
        $intent = ChatbotIntent::findOrFail($id);

        $intent->update([
            'intent_name' => strtolower($request->intent_name),
            'description' => $request->description,
            'example_phrases' => $request->example_phrases,
            'action' => $request->action,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã cập nhật Kịch bản thành công.');
    }

    // Bật hoặc tắt trạng thái hoạt động của kịch bản
    public function toggleActive($id)
    {
        $intent = ChatbotIntent::findOrFail($id);
        $intent->is_active = !$intent->is_active;
        $intent->save();

        return back()->with('success', 'Đã thay đổi trạng thái Kịch bản.');
    }

    // Xóa kịch bản (chỉ cho phép xóa khi chưa có câu trả lời nào liên kết)
    public function destroy($id)
    {
        $intent = ChatbotIntent::findOrFail($id);

        if ($intent->responses()->count() > 0) {
            return back()->with('error', 'Không thể xoá Kịch bản này vì đang có câu trả lời liên kết.');
        }

        $intent->delete();
        return back()->with('success', 'Đã xoá Kịch bản thành công.');
    }

    // --- QUẢN LÝ CÂU TRẢ LỜI CỦA TỪNG KỊCH BẢN ---

    // Hiển thị chi tiết kịch bản và danh sách các câu trả lời
    public function show($id)
    {
        $intent = ChatbotIntent::with(['responses' => function ($q) {
            $q->orderBy('priority', 'asc');
        }])->findOrFail($id);

        return view('admin.chatbot.intents.show', compact('intent'));
    }

    public function storeResponse(StoreResponseRequest $request, $intentId)
    {        ChatbotResponse::create([
            'intent_id' => $intentId,
            'content' => $request->content,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã thêm Câu trả lời thành công.');
    }

    public function updateResponse(UpdateResponseRequest $request, $intentId, $id)
    {
        $response = ChatbotResponse::where('intent_id', $intentId)->findOrFail($id);

        $response->update([
            'content' => $request->content,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã cập nhật Câu trả lời thành công.');
    }

    public function toggleResponseActive($intentId, $id)
    {
        $response = ChatbotResponse::where('intent_id', $intentId)->findOrFail($id);
        $response->is_active = !$response->is_active;
        $response->save();

        return back()->with('success', 'Đã đổi trạng thái câu trả lời.');
    }

    public function destroyResponse($intentId, $id)
    {
        $response = ChatbotResponse::where('intent_id', $intentId)->findOrFail($id);
        $response->delete();

        return back()->with('success', 'Đã xoá câu trả lời thành công.');
    }
}
