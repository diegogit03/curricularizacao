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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['REVENUE', 'EXPENSE']);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('payable_accounts', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained();
        });

        Schema::table('receivable_accounts', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
