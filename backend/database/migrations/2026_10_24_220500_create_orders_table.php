<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // PED-0001 / COT-0001
            $table->string('type', 20);   // App\Enums\OrderType
            $table->string('status', 20); // App\Enums\OrderStatus

            // Si se borra el cliente, el comercial o la cotización de origen,
            // el pedido se conserva: guarda copia de los datos del cliente.
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('orders')->nullOnDelete();

            // Copia de los datos del cliente al momento de la solicitud
            $table->string('company_name');
            $table->string('nit', 20);
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('city', 100);
            $table->string('address')->nullable();

            $table->string('delivery_method', 20); // App\Enums\DeliveryMethod
            $table->string('payment_method', 30)->nullable();

            // Nulos mientras la cotización no tenga precios
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('tax', 12, 2)->nullable();
            $table->decimal('shipping', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Listados del panel y dashboard: filtran por tipo y estado
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
