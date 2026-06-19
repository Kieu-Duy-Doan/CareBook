<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['specialty', 'author'])->latest();

        if ($request->filled('post_type')) {
            $query->where('post_type', $request->post_type);
        }
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(15)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'specialties'));
    }

    public function create()
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.posts.create', compact('specialties'));
    }

     public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:300|unique:posts,slug',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specialty_id' => 'nullable|exists:specialties,id',
            'post_type' => 'required|in:news,service,guide,announcement',
            'is_published' => 'boolean',
        ]);

        $slug = $request->slug ?: Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('posts', 'public');
            $thumbnailUrl = '/storage/' . $path;
        }

        $isPublished = $request->has('is_published');
        $post = Post::create([
            'title' => $request->title,
            'slug' => $slug,
            'summary' => $request->summary,
            'content' => $request->content,
            'thumbnail_url' => $thumbnailUrl,
            'specialty_id' => $request->specialty_id,
            'post_type' => $request->post_type,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'author_id' => Auth::id(),
            'view_count' => 0,
        ]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'POST_CREATED',
            'module' => 'cms',
            'ref_type' => 'post',
            'ref_id' => $post->id,
            'description' => 'Thêm bài viết: ' . $post->title,
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('admin.posts.index')->with('success', 'Đã thêm bài viết thành công.');
    }

}