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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('served_time')->after('updated_at')->nullable();
            $table->string('served_total_time')->after('served_time')->nullable();
            $table->string('total_time')->after('served_total_time')->nullable();
            $table->string('order_status_served')->after('total_time')->nullable();
            $table->string('order_status_cooked')->after('order_status_served')->nullable();
            $table->string('update_status')->after('order_status_cooked')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
