<?php namespace Wpjscc\Docs\Models;

use Model;

/**
 * Model
 */
class Doc extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpjscc_docs_docs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [
        'config'
    ];

    public function beforeCreate()
    {
        if (!$this->key) {
            $this->key = str_random(10);
        }
       
    }
}
