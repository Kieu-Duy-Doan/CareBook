<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
class FaqController extends Controller
{
    // Hiển thị danh sách câu hỏi thường gặp kèm theo bộ lọc tìm kiếm
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

    // Hiển thị giao diện thêm câu hỏi mới
    public function create()
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.faqs.create', compact('specialties'));
    }

    // Xử lý lưu dữ liệu câu hỏi mới vào cơ sở dữ liệu
    public function store(StoreFaqRequest $request)
    {
        $validated = $request->validated();

        $faq = Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'specialty_id' => $validated['specialty_id'] ?? null,
            'keywords' => $validated['keywords'] ?? null,
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

    // Hiển thị giao diện chỉnh sửa câu hỏi đã chọn
    public function edit($id)
    {
        $faq = Faq::findOrFail($id);
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.faqs.edit', compact('faq', 'specialties'));
    }

    // Xử lý cập nhật thông tin câu hỏi vào cơ sở dữ liệu
    public function update(UpdateFaqRequest $request, $id)
    {
        $faq = Faq::findOrFail($id);
        $validated = $request->validated();

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'specialty_id' => $validated['specialty_id'] ?? null,
            'keywords' => $validated['keywords'] ?? null,
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

    // Bật hoặc tắt trạng thái hiển thị của câu hỏi
    public function toggleActive($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return back()->with('success', 'Đã thay đổi trạng thái FAQ.');
    }

    // Xóa câu hỏi khỏi hệ thống nếu thỏa điều kiện
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
