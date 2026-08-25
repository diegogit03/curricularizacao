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
        Schema::create('receivable_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('status', ['RECEIVED', 'PENDING']);
            $table->decimal('value', 12, 2);
            $table->date('due_at');
            $table->date('received_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_accounts');
    }
};
