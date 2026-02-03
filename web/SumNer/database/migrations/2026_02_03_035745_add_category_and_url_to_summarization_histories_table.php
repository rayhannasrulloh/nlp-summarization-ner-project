<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('summarization_histories', function (Blueprint $table) {
            $table->string('category')->nullable()->after('summary_type');
            $table->text('input_url')->nullable()->after('input_text');
            $table->text('image_url')->nullable()->after('summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('summarization_histories', function (Blueprint $table) {
            //
        });
    }
};
