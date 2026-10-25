<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // App\Enums\CustomerType. Los clientes que ya existían son empresas.
            $table->string('type', 20)->default('company')->after('user_id');
            // La persona natural no tiene razón social y su cédula (guardada en nit) es opcional
            $table->string('company_name')->nullable()->change();
            $table->string('nit', 20)->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            // Copia del tipo de cliente, igual que el resto de sus datos
            $table->string('customer_type', 20)->default('company')->after('quote_id');
            $table->string('company_name')->nullable()->change();
            $table->string('nit', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Las personas naturales no caben en el esquema anterior: se vuelven datos de empresa
        DB::table('customers')->whereNull('company_name')->orWhereNull('nit')->orderBy('id')->each(function ($row) {
            DB::table('customers')->where('id', $row->id)->update([
                'company_name' => $row->company_name ?? $row->contact_name,
                'nit' => $row->nit ?? 'NAT-'.$row->id,
            ]);
        });
        DB::table('orders')->whereNull('company_name')->update(['company_name' => DB::raw('contact_name')]);
        DB::table('orders')->whereNull('nit')->update(['nit' => '']);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('company_name')->nullable(false)->change();
            $table->string('nit', 20)->nullable(false)->change();
            $table->dropColumn('customer_type');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_name')->nullable(false)->change();
            $table->string('nit', 20)->nullable(false)->change();
            $table->dropColumn('type');
        });
    }
};
