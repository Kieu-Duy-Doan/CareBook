<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class ChatbotIntentController extends Controller
{
    public function index()
    {
        $intents = ChatbotIntent::orderBy('intent_name')->get();
        return view('admin.chatbot.intents.index', compact('intents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'intent_name' => 'required|string|max:100|unique:chatbot_intents,intent_name|regex:/^[a-z0-9_]+$/',
            'description' => 'required|string|max:255',
            'example_phrases' => 'nullable|string',
            'action' => 'required|in:faq_lookup,guide_booking,introduce_specialty,transfer_staff',
            'is_active' => 'boolean',
        ]);

        ChatbotIntent::create([
            'intent_name' => strtolower($request->intent_name),
            'description' => $request->description,
            'example_phrases' => $request->example_phrases,
            'action' => $request->action,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã thêm Kịch bản (Intent) thành công.');
    }

    public function update(Request $request, $id)
    {
        $intent = ChatbotIntent::findOrFail($id);

        $request->validate([
            'intent_name' => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:chatbot_intents,intent_name,' . $id,
            'description' => 'required|string|max:255',
            'example_phrases' => 'nullable|string',
            'action' => 'required|in:faq_lookup,guide_booking,introduce_specialty,transfer_staff',
            'is_active' => 'boolean',
        ]);

        $intent->update([
            'intent_name' => strtolower($request->intent_name),
            'description' => $request->description,
            'example_phrases' => $request->example_phrases,
            'action' => $request->action,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã cập nhật Kịch bản thành công.');
    }

    public function toggleActive($id)
    {
        $intent = ChatbotIntent::findOrFail($id);
        $intent->is_active = !$intent->is_active;
        $intent->save();

        return back()->with('success', 'Đã thay đổi trạng thái Kịch bản.');
    }

    public function destroy($id)
    {
        $intent = ChatbotIntent::findOrFail($id);

        if ($intent->responses()->count() > 0) {
            return back()->with('error', 'Không thể xoá Kịch bản này vì đang có câu trả lời liên kết.');
        }

        $intent->delete();
        return back()->with('success', 'Đã xoá Kịch bản thành công.');
    }

    // --- RESPONSES CRUD INSIDE INTENT DETAILS ---

    public function show($id)
    {
        $intent = ChatbotIntent::with(['responses' => function ($q) {
            $q->orderBy('priority', 'asc');
        }])->findOrFail($id);

        return view('admin.chatbot.intents.show', compact('intent'));
    }

    public function storeResponse(Request $request, $intentId)
    {
        $request->validate([
            'content' => 'required|string',
            'priority' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        ChatbotResponse::create([
            'intent_id' => $intentId,
            'content' => $request->content,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Đã thêm Câu trả lời thành công.');
    }

    public function updateResponse(Request $request, $intentId, $id)
    {
        $response = ChatbotResponse::where('intent_id', $intentId)->findOrFail($id);

        $request->validate([
            'content' => 'required|string',
            'priority' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

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
