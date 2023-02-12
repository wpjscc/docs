<?php

namespace Wpjscc\Docs\Components;

use Cms\Classes\ComponentBase;
use Wpjscc\Docs\Classes\DocsManager;

class DocsList extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'wpjscc.docs::lang.components.docsList.name',
            'description' => 'wpjscc.docs::lang.components.docsList.description',
        ];
    }

    public function docs()
    {
        // Load documentation
        $docsManager = DocsManager::instance();
        return collect($docsManager->listDocumentation())->groupBy('doc.service')->map(function($items){
            return $items->sortBy('doc.sort')->first();
        });
    }
}
