<?php 

namespace Wpjscc\Docs\Classes;

use Wpjscc\Docs\Models\TranslateContent as TranslateModel;

class TranslateContent
{
    public $service;
    public $path;
    public $local;
    public $contents;

    public function __construct($service, $local, $path, $contents)
    {
        $this->service = $service;
        $this->path = $path;
        $this->local = $local;
        $this->contents = $contents;
    }

    public function getTranslateContents()
    {
        $markdownSplits = (new MarkdownSplit())->splitMarkdownAtLevel($this->contents, true, $level = 5);

        $data = [];
        foreach ($markdownSplits as $key => $markdownSplit) {
            if ($markdownSplit['header_md']) {
                $headers[] = $markdownSplit['header'];

                $model = TranslateModel::where('service', $this->service)
                    ->where('local', $this->local)
                    ->where('path', $this->path)
                    ->where('header', implode('-', explode(' ', $markdownSplit['header'])))
                    ->first();

                if ($model) {
                    // $model->contents = $markdownSplit['body'];
                    // $model->save();
                } else {
                    $model = new TranslateModel();
                    $model->service = $this->service;
                    $model->local = $this->local;
                    $model->path = $this->path;
                    $model->header = implode('-', explode(' ', $markdownSplit['header']));
                    $model->header_md = $markdownSplit['header_md'];
                    $model->contents = $markdownSplit['body'];
                    $model->to_contents = $markdownSplit['body'];
                    $model->sort = $key;
                    $model->save();
                }

                $data[] = $model->header_md;
                $data[] = $model->to_contents;
            }
        }

        return implode("\n", $data);
    }

    
    
}