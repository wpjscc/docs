<?php namespace Wpjscc\Middleware;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * health Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'wpjscc.health::lang.plugin.name',
            'description' => 'wpjscc.health::lang.plugin.description',
            'author'      => 'wpjscc',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {

    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        $this->app->register("Shallowman\\Laralog\\ServiceProvider");
        $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(\Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle::class);
        
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

    }
}
