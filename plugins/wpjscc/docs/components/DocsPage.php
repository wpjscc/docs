<?php namespace Wpjscc\Docs\Components;

use App;
use View;
use Request;
use Cms\Classes\ComponentBase;
use Wpjscc\Docs\Classes\DocsManager;
use Winter\Storm\Exception\AjaxException;
use Winter\Translate\Classes\Translator;

class DocsPage extends ComponentBase
{

    /**
     * @var Winter\Translate\Classes\Translator Translator object.
     */
    protected $translator;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'wpjscc.docs::lang.components.docsPage.name',
            'description' => 'wpjscc.docs::lang.components.docsPage.description',
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [
            'service' => [
                'title' => 'wpjscc.docs::lang.components.docsPage.docId.title',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'wpjscc.docs::lang.components.docsId.placeholder',
            ],
            'version' => [
                'title' => 'wpjscc.docs::lang.components.docsPage.docId.title',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'wpjscc.docs::lang.components.docsId.placeholder',
            ],
            'component_type' => [
                'title' => 'wpjscc.docs::lang.components.docsPage.docId.title',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'wpjscc.docs::lang.components.docsId.placeholder',
            ],
            'pageSlug' => [
                'title' => 'wpjscc.docs::lang.components.docsPage.pageSlug.title',
                'description' => 'wpjscc.docs::lang.components.docsPage.pageSlug.description',
                'type' => 'string',
            ],
        ];
    }

    public function init()
    {
        $this->translator = Translator::instance();
    }

    /**
     * @inheritDoc
     */
    public function onRun()
    {
        $service = $this->property('service');
        $version = $this->property('version');
        $activeLocale = $this->translator->getLocale();

        // $docId = $this->property('docId');
        $docsManager = DocsManager::instance();
        $docId = $docsManager->getDocIdByServiceAndVersion($service, $version, $activeLocale);

        if (!$docId) {
            return $this->respond404();
        }
        $path = $this->property('pageSlug');

        // Load documentation
        $docs = $docsManager->getDocumentation($docId);

        if (!$docs || !$docs->isAvailable()) {
            return $this->respond404();
        }

        $pageList = $docs->getPageList();

        if (empty($path)) {
            $page = $pageList->getRootPage();
            return redirect()->to($this->controller->pageUrl($this->page->baseFileName, [
                'slug' => $page->getPath(),
            ]));
        } else {
            $paths = [
                $path,
                implode('-', explode('/', $path))
            ];
            foreach ($paths as $pathTmp) {
                $page = $pageList->getPage($pathTmp);
                if (is_null($page)) {
                    continue;
                   
                }
                $page->load($this->controller->pageUrl($this->page->baseFileName, [
                    'slug' => ''
                ]));
                break;
            }

            if (empty($page)) {
                throw new AjaxException([
                    'error' => 'The page that you have requested does not exist',
                ]);
            }
        }
        $pageList->setActivePage($page);

        $this->page['docId'] = $docs->getIdentifier();
        $this->page['service'] = $this->property('service');
        $this->page['version'] = $this->property('version');
        $this->page['slug'] = $this->property('pageSlug');
        $this->page['docName'] = $docs->getName();
        $this->page['docType'] = $docs->getType();
        $this->page['sourceUrl'] = $docs->getRepositoryUrl();
        $this->page['tocUrl'] = $docs->getTocUrl();
        $this->page['mainNav'] = $pageList->getNavigation();
        $this->page['title'] = $page->getTitle();
        $this->page['pagePath'] = $page->getPath();
        $this->page['content'] = $page->getContent();
        $this->page['editUrl'] = $page->getEditUrl();
        $this->page['pageNav'] = $page->getNavigation();
        $this->page['frontMatter'] = $page->getFrontMatter();
        $this->page['versions'] = $docsManager->getVersionsByServiceAndLocal($this->property('service'), $activeLocale);
    }

    public function onLoadPage()
    {
        $service = $this->property('service');
        $version = $this->property('version');
        $activeLocale = $this->translator->getLocale();
        // $docId = $this->property('docId');
        $docsManager = DocsManager::instance();
        $docId = $docsManager->getDocIdByServiceAndVersion($service, $version, $activeLocale);
        if (!$docId) {
            return $this->respond404();
        }
        $path = Request::post('page');

        // Load documentation
        $docs = $docsManager->getDocumentation($docId);

        if (!$docs || !$docs->isAvailable()) {
            throw new AjaxException([
                'error' => 'The documentation that you have requested does not exist',
            ]);
        }

        $pageList = $docs->getPageList();

        if (empty($path)) {
            $page = $pageList->getRootPage();
            return redirect()->to($this->controller->pageUrl($this->page->baseFileName, [
                'slug' => $page->getPath(),
            ]));
        } else {
            $paths = [
                $path,
                implode('-', explode('/', $path))
            ];
            foreach ($paths as $pathTmp) {
                $page = $pageList->getPage($pathTmp);
                if (is_null($page)) {
                    continue;
                   
                }
                $page->load($this->controller->pageUrl($this->page->baseFileName, [
                    'slug' => ''
                ]));
                break;
            }

            if (empty($page)) {
                throw new AjaxException([
                    'error' => 'The page that you have requested does not exist',
                ]);
            }
           
        }
        $pageList->setActivePage($page);

        return [
            'docId' => $docs->getIdentifier(),
            'docName' => $docs->getName(),
            'docType' => $docs->getType(),
            'title' => $page->getTitle(),
            'pagePath' => $page->getPath(),
            'frontMatter' => $page->getFrontMatter(),
            '#docs-menutop' => $this->renderPartial('@menutop', [
                'service' => $this->property('service'),
                'version' => $this->property('version'),
                'slug' => $path,
                'versions' => $docsManager->getVersionsByServiceAndLocal($this->property('service'), $activeLocale),
            ]),
            '#docs-menu' => $this->renderPartial('@menu', [
                'mainNav' => $pageList->getNavigation(),
                'docId' => $docs->getIdentifier(),
                'service' => $this->property('service'),
                'version' => $this->property('version'),
                'slug' => $path,
                'versions' => $docsManager->getVersionsByServiceAndLocal($this->property('service'), $activeLocale),
                'docName' => $docs->getName(),
                'docType' => $docs->getType(),
                'sourceUrl' => $docs->getRepositoryUrl(),
                'tocUrl' => $docs->getTocUrl(),
                'title' => $page->getTitle(),
                'pagePath' => $page->getPath(),
                'frontMatter' => $page->getFrontMatter(),
                'editUrl' => $page->getEditUrl(),
            ]),
            '#docs-content' => $this->renderPartial('@contents', [
                'content' => $page->getContent(),
                'docId' => $docs->getIdentifier(),
                'service' => $this->property('service'),
                'version' => $this->property('version'),
                'slug' => $path,
                'docName' => $docs->getName(),
                'docType' => $docs->getType(),
                'sourceUrl' => $docs->getRepositoryUrl(),
                'title' => $page->getTitle(),
                'pagePath' => $page->getPath(),
                'frontMatter' => $page->getFrontMatter(),
                'editUrl' => $page->getEditUrl(),
            ]),
            '#docs-toc' => $this->renderPartial('@toc', [
                'pageNav' => $page->getNavigation(),
                'docId' => $docs->getIdentifier(),
                'service' => $this->property('service'),
                'version' => $this->property('version'),
                'slug' => $path,
                'docName' => $docs->getName(),
                'docType' => $docs->getType(),
                'sourceUrl' => $docs->getRepositoryUrl(),
                'title' => $page->getTitle(),
                'pagePath' => $page->getPath(),
                'frontMatter' => $page->getFrontMatter(),
                'editUrl' => $page->getEditUrl(),
            ]),
        ];
    }

    /**
     * Get documentation options.
     *
     * @return array
     */
    public function getDocIdOptions()
    {
        $docsManager = DocsManager::instance();
        $docs = $docsManager->listDocumentation();
        $options = [];

        foreach ($docs as $doc) {
            $options[$doc['id']] = $doc['name'];
        }

        return $options;
    }

    /**
     * Responds with the correct 404 page depending on location.
     *
     * @return Response
     */
    protected function respond404()
    {
        if (App::runningInBackend()) {
            return response(View::make('backend::404'), 404);
        } else {
            $this->controller->setStatusCode(404);
            return $this->controller->run('404');
        }
    }
}
