<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Solicitudes de cotización enviadas desde la tienda sin crear cuenta. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('email');
            $table->string('city', 100);
            $table->string('company', 160)->nullable();
            $table->string('nit', 30)->nullable();
            $table->text('message')->nullable();
            // Copia de lo que pidió: [{product_id?, sku?, name, label?, line?, quantity}]
            $table->json('items');
            $table->timestamp('privacy_accepted_at');
            $table->string('status', 20)->default('new'); // App\Enums\QuoteRequestStatus
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
