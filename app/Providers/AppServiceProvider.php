<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('components.layouts.patient*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $notifications = \App\Models\Notification::where('user_id', $user->id)
                    ->latest('created_at')
                    ->take(5)
                    ->get();
                $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                $view->with(compact('notifications', 'unreadCount'));
            }
        });

        \Illuminate\Support\Facades\View::composer('components.layouts.doctor*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $notifications = \App\Models\Notification::where('user_id', $user->id)
                    ->latest('created_at')
                    ->take(5)
                    ->get();
                $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                $view->with(compact('notifications', 'unreadCount'));
            }
        });

        \Illuminate\Support\Facades\View::composer('components.layouts.receptionist*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $notifications = \App\Models\Notification::where('user_id', $user->id)
                    ->latest('created_at')
                    ->take(5)
                    ->get();
                $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                $view->with(compact('notifications', 'unreadCount'));
            }
        });
    }
}
