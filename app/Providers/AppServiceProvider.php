<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\User;
use App\Observers\EventHistoryObserver;
use App\Observers\UserHistoryObserver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->ensurePublicStorageLink();

        User::observe(UserHistoryObserver::class);
        Event::observe(EventHistoryObserver::class);

        Validator::replacer('required', fn () => 'Required.');

        View::composer('*', function ($view) {
            $view->with('appLogoUrl', config('volunteerhub.logo_url'));
            $view->with('profileUrl', ! auth()->check()
                ? route('login')
                : (auth()->user()->role === 'organization'
                    ? route('admin.profile')
                    : route('user.profile.history')));
        });
    }

    private function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');

        if (is_link($link) || file_exists($link)) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            Log::warning('StorageImage: could not create public/storage symlink', [
                'error' => $e->getMessage(),
                'hint' => 'Images are still served via the /storage/{path} route fallback.',
            ]);
        }
    }
}
