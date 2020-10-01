<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResearchTreeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trees', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name', 191); // What users and admins will see the table as. 
            $table->string('summary', 300)->nullable()->default(null); // Summary of tree.
            $table->text('description')->nullable()->default(null); // Text description of the tree
            $table->text('parsed_description')->nullable()->default(null); // Text description of the tree, except parsed this time

            $table->string('image_url', 191)->nullable()->default(null); // Null if no image, simple path if yes image.
            $table->integer('sort')->unsigned()->default(0); // Sort the tree. I will figure out how to do this eventually

            $table->integer('currency_id')->unsigned(); // Currency, from currencies table

            $table->boolean('is_active')->default(1); // Can users see/obtain research from this tree?
            $table->softDeletes();
        });
        
        Schema::create('researches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name', 191); // What users and admins will see the table as. 
            $table->string('summary', 300)->nullable()->default(null); // Summary of research.
            $table->text('description')->nullable()->default(null); // Text description of the tree
            $table->text('parsed_description')->nullable()->default(null); // Text description of the tree, except parsed this time

            $table->string('icon_code', 191)->default('fas fa-sitemap'); // Null if no image, simple path if yes image.
            $table->integer('sort')->unsigned()->default(0); // Sort the tree. I will figure out how to do this eventually

            $table->integer('tree_id')->unsigned()->nullable()->default(null); // Tree, from trees table
            $table->integer('prerequisite_id')->unsigned()->nullable()->default(null); // Prerequisite id, from this table. Automatically set to parent_id unless specifically set to somethign else.
            $table->integer('parent_id')->unsigned()->nullable()->default(null); // Parent id, from this table.
            $table->integer('price')->unsigned()->default(0); // Price for this particular research - currency type taken from the tree.

            $table->boolean('is_active')->default(1); // Can users see/obtain this research?
            $table->softDeletes();
        });
        
        Schema::create('user_research', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            // Create the link between the table and the user
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // this ondelete cascade thing was encouraged from the internet, it causes me fear

            // Create the link between the table and the research
            $table->unsignedInteger('research_id');
            $table->foreign('research_id')->references('id')->on('researches')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            

        });
        Schema::create('user_research_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('tree_id')->unsigned()->index()->nullable(); // Tree, from trees table
            $table->integer('research_id')->unsigned()->index(); // Research, from researches table

            $table->string('data', 191); // a message perhaps

            $table->integer('recipient_id')->unsigned()->index()->nullable()->default(null); // User who gained the research, from users table
            $table->integer('sender_id')->unsigned()->index()->nullable()->default(null); // Either Admin who granted or null, from users table
            
            $table->integer('currency_id')->unsigned()->nullable()->default(null); // Currency, from currencies table
            $table->integer('cost')->default(0); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_research_log');
        Schema::dropIfExists('user_research');
        Schema::dropIfExists('researches');
        Schema::dropIfExists('trees');
    }
}
