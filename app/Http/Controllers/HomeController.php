<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Models\DoctorProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $specialties = Specialty::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'name', 'description', 'image_url']);

        $doctors = DoctorProfile::with([
            'user:id,full_name,avatar_url',
            'specialties:id,name',
        ])
        ->orderByDesc('experience_years')
        ->take(4)
        ->get();

        $posts = \App\Models\Post::where('is_published', true)
            ->latest('published_at')
            ->take(3)
            ->get(['id', 'title', 'slug', 'thumbnail_url', 'summary', 'published_at', 'post_type']);

        return view('home', compact('specialties', 'doctors', 'posts'));
    }
}
