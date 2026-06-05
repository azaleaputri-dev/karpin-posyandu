<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useTailwind();

        View::composer('*', function ($view) {
            $user = auth()->user();
            $items = [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Profil Saya', 'route' => 'profile.edit'],
                ['label' => 'Data Anak', 'route' => 'children.index'],
                ['label' => 'Pengukuran', 'route' => 'measurements.index'],
            ];

            if ($user && $user->isAdmin()) {
                array_splice($items, 1, 0, [
                    ['label' => 'Posyandu', 'route' => 'posyandus.index'],
                ]);
                $items[] = ['label' => 'Manajemen User', 'route' => 'users.index'];
                $items[] = ['label' => 'Perangkat IoT', 'route' => 'devices.index'];
            }

            $view->with('navItems', $items);
        });
    }
}
