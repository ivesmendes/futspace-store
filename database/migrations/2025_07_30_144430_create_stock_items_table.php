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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('description'); // Descrição do item (ex: "Camisa Flamengo Zico 81")
            $table->decimal('wholesale_value', 10, 2); // Valor de atacado (custo de aquisição)
            $table->decimal('suggested_sale_value', 10, 2); // Valor que sugere vender (equivalente ao order_value para o cliente)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
