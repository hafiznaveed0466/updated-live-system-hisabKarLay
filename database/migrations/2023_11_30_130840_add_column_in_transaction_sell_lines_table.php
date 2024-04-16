<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            $table->string('update_time')->nullable();
            $table->integer('update_status')->default(0);
            $table->string('total_time')->nullable();
            $table->integer('update_cooked_status')->default(0);
            $table->integer('inovice_status')->default(0);
            $table->integer('new_qty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            //
        });
    }
};
