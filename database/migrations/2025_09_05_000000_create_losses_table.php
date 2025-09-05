<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('losses', function (Blueprint $table) {
            $table->id();
            $table->string('reason');                 // motivo do prejuízo
            $table->decimal('amount', 10, 2);         // valor do prejuízo
            $table->date('loss_date')->nullable();    // opcional: quando ocorreu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
