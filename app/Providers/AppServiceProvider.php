<?php

namespace App\Providers;

use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\GradeRepositoryInterface;
use App\Repositories\Contracts\ScoreRepositoryInterface;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Repositories\FeeRepository;
use App\Repositories\GradeRepository;
use App\Repositories\ScoreRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\SessionRepository;
use App\Repositories\SubjectRepository;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SubjectRepositoryInterface::class , SubjectRepository::class);
        $this->app->bind(SessionRepositoryInterface::class , SessionRepository::class );
        $this->app->bind(GradeRepositoryInterface::class , GradeRepository::class );
        $this->app->bind(SemesterRepositoryInterface::class , SemesterRepository::class);
        $this->app->bind(ScoreRepositoryInterface::class, ScoreRepository::class );
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
