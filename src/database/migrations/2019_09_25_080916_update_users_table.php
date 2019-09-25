<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    const COLUMNS = [
        'iam_uid' => 'uuid',
        'email' => 'string',
        'name' => 'string',
        'surname' => 'string',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            foreach (self::COLUMNS as $column => $type) {
                if (!Schema::hasColumn("users", $column)) {
                    $table->{$type}($column)->nullable();
                } else {
                    $table->{$type}($column)->nullable()->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
