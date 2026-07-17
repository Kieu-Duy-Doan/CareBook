<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $maintenanceMode = SystemSetting::where('key', 'maintenance_mode')->value('value');

            if (filter_var($maintenanceMode, FILTER_VALIDATE_BOOLEAN)) {
                // Cho phép các route admin hoạt động bình thường
                if ($request->is('admin') || $request->is('admin/*')) {
                    return $next($request);
                }

                $message = SystemSetting::where('key', 'maintenance_message')->value('value')
                    ?: 'Hệ thống đang bảo trì nâng cấp, vui lòng quay lại sau.';

                abort(503, $message);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Bỏ qua nếu bảng system_settings chưa được tạo (lỗi SQL)
        }

        return $next($request);
    }
}
