<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::with('specialty')->latest();

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $faqs = $query->paginate(20)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.faqs.index', compact('faqs', 'specialties'));
    }

    public function create()
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.faqs.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'specialty_id' => 'nullable|exists:specialties,id',
            'keywords' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $faq = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'specialty_id' => $request->specialty_id,
            'keywords' => $request->keywords,
            'is_active' => $request->has('is_active'),
        ]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'FAQ_CREATED',
            'module' => 'faq',
            'ref_type' => 'faq',
            'ref_id' => $faq->id,
            'description' => 'Thêm câu hỏi FAQ',
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã thêm FAQ thành công.');
    }

    public function edit($id)
    {
        $faq = Faq::findOrFail($id);
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.faqs.edit', compact('faq', 'specialties'));
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'specialty_id' => 'nullable|exists:specialties,id',
            'keywords' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'specialty_id' => $request->specialty_id,
            'keywords' => $request->keywords,
            'is_active' => $request->has('is_active'),
        ]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'FAQ_UPDATED',
            'module' => 'faq',
            'ref_type' => 'faq',
            'ref_id' => $faq->id,
            'description' => 'Cập nhật FAQ',
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('admin.faqs.edit', $faq->id)->with('success', 'Đã cập nhật FAQ thành công.');
    }

    public function toggleActive($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return back()->with('success', 'Đã thay đổi trạng thái FAQ.');
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);

        if ($faq->view_count > 0 && $faq->is_active == 1) {
            return back()->with('error', 'Chỉ cho phép xoá khi lượt xem bằng 0 hoặc FAQ đang tắt.');
        }

        $faq->delete();

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'FAQ_DELETED',
            'module' => 'faq',
            'ref_type' => 'faq',
            'ref_id' => $id,
            'description' => 'Xoá FAQ',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã xoá FAQ thành công.');
    }
}
