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
            $table->timestamp('trigger_at')->nullable()->default(null);             // In the absense of triggers, this will trigger at a particular time
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id')->unsigned();        // ID of Batch

            $table->string('target_type');                  // Model type of target
            $table->integer('target_id')->unsigned();       // ID of target
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
    }
}
