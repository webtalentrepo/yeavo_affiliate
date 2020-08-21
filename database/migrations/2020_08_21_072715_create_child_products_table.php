<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('child_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->index();
            $table->string('product_id', 100)->index();
            $table->string('advertiser_id', 100)->index();
            $table->string('ad_id', 100)->index();
            $table->string('source_feed_type', 100)->index();
            $table->text('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('target_country')->index();
            $table->string('brand', 200)->index();
            $table->text('link');
            $table->text('image_link');
            $table->double('p_amount');
            $table->string('p_currency', 50)->nullable();
            $table->double('s_amount');
            $table->string('s_currency', 100)->nullable();
            $table->string('catalog_id', 100)->nullable();
            $table->boolean('deleted_flag');
            $table->boolean('edited_flag');
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
        Schema::dropIfExists('child_products');
    }
}
