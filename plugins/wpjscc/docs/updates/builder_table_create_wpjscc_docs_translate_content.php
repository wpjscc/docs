<?php namespace Wpjscc\Docs\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpjsccDocsTranslateContent extends Migration
{
    public function up()
    {
        Schema::create('wpjscc_docs_translate_contents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('service');
            $table->string('local');
            $table->string('path');
            $table->string('header');
            $table->string('header_md');
            $table->text('contents');
            $table->text('to_contents');
            $table->index(['service', 'local', 'path'], 'service_local_path');
            $table->bool('is_translate')->default(0);
            $table->bool('is_translated')->default(0);
            $table->integer('sort')->unsigned()->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('wpjscc_docs_translate_contents');
    }
}
