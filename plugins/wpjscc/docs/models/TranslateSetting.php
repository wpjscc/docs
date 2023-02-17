<?php namespace Wpjscc\Docs\Models;

use Model;

/**
 * TranslateSetting Model
 */
class TranslateSetting extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var array Behaviors implemented by this model.
     */
    public $implement = [\System\Behaviors\SettingsModel::class];

    /**
     * @var string Unique code
     */
    public $settingsCode = 'wpjscc_docs_translatesetting';

    /**
     * @var mixed Settings form field definitions
     */
    public $settingsFields = 'fields.yaml';
    
    /**
     * @var array Validation rules
     */
    public $rules = [];
}
