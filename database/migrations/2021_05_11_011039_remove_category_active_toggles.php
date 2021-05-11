<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCategoryActiveToggles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_types', function (Blueprint $table) {                   $table->dropColumn('is_active');                    });
        Schema::table('fauna_categories', function (Blueprint $table) {                 $table->dropColumn('is_active');                    });
        Schema::table('flora_categories', function (Blueprint $table) {                 $table->dropColumn('is_active');                    });
        Schema::table('event_categories', function (Blueprint $table) {                 $table->dropColumn('is_active');                    });
        Schema::table('figure_categories', function (Blueprint $table) {                $table->dropColumn('is_active');                    });
        Schema::table('faction_types', function (Blueprint $table) {                    $table->dropColumn('is_active');                    });
        Schema::table('concept_categories', function (Blueprint $table) {               $table->dropColumn('is_active');                    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_types', function (Blueprint $table) {                   $table->boolean('is_active')->default(1);           });
        Schema::table('fauna_categories', function (Blueprint $table) {                 $table->boolean('is_active')->default(1);           });
        Schema::table('flora_categories', function (Blueprint $table) {                 $table->boolean('is_active')->default(1);           });
        Schema::table('event_categories', function (Blueprint $table) {                 $table->boolean('is_active')->default(1);           });
        Schema::table('figure_categories', function (Blueprint $table) {                $table->boolean('is_active')->default(1);           });
        Schema::table('faction_types', function (Blueprint $table) {                    $table->boolean('is_active')->default(1);           });
        Schema::table('concept_categories', function (Blueprint $table) {               $table->boolean('is_active')->default(1);           });

    }
}
