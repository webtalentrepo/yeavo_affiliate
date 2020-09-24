<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('activated');
            $table->string('activation_code', 190)->index();
            $table->boolean('banned');
            $table->string('api_key', 100);
            $table->bigInteger('current_plan')->index();
            $table->string('persist_code');
            $table->text('image_ext');
            $table->string('company');
            $table->string('address');
            $table->string('city', 100);
            $table->string('postal_code', 20);
            $table->string('country', 200);
            $table->string('state_code', 200);
            $table->string('phone', 100);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
