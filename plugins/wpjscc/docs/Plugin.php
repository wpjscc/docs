<?php namespace Wpjscc\Docs;

use Backend;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginBase;
use Wpjscc\Docs\Classes\DocsManager;
use Wpjscc\Docs\Classes\MarkdownDocumentation;
use Wpjscc\Docs\Classes\MarkdownPageIndex;
use Wpjscc\Docs\Classes\PHPApiPageIndex;
use Winter\Storm\Support\Str;
use Wpjscc\Docs\Models\Doc;

class Plugin extends PluginBase
{

    /**
     * Registers back-end quick actions for this plugin.
     *
     * @return array
     */
    public function registerQuickActions()
    {
        return [
            'help' => [
                'label' => 'wpjscc.docs::lang.links.docsLink',
                'icon' => 'icon-question-circle',
                'url' => Backend::url('docs'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerComponents()
    {
        return [
            \Wpjscc\Docs\Components\DocsPage::class => 'docsPage',
            \Wpjscc\Docs\Components\DocsList::class => 'docsList',
        ];
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            \Wpjscc\Docs\Console\DocsList::class,
            \Wpjscc\Docs\Console\DocsProcess::class,
        ]);
    }

    public function registerSearchHandlers()
    {
        $handlers = [];
        $docs = DocsManager::instance()->listDocumentation();

        foreach ($docs as $doc) {
            if (!$doc['instance']->isProcessed()) {
                continue;
            }

            // Find page connected to this documentation.
            $theme = Theme::getActiveTheme();
            $page = Page::inTheme($theme)->whereComponent('docsPage', 'component_type', $doc['component_type'])->first();

            $handlers['docs.' . $doc['id']] = [
                'name' => $doc['name'],
                'model' => function () use ($doc) {
                    if ($doc['instance'] instanceof MarkdownDocumentation) {
                        MarkdownPageIndex::clearBootedModels();
                        MarkdownPageIndex::setPageList($doc['instance']->getPageList());

                        return new MarkdownPageIndex();
                    }
                    PHPApiPageIndex::clearBootedModels();
                    PHPApiPageIndex::setPageList($doc['instance']->getPageList());

                    return new PHPApiPageIndex();
                },
                'record' => function ($record, $query) use ($page) {
                    $excerpt = Str::excerpt($record->content, $query);

                    if (is_null($excerpt)) {
                        if (Str::length($record->content) > 200) {
                            $excerpt = Str::substr($record->content, 0, 200) . '...';
                        } else {
                            $excerpt = $record->content;
                        }
                    }

                    return [
                        'title' => $record->title,
                        'description' => $excerpt,
                        'url' => Page::url($page->getBaseFileName(), ['slug' => $record->path]),
                    ];
                },
            ];
        }

        return $handlers;
    }

    public function registerDocumentation()
    {
        $docs = [];

        foreach (Doc::get() as $doc) {
            if ($doc->config) {
                if ($doc->config) {
                    $docs[$doc->key] = $doc->config;
                }
            }
        }
        return $docs;
        return [
            'wintercmsv12' => [
                'name' => 'Wintercms Documentation',
                'type' => 'md',
                'source' => 'remote',
                'service' => 'wintercms',
                'version' => 'v1.2',
                'component_type' => 'doc',
                'group' => 'doc',
                'sort' => 1,
                // 'source' => 'local',
                // 'path' => plugins_path('/wpjscc/docs/wintercms'),
                'url' => 'https://github.com/wpjscc/wintercms-docs/archive/refs/tags/v1.2.zip',
                'repository' => [
                    'editUrl' => 'https://github.com/wpjscc/wintercms-docs/edit/main/%s.md',
                    'url' => 'https://github.com/wpjscc/wintercms-docs'
                ]
            ],
            'wintercmsv11' => [
                'name' => 'Wintercms Documentation',
                'type' => 'md',
                'source' => 'remote',
                'service' => 'wintercms',
                'version' => 'v1.1',
                'component_type' => 'doc',
                'group' => 'doc',
                'sort' => 2,
                // 'source' => 'local',
                // 'path' => plugins_path('/wpjscc/docs/wintercms'),
                'url' => 'https://github.com/wpjscc/wintercms-docs/archive/refs/tags/v1.1.zip',
                'repository' => [
                    'editUrl' => 'https://github.com/wpjscc/wintercms-docs/edit/1.1/%s.md',
                    'url' => 'https://github.com/wpjscc/wintercms-docs/tree/1.1'
                ]
            ],
            'wintercmsv10' => [
                'name' => 'Wintercms Documentation',
                'type' => 'md',
                'source' => 'remote',
                'service' => 'wintercms',
                'version' => 'v1.0',
                'component_type' => 'doc',
                'group' => 'doc',
                'sort' => 3,
                // 'source' => 'local',
                // 'path' => plugins_path('/wpjscc/docs/wintercms'),
                'url' => 'https://github.com/wpjscc/wintercms-docs/archive/refs/tags/v1.0.zip',
                'repository' => [
                    'editUrl' => 'https://github.com/wpjscc/wintercms-docs/edit/1.0/%s.md',
                    'url' => 'https://github.com/wpjscc/wintercms-docs/tree/1.0'
                ]
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'locales' => [
                'label'       => '翻译设置',
                'description' => '',
                'icon'        => 'icon-language',
                'class'       => 'Wpjscc\Docs\Models\TranslateSetting',
                'order'       => 550,
                'category'    => 'Docs'
            ],
        ];
    }

    public static function writeToFile($sql)
    {

        // $sql is an object with the properties:
        //  sql: The query
        //  bindings: the sql query variables
        //  time: The execution time for the query
        //  connectionName: The name of the connection

        // To save the executed queries to file:
        // Process the sql and the bindings:
        foreach ($sql->bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
            } else {
                if (is_string($binding)) {
                    $sql->bindings[$i] = "'$binding'";
                }
            }
        }

        // Insert bindings into query
        $query = str_replace(['%', '?'], ['%%', '%s'], $sql->sql);

        $query = vsprintf($query, $sql->bindings);

        $runtime = $sql->time . 'ms';
        // Save the query to file
        $logFile = fopen(
            storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_query.log'),
            'a+'
        );
        fwrite($logFile, date('Y-m-d H:i:s') . ': ' . $query . PHP_EOL . $runtime. PHP_EOL);
        fclose($logFile);
    }
}
