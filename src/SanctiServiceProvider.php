<?php

namespace Sancti;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Sancti\Exceptions\SanctiHandler;
use Sancti\Exceptions\SanctiCodeHandler;
use Sancti\Http\Facades\SanctiFacade;
use Sancti\Services\Sancti;

class SanctiServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sancti');

		$this->app->bind(Sancti::class, function($app) {
			return new Sancti();
		});

		$this->app->bind('sancti-facade', function($app) {
			return new SanctiFacade();
		});

		if(config('sancti.settings.code_handler') == true) {
			$this->app->singleton(ExceptionHandler::class, SanctiCodeHandler::class);
		} else {
			$this->app->singleton(ExceptionHandler::class, SanctiHandler::class);
		}
	}

	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'sancti');
		$this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'sancti');

		$this->loadRoutesFrom(__DIR__.'/../routes/api.php');
		$this->loadMigrationsFrom(__DIR__.'/../database/migrations');

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/config.php' => config_path('sancti.php'),
				__DIR__.'/../resources/views' => resource_path('views/vendor/sancti')
			], 'sancti-config');

			$this->publishes([
				__DIR__.'/../tests/Sancti' => base_path('tests/Sancti')
			], 'sancti-tests');
		}
	}
}