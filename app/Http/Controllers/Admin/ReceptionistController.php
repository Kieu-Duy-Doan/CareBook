<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StaffProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class ReceptionistController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'  => User::where('role', 'receptionist')->count(),
            'active' => User::where('role', 'receptionist')->where('is_active', true)->count(),
            'locked' => User::where('role', 'receptionist')->where('is_active', false)->count(),
        ];

        $query = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->latest('created_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhereHas('staffProfile', fn($sq) =>
                      $sq->where('employee_code', 'like', '%'.$request->search.'%')
                         ->orWhere('position', 'like', '%'.$request->search.'%')
                  );
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('department')) {
            $query->whereHas('staffProfile', fn($sq) =>
                $sq->where('department', 'like', '%'.$request->department.'%')
            );
        }

        $receptionists = $query->paginate(15)->withQueryString();

        // Lấy danh sách phòng ban distinct để filter
        $departments = \App\Models\StaffProfile::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.receptionists.index', compact('receptionists', 'stats', 'departments'));
    }
    public function create()
    {
        // Tự động sinh mã nhân viên kế tiếp (LT001, LT002, ...)
        $latestStaff = StaffProfile::where('employee_code', 'regexp', '^LT[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(employee_code, 3) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($latestStaff) {
            $numberStr = substr($latestStaff->employee_code, 2);
            if (is_numeric($numberStr)) {
                $nextNumber = (int)$numberStr + 1;
            }
        }
        $nextEmployeeCode = 'LT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('admin.receptionists.create', compact('nextEmployeeCode'));
    }