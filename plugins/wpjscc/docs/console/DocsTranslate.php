<?php namespace Wpjscc\Docs\Console;

use Winter\Storm\Console\Command;
use Wpjscc\Docs\Models\TranslateContent as TranslateModel;
use Wpjscc\Docs\Classes\ChatgptTranslate;

class DocsTranslate extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'docs:translate';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'docs:translate
        {id : The identifier of the documentation to process}
        {token : The token}
        {proxy : The proxy}
        {--f|force : Force the operation to run and ignore production warnings and confirmation questions.}';

    /**
     * @var string The console command description.
     */
    protected $description = 'No description provided yet...';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $chatgpt = new ChatgptTranslate($this->argument('token'), $this->argument('proxy'));

        $models = TranslateModel::where([
            'service' => $this->argument('id'),
            'is_translate' => 1,
            'is_translated' => 0,
        ])->get();

        foreach ($models as $model) {
            
            if ($model->is_translate && !$model->is_translated) {
                $result = $chatgpt->getTranslateContent($model->contents);
                if ($result) {
                    $model->to_contents = $result;
                    $model->is_translated = 1;
                    $model->save();
                }
            }
        }


    }

    /**
     * Provide autocomplete suggestions for the "myCustomArgument" argument
     */
    // public function suggestMyCustomArgumentValues(): array
    // {
    //     return ['value', 'another'];
    // }
}
