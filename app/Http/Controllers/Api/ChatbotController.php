<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Faq;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_token' => 'nullable|string'
        ]);

        $messageText = trim($request->input('message'));
        $sessionToken = $request->input('session_token');
        $session = null;

        if ($sessionToken) {
            $session = ChatSession::where('session_token', $sessionToken)->where('status', 'active')->first();
        }

        if (!$session) {
            $sessionToken = (string) Str::uuid();
            $session = ChatSession::create([
                'session_token' => $sessionToken,
                'user_id' => auth()->guard('web')->id(),
                'status' => 'active'
            ]);
        }

        $matchResult = $this->findBestResponse($messageText);
        ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $messageText,
            'intent_detected' => $matchResult['intent_name']
        ]);
        ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $matchResult['reply'],
            'metadata' => $matchResult['metadata']
        ]);

        return response()->json([
            'reply' => $matchResult['reply'],
            'metadata' => $matchResult['metadata'],
            'session_token' => $sessionToken
        ]);
    }

    private function findBestResponse($messageText)
    {
        $messageLower = mb_strtolower($messageText, 'UTF-8');
        $intents = ChatbotIntent::where('is_active', true)->with(['responses' => function ($q) {
            $q->where('is_active', true)->orderBy('priority');
        }])->get();

        foreach ($intents as $intent) {
            if (empty($intent->example_phrases)) continue;
            $phrases = array_map('trim', explode('│', $intent->example_phrases));

            foreach ($phrases as $phrase) {
                if (empty($phrase)) continue;
                $phraseLower = mb_strtolower($phrase, 'UTF-8');
                if (Str::contains($messageLower, $phraseLower)) {
                    if ($intent->action == 'faq_lookup') {
                        $faq = $this->findFaq($messageLower);
                        if ($faq) {
                            return [
                                'reply' => $faq->answer,
                                'intent_name' => $intent->intent_name,
                                'metadata' => $faq->specialty ? ['Chuyên khoa liên quan' => $faq->specialty->name] : null
                            ];
                        }
                    }
                    if ($intent->responses->isNotEmpty()) {
                        $response = $intent->responses->first();
                        $response->increment('use_count');

                        return [
                            'reply' => $response->content,
                            'intent_name' => $intent->intent_name,
                            'metadata' => ['action' => $intent->action]
                        ];
                    }
                }
            }
        }
        $faq = $this->findFaq($messageLower);
        if ($faq) {
            return [
                'reply' => $faq->answer,
                'intent_name' => 'faq_lookup',
                'metadata' => $faq->specialty ? ['Chuyên khoa liên quan' => $faq->specialty->name] : null
            ];
        }
        return [
            'reply' => "Xin lỗi, tôi chưa hiểu rõ ý của bạn. Bạn có thể diễn đạt lại câu hỏi hoặc để lại số điện thoại, nhân viên CareBook sẽ liên hệ hỗ trợ nhé.",
            'intent_name' => null,
            'metadata' => null
        ];
    }

    private function findFaq($messageLower)
    {
        $faqs = Faq::where('is_active', true)->with('specialty')->get();
        foreach ($faqs as $f) {
            if (empty($f->keywords)) continue;

            $keywords = array_map('trim', explode(',', $f->keywords));
            foreach ($keywords as $kw) {
                if (empty($kw)) continue;
                if (Str::contains($messageLower, mb_strtolower($kw, 'UTF-8'))) {
                    $f->increment('view_count');
                    return $f;
                }
            }
        }
        return null;
    }
}
