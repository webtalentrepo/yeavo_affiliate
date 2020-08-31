<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 100);
            $table->string('product_id', 100)->index();
            $table->text('product_url');
            $table->double('amount');
            $table->boolean('free_plan');
            $table->tinyInteger('duration');
            $table->string('duration_schedule', 10);
            $table->integer('keyword_limit');
            $table->enum('status', ['Active', 'Inactive', 'Deleted']);
            $table->text('description');
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
        Schema::dropIfExists('plans');
    }
}
