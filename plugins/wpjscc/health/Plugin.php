<?php namespace Wpjscc\Health;

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

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate

        return [
            'Wpjscc\Health\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'wpjscc.health.some_permission' => [
                'tab' => 'wpjscc.health::lang.plugin.name',
                'label' => 'wpjscc.health::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'health' => [
                'label'       => 'wpjscc.health::lang.plugin.name',
                'url'         => Backend::url('wpjscc/health/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['wpjscc.health.*'],
                'order'       => 500,
            ],
        ];
    }
}
