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
            $table->string('summary_type')->default('abstractive')->after('entities');
            $table->string('sentiment_label')->nullable()->after('summary_type');
            $table->float('sentiment_score')->nullable()->after('sentiment_label');
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
