<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $profile = PatientProfile::where('owner_id', $user->id)
            ->where('is_self', true)
            ->first();

        return view('patient.dashboard.profile', compact('user', 'profile'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $isSelf = $request->query('is_self') === '1';
        return view('patient.profiles.create', compact('isSelf'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isSelfRequested = $request->input('is_self') == '1';
        $hasSelf = PatientProfile::where('owner_id', auth()->id())->where('is_self', true)->exists();
        $isSelf = $isSelfRequested && !$hasSelf;

        if (!$isSelf) {
            $familyCount = PatientProfile::where('owner_id', auth()->id())->where('is_self', false)->count();
            if ($familyCount >= 5) {
                return back()->with('error', 'Bạn chỉ được phép quản lý tối đa 5 hồ sơ người thân.')->withInput();
            }
        }

        $rules = [
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,M,F,O',
            'id_card' => 'nullable|string|max:20|unique:patient_profiles,id_card',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'insurance_code' => 'nullable|string|min:10|max:15',
            'insurance_place' => 'nullable|string|max:255',
            'insurance_expiry' => 'nullable|date',
            'medical_history' => 'nullable|array',
            'medical_history.*' => 'string|max:255',
            'symptom_notes' => 'nullable|string',
        ];

        if ($isSelf) {
            $rules['email'] = 'nullable|email|max:150|unique:users,email,' . auth()->id();
        } else {
            $rules['relationship'] = 'required|in:parent,spouse,child,other';
        }

        $messages = [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh không hợp lệ (phải trước ngày hôm nay).',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'id_card.max' => 'CCCD không được vượt quá 20 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'occupation.max' => 'Nghề nghiệp không được vượt quá 100 ký tự.',
            'ethnicity.max' => 'Dân tộc không được vượt quá 50 ký tự.',
            'insurance_code.min' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_code.max' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_place.max' => 'Nơi KCB ban đầu không được vượt quá 255 ký tự.',
            'insurance_expiry.date' => 'Hạn thẻ BHYT không hợp lệ.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'relationship.required' => 'Vui lòng chọn mối quan hệ.',
            'relationship.in' => 'Mối quan hệ không hợp lệ.',
            'id_card.unique' => 'Số CMND/CCCD này đã được sử dụng trong hệ thống.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($isSelf && auth()->user()->id_card) {
            $validated['id_card'] = auth()->user()->id_card;
        }

        $genderMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $validated['gender'] = $genderMap[$validated['gender']] ?? $validated['gender'];

        $validated['owner_id'] = auth()->id();
        $validated['is_self'] = $isSelf;

        if ($isSelf) {
            $user = auth()->user();
            $userData = [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'id_card' => $validated['id_card'],
            ];
            if (array_key_exists('email', $validated)) {
                $userData['email'] = $validated['email'];
                unset($validated['email']);
            }
            $user->update($userData);
        }

        PatientProfile::create($validated);

        if ($request->query('redirect') === 'booking') {
            return redirect()->route('patient.booking.index')->with('success', 'Thêm hồ sơ thành công.');
        }

        if ($isSelf) {
            return redirect()->route('patient.profiles.index')->with('success', 'Cập nhật thông tin cá nhân thành công.');
        }

        return redirect()->route('patient.family.index')->with('success', 'Thêm hồ sơ người thân thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        return view('patient.profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        $rules = [
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,M,F,O',
            'id_card' => 'nullable|string|max:20|unique:patient_profiles,id_card,' . $profile->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'insurance_code' => 'nullable|string|min:10|max:15',
            'insurance_place' => 'nullable|string|max:255',
            'insurance_expiry' => 'nullable|date',
            'medical_history' => 'nullable|array',
            'medical_history.*' => 'string|max:255',
            'symptom_notes' => 'nullable|string',
        ];

        if ($profile->is_self) {
            $rules['email'] = 'nullable|email|max:150|unique:users,email,' . auth()->id();
        } else {
            $rules['relationship'] = 'required|in:parent,spouse,child,other';
        }

        $messages = [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh không hợp lệ (phải trước ngày hôm nay).',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'id_card.max' => 'CCCD không được vượt quá 20 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'occupation.max' => 'Nghề nghiệp không được vượt quá 100 ký tự.',
            'ethnicity.max' => 'Dân tộc không được vượt quá 50 ký tự.',
            'insurance_code.min' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_code.max' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_place.max' => 'Nơi KCB ban đầu không được vượt quá 255 ký tự.',
            'insurance_expiry.date' => 'Hạn thẻ BHYT không hợp lệ.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'relationship.required' => 'Vui lòng chọn mối quan hệ.',
            'relationship.in' => 'Mối quan hệ không hợp lệ.',
            'id_card.unique' => 'Số CMND/CCCD này đã được sử dụng trong hệ thống.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($profile->id_card) {
            $validated['id_card'] = $profile->id_card;
        }

        if ($profile->is_self) {
            $user = auth()->user();
            $userData = [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'id_card' => $validated['id_card'],
            ];
            if (array_key_exists('email', $validated)) {
                $userData['email'] = $validated['email'];
                unset($validated['email']);
            }
            $user->update($userData);
        }

        $genderMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $validated['gender'] = $genderMap[$validated['gender']] ?? $validated['gender'];

        $profile->update($validated);

        if ($profile->is_self) {
            return redirect()->route('patient.profiles.index')->with('success', 'Cập nhật thông tin thành công.');
        }

        return redirect()->route('patient.family.index')->with('success', 'Cập nhật hồ sơ người thân thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        if ($profile->is_self) {
            return back()->with('error', 'Không thể xóa hồ sơ chính chủ.');
        }

        if ($profile->appointments()->exists()) {
            return back()->with('error', 'Không thể xóa hồ sơ đã có lịch sử đặt khám.');
        }

        $profile->delete();

        return redirect()->route('patient.family.index')->with('success', 'Xóa hồ sơ thành công.');
    }
}