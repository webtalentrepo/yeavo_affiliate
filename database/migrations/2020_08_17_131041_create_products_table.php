<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('network', 191)->index();
            $table->string('category', 191)->index();
            $table->string('child_category', 191)->index();
            $table->string('site_id', 191)->index();
            $table->integer('popular_rank')->index();
            $table->text('p_title')->nullable();
            $table->text('p_description')->nullable();
            $table->double('p_commission')->index();
            $table->string('p_commission_unit', 100);
            $table->double('p_gravity')->index();
            $table->double('p_percent_sale')->index();
            $table->boolean('deleted_flag')->index();
            $table->boolean('edited_flag')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
