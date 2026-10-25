<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            // 1:1 con users. Si se borra el usuario se borra su ficha de cliente;
            // los pedidos no se pierden porque guardan copia de los datos.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('nit', 20)->unique();
            $table->string('contact_name');
            $table->string('phone', 30);
            $table->string('city', 100);
            $table->string('address')->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->timestamp('marketing_opt_in_at')->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
