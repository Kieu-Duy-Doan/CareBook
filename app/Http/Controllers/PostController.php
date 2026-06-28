<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::where('is_published', true)->orderBy('published_at', 'desc');

        if ($request->filled('type')) {
            $query->where('post_type', $request->input('type'));
        }
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->paginate(9)->withQueryString();

        return view('posts.index', compact('posts'));
    }

    public function show($slug): View
    {
        $post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        $post->increment('view_count');

        // Get related posts
        $relatedPosts = Post::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('post_type', $post->post_type)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }
}
