<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatbotIntent;
use App\Models\Faq;
use Illuminate\Support\Str;

// Dịch vụ chuyên biệt đảm nhiệm việc kết nối và xử lý logic với Google Gemini AI
class ChatbotAIService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    // Xử lý toàn bộ luồng tin nhắn đầu vào và trả về kết quả JSON
    public function processMessage(string $userMessage): array
    {
        // Khử trùng (Sanitize) dữ liệu đầu vào để tránh mã độc
        $userMessage = htmlspecialchars(trim($userMessage));

        // Chuẩn bị danh sách Kịch bản có sẵn làm ngữ cảnh (Context) cho AI
        $intentsContext = $this->buildIntentsContext();

        // Gửi yêu cầu phân tích tới API của Gemini
        $geminiResponse = $this->callGeminiAPI($userMessage, $intentsContext);

        if ($geminiResponse) {
            return $this->handleAIResult($geminiResponse, $userMessage);
        }

        // Dự phòng (Fallback): Nếu API Google lỗi, tự động chuyển về tìm kiếm từ khóa cơ bản
        Log::warning('Gemini API failed or timed out, using fallback keyword matching.');
        return $this->fallbackMatching($userMessage);
    }

    // Xây dựng chuỗi văn bản mô tả các Intent để đưa vào System Prompt
    protected function buildIntentsContext(): string
    {
        $intents = ChatbotIntent::where('is_active', true)->get();
        $context = "Danh sách các Intent Name hiện có trong hệ thống:\n";
        foreach ($intents as $intent) {
            $context .= "- Intent Name: {$intent->intent_name} | Action: {$intent->action} | Ví dụ mẫu câu hỏi: {$intent->example_phrases}\n";
        }
        return $context;
    }

    // Thực hiện gọi HTTP Request tới endpoint của Google Gemini
    protected function callGeminiAPI(string $userMessage, string $intentsContext): ?array
    {
        $systemPrompt = <<<PROMPT
Bạn là một trợ lý y tế ảo thân thiện của phòng khám CareBook. 
Nhiệm vụ của bạn là phân tích câu hỏi của người dùng và quyết định xem câu hỏi đó KHỚP với "Intent Name" nào trong danh sách được cung cấp.
Nếu không khớp với Intent nào, hãy phân loại action là "unknown" và tự sinh ra một câu trả lời (reply) phù hợp, thân thiện, và từ chối lịch sự nếu nội dung nằm ngoài lĩnh vực y tế, sức khỏe hoặc phòng khám.

$intentsContext

Hãy trả về CHỈ MỘT chuỗi JSON hợp lệ với cấu trúc chính xác như sau:
{
    "action": "tên action (faq_lookup, guide_booking, introduce_specialty, transfer_staff, hoặc unknown)",
    "intent_name": "tên intent_name tương ứng nếu khớp, hoặc null nếu unknown",
    "reply": "Câu trả lời tự sinh tự nhiên bằng tiếng Việt nếu unknown. Để null nếu đã khớp intent."
}
PROMPT;

        try {
            $response = Http::timeout(10)->post($this->apiUrl . '?key=' . $this->apiKey, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => [
                    ['parts' => [['text' => $userMessage]]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $json = json_decode($text, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }
            
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Gemini API exception: ' . $e->getMessage());
        }

        return null;
    }

    // Xử lý dữ liệu JSON trả về từ AI để bóc tách Intent và nội dung phản hồi
    protected function handleAIResult(array $aiResult, string $userMessage): array
    {
        $intentName = $aiResult['intent_name'] ?? null;
        $action = $aiResult['action'] ?? 'unknown';
        $reply = $aiResult['reply'] ?? null;

        if ($intentName) {
            $intent = ChatbotIntent::where('intent_name', $intentName)
                ->where('is_active', true)
                ->with(['responses' => function ($q) {
                    $q->where('is_active', true)->orderBy('priority');
                }])->first();

            if ($intent) {
                if ($intent->action === 'faq_lookup') {
                    $faq = $this->findFaq($userMessage);
                    if ($faq) {
                        return [
                            'reply' => $faq->answer,
                            'intent_name' => $intent->intent_name,
                            'metadata' => $faq->specialty ? ['Chuyên khoa' => $faq->specialty->name] : null
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

        return [
            'reply' => $reply ?: "Dạ, tôi chưa hiểu rõ ý của bạn. Bạn có thể để lại số điện thoại hoặc hỏi lại câu khác, nhân viên CareBook sẽ hỗ trợ bạn nhé.",
            'intent_name' => null,
            'metadata' => null
        ];
    }

    // Tìm kiếm câu trả lời nhanh từ bảng FAQ
    protected function findFaq(string $messageLower): ?Faq
    {
        $messageLower = mb_strtolower($messageLower, 'UTF-8');
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

    // Cơ chế quét từ khóa thuần túy dùng khi AI gặp sự cố
    public function fallbackMatching(string $messageText): array
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
                                'metadata' => $faq->specialty ? ['Chuyên khoa' => $faq->specialty->name] : null
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
                'metadata' => $faq->specialty ? ['Chuyên khoa' => $faq->specialty->name] : null
            ];
        }
        return [
            'reply' => "Xin lỗi, tôi chưa hiểu rõ ý của bạn. Vui lòng liên hệ trực tiếp qua số Hotline để được hỗ trợ nhé.",
            'intent_name' => null,
            'metadata' => null
        ];
    }
}
