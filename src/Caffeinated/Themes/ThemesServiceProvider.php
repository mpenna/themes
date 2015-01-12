<?php

namespace Caffeinated\Themes;

use Caffeinated\Themes\Handlers\ThemesHandler;
use Illuminate\Support\ServiceProvider;

class ThemesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerResources();

		$this->registerServices();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['themes.handler', 'themes'];
	}

	/**
	 * Register the package resources.
	 *
	 * @return void
	 */
	protected function registerResources()
	{
		$userConfigFile    = app()->configPath().'/caffeinated/themes.php';
		$packageConfigFile = __DIR__.'/../../config/config.php';
		$config            = $this->app['files']->getRequire($packageConfigFile);

		if (file_exists($userConfigFile)) {
			$userConfig = $this->app['files']->getRequire($userConfigFile);
			$config     = array_replace_recursive($config, $userConfig);
		}

		$this->app['config']->set('caffeinated::themes', $config);
	}

	/**
	 * Register the package services.
	 *
	 * @return void
	 */
	protected function registerServices()
	{
		$this->app->bindShared('themes.handler', function ($app) {
			return new ThemesHandler($app['files'], $app['config'], $app['view']);
		});

		$this->app->bindShared('themes', function($app) {
			return new Themes($app['themes.handler']);
		});

		$this->app->booting(function($app) {
			$app['themes']->register();
		});
	}
}
