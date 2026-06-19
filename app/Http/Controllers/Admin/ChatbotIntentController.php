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
}
