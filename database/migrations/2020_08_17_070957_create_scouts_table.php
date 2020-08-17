<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scouts', function (Blueprint $table) {
            $table->id();
            $table->string('network', 100)->index();
            $table->string('site_id', 100)->index();
            $table->bigInteger('user_id')->index();
            $table->string('advertiser_id', 100)->index();
            $table->string('account_status', 50)->nullable();
            $table->double('seven_day_epc');
            $table->double('three_month_epc');
            $table->string('advertiser_name', 191)->index();
            $table->text('program_url')->nullable();
            $table->string('relationship_status')->nullable();
            $table->boolean('mobile_supported');
            $table->boolean('mobile_tracking_certified');
            $table->boolean('cookieless_tracking_enabled');
            $table->integer('network_rank');
            $table->string('primary_category_parent', 191)->index();
            $table->string('primary_category_child', 191)->index();
            $table->boolean('performance_incentives');
            $table->longText('action')->nullable();
            $table->longText('link_types')->nullable();
            $table->string('default_commission')->nullable();
            $table->string('commission_unit')->nullable();
            $table->string('commission_value')->nullable();
            $table->string('commission_currency')->nullable();
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
        Schema::dropIfExists('scouts');
    }
}
