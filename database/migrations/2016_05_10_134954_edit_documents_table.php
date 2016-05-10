<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function ($table) {
            if (Schema::hasColumn('documents', 'path')) {
                $table->dropColumn('path');
            }

            $table->string('folder');
            $table->boolean('public');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function ($table) {
            if (Schema::hasColumns('documents', ['folder', 'public'])) {
                $table->dropColumn(['folder', 'public']);
            }

            $table->string('path');
        });
    }
}
