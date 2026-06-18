<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Exception;

class DoctorController extends Controller
{
    /**
     * Lấy danh sách bác sĩ thuộc chuyên khoa.
     *
     * @param Request $request
     * @param int $specialtyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySpecialty(Request $request)
    {
        $specialtyId = $request->route('specialtyId');

        $specialty = Specialty::find($specialtyId);

        if (! $specialty) {
            return response()->json([
                'success' => false,
                'message' => 'Chuyên khoa không tồn tại.',
            ], 404);
        }

        try {
            $doctors = DoctorProfile::with(['user', 'specialties'])
                ->whereHas(
                    'specialties',
                    fn($query) =>
                    $query->where('specialties.id', $specialtyId)
                )
                ->whereHas(
                    'user',
                    fn($query) =>
                    $query->where('is_active', true)
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => $doctors,
                'message' => 'Lấy danh sách bác sĩ theo chuyên khoa thành công.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách bác sĩ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
