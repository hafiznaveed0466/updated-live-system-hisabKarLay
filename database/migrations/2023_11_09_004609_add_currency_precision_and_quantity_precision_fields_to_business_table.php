<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyPrecisionAndQuantityPrecisionFieldsToBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('business', function (Blueprint $table) {
        //     $table->tinyInteger('currency_precision')->default(2)->after('time_format');
        //     $table->tinyInteger('quantity_precision')->default(2)->after('currency_precision');
        //     $table->integer('enable_is_other_currency')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
