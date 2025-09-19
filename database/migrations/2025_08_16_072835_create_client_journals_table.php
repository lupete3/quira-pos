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
        Schema::create('client_journals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained();
                $table->foreignId('sale_id')->nullable()->constrained();
                $table->foreignId('sale_return_id')->nullable()->constrained();
                $table->foreignId('debt_id')->nullable()->constrained('client_debts');
                $table->decimal('payment', 12, 2)->nullable();
                $table->dateTime('entry_date')->useCurrent();
                $table->text('description')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_journals');
    }
};
