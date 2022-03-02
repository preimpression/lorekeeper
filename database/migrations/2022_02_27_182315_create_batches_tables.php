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
            $table->timestamp('trigger_at')->nullable()->default(null);  // Optional automated trigger time
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id')->unsigned();        // ID of Batch

            $table->string('target_type');                  // Model type of target
            $table->integer('target_id')->unsigned();       // ID of target
        });

        Schema::create('batch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name');                                       // For quick reference
            $table->integer('batch_id')->unsigned();                            // ID of Batch. Yes, it's soft deleted, but it's good to keep track.
            $table->integer('staff_id')->unsigned()->nullable()->default(null); // ID of Batch
            $table->text('data');                                               // To keep track of targets
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
        Schema::dropIfExists('batches');
        Schema::dropIfExists('batch_targets');
        Schema::dropIfExists('batch_logs');
    }
}
