<?php namespace Wpjscc\Docs\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpjsccDocsDocs extends Migration
{
    public function up()
    {
        Schema::table('wpjscc_docs_docs', function($table)
        {
            $table->string('name', 255)->nullable();
            $table->text('config')->nullable();
            $table->string('key', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpjscc_docs_docs', function($table)
        {
            $table->dropColumn('name');
            $table->dropColumn('config');
            $table->dropColumn('key');
        });
    }
}
