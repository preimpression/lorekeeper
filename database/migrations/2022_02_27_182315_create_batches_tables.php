<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id')->unsigned();        // ID of Batch

            $table->string('target_type');                  // Model type of target
            $table->integer('target_id')->unsigned();       // ID of target
        });

        Schema::create('batch_triggers', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id')->unsigned()->index();                        // ID of Batch

            $table->string('trigger_type')->nullable()->default(null);              // Model type and string of trigger
            $table->integer('trigger_id')->unsigned()->nullable()->default(null);   // ID of trigger

            $table->timestamp('trigger_at')->nullable()->default(null);             // In the absense of a trigger type and id, this will trigger at a particular time
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('batch_targets');
        Schema::dropIfExists('batch_triggers');
    }
}
