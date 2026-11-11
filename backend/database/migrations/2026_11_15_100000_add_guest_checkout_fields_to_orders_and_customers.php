<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compra sin cuenta con datos de facturación electrónica (DIAN, compatibles con Siigo) y dirección de entrega en columnas propias.
 *
 * orders: is_guest, copia de los datos de facturación (tipo y número de documento, DV, nombres, responsabilidad fiscal, correo, dirección,
 *   departamento y municipio con código DANE), dirección de entrega desglosada (para calcular el envío) y la autorización de datos
 *   (versión y fecha). Las columnas que ya existían (company_name, nit, contact_name, email, phone, city, address) se conservan.
 * customers: los mismos datos de facturación, para autocompletar el formulario de quien tiene cuenta.
 *
 * Idempotente: cada columna se agrega solo si falta (un despliegue cortado a medias se puede repetir).
 */
return new class extends Migration
{
    private const ORDER_COLUMNS = [
        'is_guest' => ['boolean', ['default' => false]],
        'billing_doc_type' => ['string', ['length' => 3, 'nullable' => true]],
        'billing_doc_number' => ['string', ['length' => 20, 'nullable' => true]],
        'billing_doc_dv' => ['string', ['length' => 1, 'nullable' => true]],
        'billing_first_name' => ['string', ['length' => 80, 'nullable' => true]],
        'billing_last_name' => ['string', ['length' => 80, 'nullable' => true]],
        'fiscal_responsibility' => ['string', ['length' => 30, 'nullable' => true]],
        'billing_email' => ['string', ['length' => 255, 'nullable' => true]],
        'billing_address' => ['string', ['length' => 255, 'nullable' => true]],
        'billing_department_code' => ['string', ['length' => 2, 'nullable' => true]],
        'billing_department' => ['string', ['length' => 80, 'nullable' => true]],
        'billing_city_code' => ['string', ['length' => 5, 'nullable' => true]],
        'delivery_department_code' => ['string', ['length' => 2, 'nullable' => true]],
        'delivery_department' => ['string', ['length' => 80, 'nullable' => true]],
        'delivery_city_code' => ['string', ['length' => 5, 'nullable' => true]],
        'delivery_city' => ['string', ['length' => 80, 'nullable' => true]],
        'delivery_neighborhood' => ['string', ['length' => 100, 'nullable' => true]],
        'delivery_road' => ['string', ['length' => 120, 'nullable' => true]],
        'delivery_number' => ['string', ['length' => 40, 'nullable' => true]],
        'delivery_complement' => ['string', ['length' => 120, 'nullable' => true]],
        'delivery_postal_code' => ['string', ['length' => 10, 'nullable' => true]],
        'delivery_notes' => ['string', ['length' => 500, 'nullable' => true]],
        'delivery_same_as_billing' => ['boolean', ['default' => false]],
        'privacy_accepted_at' => ['timestamp', ['nullable' => true]],
        'privacy_version' => ['string', ['length' => 20, 'nullable' => true]],
    ];

    private const CUSTOMER_COLUMNS = [
        'doc_type' => ['string', ['length' => 3, 'nullable' => true]],
        'doc_dv' => ['string', ['length' => 1, 'nullable' => true]],
        'first_name' => ['string', ['length' => 80, 'nullable' => true]],
        'last_name' => ['string', ['length' => 80, 'nullable' => true]],
        'fiscal_responsibility' => ['string', ['length' => 30, 'nullable' => true]],
        'billing_email' => ['string', ['length' => 255, 'nullable' => true]],
        'department_code' => ['string', ['length' => 2, 'nullable' => true]],
        'department' => ['string', ['length' => 80, 'nullable' => true]],
        'city_code' => ['string', ['length' => 5, 'nullable' => true]],
    ];

    public function up(): void
    {
        $this->add('orders', self::ORDER_COLUMNS);
        $this->add('customers', self::CUSTOMER_COLUMNS);

        if (! $this->hasIndex('orders', 'orders_is_guest_index')) {
            Schema::table('orders', fn (Blueprint $table) => $table->index('is_guest'));
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('orders', 'orders_is_guest_index')) {
            Schema::table('orders', fn (Blueprint $table) => $table->dropIndex('orders_is_guest_index'));
        }
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(array_keys(self::ORDER_COLUMNS)));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn(array_keys(self::CUSTOMER_COLUMNS)));
    }

    private function add(string $tableName, array $columns): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
            foreach ($columns as $name => [$type, $opts]) {
                if (Schema::hasColumn($tableName, $name)) {
                    continue;
                }

                $column = $type === 'string' ? $table->string($name, $opts['length']) : $table->{$type}($name);
                if ($opts['nullable'] ?? false) {
                    $column->nullable();
                }
                if (array_key_exists('default', $opts)) {
                    $column->default($opts['default']);
                }
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn (array $i) => $i['name'] === $index);
    }
};
