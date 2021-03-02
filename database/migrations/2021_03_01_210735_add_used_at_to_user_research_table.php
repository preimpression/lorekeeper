<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsedAtToUserResearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_research', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->default(null);
            $table->boolean('rewards_claimed')->default(0);
        });

        Schema::create('research_rewards', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('research_id')->unsigned()->default(0);
            $table->string('rewardable_type');
            $table->integer('rewardable_id')->unsigned();
            $table->integer('quantity')->unsigned();

            $table->foreign('research_id')->references('id')->on('researches');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('research_rewards');
        Schema::table('user_research', function (Blueprint $table) {
            $table->dropColumn('used_at');
            $table->dropColumn('rewards_claimed');
        });
    }
}
