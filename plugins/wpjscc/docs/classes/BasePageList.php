<?php

namespace Wpjscc\Docs\Classes;

use System\Classes\PluginManager;
use Wpjscc\Docs\Classes\Contracts\Page;
use Wpjscc\Docs\Classes\Contracts\PageList;

abstract class BasePageList implements PageList
{
    /**
     * The root page of the documentation.
     */
    protected Page $rootPage;

    /**
     * The active page of the documentation.
     */
    protected ?Page $activePage = null;

    /**
     * @var array<string, Page> Available pages, keyed by path.
     */
    protected array $pages = [];

    /**
     * The navigation of the documentation.
     */
    protected array $navigation = [];

    /**
     * @inheritDoc
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @inheritDoc
     */
    public function getPage(string $path): ?Page
    {
        if (!array_key_exists($path, $this->pages)) {
            return null;
        }

        $page = $this->pages[$path];

        return $page;
    }

    /**
     * @inheritDoc
     */
    public function setActivePage(Page $page): void
    {
        $this->activePage = $page;
        $this->guessNavigation($page);
        $fullNav = $this->navigation;

        $childIterator = function (&$nav) use (&$childIterator) {
            $childActive = false;
            if (isset($nav['path'])) {
                $guessPath = implode('-', explode('/', $nav['path']));
                if ((isset($this->pages[$nav['path']]) && $this->pages[$nav['path']] === $this->activePage) || (isset($this->pages[$guessPath]) && $this->pages[$guessPath] === $this->activePage)) {
                    $nav['active'] = true;
                    $childActive = true;
                } else {
                    unset($nav['active']);
                }
            }

            if (isset($nav['children'])) {
                foreach ($nav['children'] as $subKey => &$subNav) {
                    if ($childIterator($subNav) === true) {
                        $childActive = true;
                    }
                }

                if ($childActive) {
                    $nav['childActive'] = true;
                } else {
                    unset($nav['childActive']);
                }
            }

            return $childActive;
        };

        // Find the page within the navigation and add an "active" state to the page, and a
        // "childActive" state to the section.
        foreach ($fullNav as $key => &$nav) {
            $childIterator($nav);
        }

        $this->navigation = $fullNav;
    }

    /**
     * @inheritDoc
     */
    public function getRootPage(): Page
    {
        return $this->rootPage;
    }

    /**
     * @inheritDoc
     */
    public function nextPage(Page $page): ?Page
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function previousPage(Page $page): ?Page
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNavigation(): array
    {
        return $this->navigation;
    }

    /**
     * @inheritDoc
     */
    public function search(string $query): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isSearchable(): bool
    {
        $pluginManager = PluginManager::instance();
        return $pluginManager->exists('Winter.Search');
    }

    /**
     * @inheritDoc
     */
    abstract public function index(): void;

    /**
     * @inheritDoc
     */
    public function getDocsIdentifier(): string
    {
        return $this->docs->getIdentifier();
    }

    public function guessNavigation(Page $page)
    {
        $path = $page->getPath();
        // dd($this->navigation);
        $guessNavigation = function ($navigation, $isNest = false) use (&$guessNavigation, $path) {
            foreach ($navigation as $nav) {
               
                if (isset($nav['root'])) {
                    if ($this->guessEqualPath($nav['path'], $path)) {
                        $this->navigation  = $nav['children'];
                        $this->rootPage  = $this->pages[$nav['root']];
                        break;
                    }
                    if (!empty($nav['children'])) {

                        $state = $guessNavigation($nav['children'], true);
                        if ($state) {
                            $this->navigation  = $nav['children'];
                            $this->rootPage  = $this->pages[$nav['root']];
                            break;
                        }
                    }

                    // dd($nav, $path);

                } else {

                    // 是配置项
                    if (isset($nav['path'])) {
                        if ($isNest) {
                            if ($this->guessEqualPath($nav['path'], $path)) {
                                return true;
                            } 
                            if (!empty($nav['children'])) {
                                $state = $guessNavigation($nav['children'], true);   
                                if ($state) {
                                    return true;
                                }
                            }
                        }
                       
                    } else {
                        if (!empty($nav['children'])) {
                            if ($isNest) {
                                $state = $guessNavigation($nav['children'], true);
                                if ($state) {
                                    return true;
                                }
                            } else {
                                $guessNavigation($nav['children']);
                            }
                        }
                    }
                }
            }

            return false;
        };

        $guessNavigation($this->navigation);
  
    }

    public function guessEqualPath($navPath, $pagePath)
    {
        $pathSplits = explode('-', $pagePath);
        if (count($pathSplits) > 2) {
            // 第一个是目录
            $dir = array_shift($pathSplits);
            $guessPath = $dir. '/'. implode('-', $pathSplits);
        } else {
            $guessPath = implode('/', $pathSplits);
        }

        if ($navPath == $pagePath || $navPath == $guessPath) {
            
            return true;
        }
        return false;
    }


}
