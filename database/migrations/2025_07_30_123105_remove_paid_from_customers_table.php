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
        Schema::table('customers', function (Blueprint $table) {
            // Verifica se a coluna 'paid' existe antes de tentar removê-la
            if (Schema::hasColumn('customers', 'paid')) {
                $table->dropColumn('paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Ao reverter, adicione a coluna 'paid' de volta como boolean (ou como tinyInteger se for o caso)
            // CUIDADO: Este `down` não restaura dados, apenas a estrutura.
            $table->boolean('paid')->default(false)->after('delivered');
        });
    }
};
