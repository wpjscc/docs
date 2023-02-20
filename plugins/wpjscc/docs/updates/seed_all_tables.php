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
            'config' => '{"name":"Wintercms","is_hidden":"0","type":"md","source":"remote","url":"https:\/\/github.com\/wpjscc\/wintercms-docs\/archive\/refs\/tags\/v1.2-zh-cn.zip","path":"","service":"wintercms","version":"v1.2","local":"zh-CN","is_translate":"1","sort":"1","group":"\u4e2d\u6587","component_type":"doc","repository":{"url":"https:\/\/github.com\/wpjscc\/wintercms-docs\/tree\/main-zh-cn","editUrl":"https:\/\/github.com\/wpjscc\/wintercms-docs\/edit\/main-zh-cn\/%s.md","tocUrl":"https:\/\/github.com\/wpjscc\/wintercms-docs\/edit\/main-zh-cn\/config\/toc-docs.yaml"},"tocPath":"","forceTranslates":["architecture-introduction.md"],"image":""}',
        ]);

    }
}
