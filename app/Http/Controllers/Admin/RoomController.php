<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Specialty;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('specialties')->latest();

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $rooms = $query->paginate(20)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.rooms.index', compact('rooms', 'specialties'));
    }
}
?>