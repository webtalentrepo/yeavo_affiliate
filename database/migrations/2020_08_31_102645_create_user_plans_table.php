<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->bigInteger('plan_id')->unsigned();
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');

            $table->boolean('free_pack');
            $table->tinyInteger('duration');
            $table->string('duration_schedule', 10);
            $table->double('amount');
            $table->string('currency_code');
            $table->enum('payment_status', ['success', 'failed', 'pending']);
            $table->enum('status', ['Active', 'Inactive', 'Pending', 'Expired', 'Deleted', 'Cancelled']);
            $table->dateTime('activated_on')->index();
            $table->dateTime('expiry_on')->index();
            $table->string('invoice_code', 100);
            $table->enum('payment_method', ['Stripe', 'Paypal', 'Authorize', 'Paykickstart', 'JVZoo'])->index();
            $table->boolean('free_flag');
            $table->timestamps();

            $table->unique(['id', 'user_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_plans');
    }
}
