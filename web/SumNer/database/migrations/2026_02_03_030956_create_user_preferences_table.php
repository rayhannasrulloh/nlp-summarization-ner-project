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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Abstractive
            $table->integer('abstractive_min_length')->default(40);
            $table->integer('abstractive_max_length')->default(150);
            $table->integer('abstractive_num_beams')->default(4);
            
            // Extractive
            $table->float('extractive_retention_ratio')->default(0.3); // 30%
            
            // NER (reserved for future)
            $table->float('ner_threshold')->default(0.50);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
