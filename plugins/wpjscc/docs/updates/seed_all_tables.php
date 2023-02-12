<?php namespace Wpjscc\Docs\Updates;

use Carbon\Carbon;
use Wpjscc\Docs\Models\Doc;

use Winter\Storm\Database\Updates\Seeder;

class SeedAllTables extends Seeder
{

    public function run()
    {
        Doc::extend(function ($model) {
            $model->setTable('wpjscc_docs_docs');
        });

        Doc::create([
            'name' => 'Wintercms 中文文档',
            'key' => 'wintercms-zh-cn',
            'config' => '{"name":"Wintercms","type":"md","source":"remote","service":"wintercms","version":"v1.2","component_type":"doc","group":"中文","local":"zh-cn","sort":1,"url":"https://github.com/wpjscc/wintercms-docs/archive/refs/tags/v1.2-zh-cn.zip","repository":{"editUrl":"https://github.com/wpjscc/wintercms-docs/edit/main-zh-cn/%s.md","tocUrl":"https://github.com/wpjscc/wintercms-docs/edit/main-zh-cn/config/toc-docs.yaml","url":"https://github.com/wpjscc/wintercms-docs/tree/main-zh-cn"}}',
        ]);
        Doc::create([
            'name' => 'Wintercms 英文文档',
            'key' => 'wintercms-en',
            'config' => '{"name":"Wintercms","type":"md","source":"remote","service":"wintercms","version":"v1.2","component_type":"doc","group":"英文","is_hidden":true,"local":"en","sort":10,"url":"https://github.com/wpjscc/wintercms-docs/archive/refs/tags/v1.2-en.zip","repository":{"editUrl":"https://github.com/wpjscc/wintercms-docs/edit/main/%s.md","tocUrl":"https://github.com/wpjscc/wintercms-docs/edit/main/config/toc-docs.yaml","url":"https://github.com/wpjscc/wintercms-docs"}}',
        ]);

    }
}
