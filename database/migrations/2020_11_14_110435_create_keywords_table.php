<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keywords')->index();
            $table->text('result');
            $table->enum('type', ['exact', 'non', 'broad'])->index();
            $table->string('volume');
            $table->json('trend');
            $table->string('state', 20);
            $table->string('bid_low', 20);
            $table->string('bid_high', 20);
            $table->double('competition');
            $table->date('write_date');
            $table->boolean('updated_flag');
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
        Schema::dropIfExists('keywords');
    }
}
