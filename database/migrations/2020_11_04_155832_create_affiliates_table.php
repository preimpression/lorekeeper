<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('staff_id')->unsigned()->nullable()->default(null); // For admin who approved affiliate
            $table->text('staff_comment')->nullable()->default(null); // Message from admin on rejection
            
            $table->string('name'); // Name of site or game
            $table->string('description')->nullable()->default(null); // Short description to show on hover

            $table->string('slug')->nullable()->default(null); // Randomized Slug

            $table->boolean('is_featured')->default(0); // Whether the affiliate is a "sister site" or is "featured"

            $table->string('url'); // Site url
            $table->string('image_url')->nullable()->default(null); // Just in case, nullable.

            $table->enum('status', ['Pending', 'Accepted', 'Rejected'])->default('Pending'); // Status
            
            $table->integer('user_id')->unsigned()->nullable()->default(null); // If a user was logged in when submitting request form
            $table->string('guest_name')->nullable()->default(null); // Name of the person submitting, if guest
            $table->text('message')->nullable()->default(null); // Message from affiliate form

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
        Schema::dropIfExists('affiliates');
    }
}
