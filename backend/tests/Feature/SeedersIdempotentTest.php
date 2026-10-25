<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\Concerns\SeedsUsers;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PDOException;
use RuntimeException;
use Tests\TestCase;

class SeedersIdempotentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.seed.admin_password' => 'clave-admin', 'app.seed.comercial_password' => 'clave-comercial', 'app.seed.customer_password' => 'clave-cliente']);
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        return [
            'users' => User::count(),
            'customers' => Customer::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
            'products' => Product::withTrashed()->count(),
            'orders' => Order::count(),
            'items' => OrderItem::count(),
            'histories' => OrderStatusHistory::count(),
        ];
    }

    public function test_database_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $first = $this->counts();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($first, $this->counts());
        $this->assertSame(3, $first['orders']);
    }

    public function test_second_run_does_not_overwrite_changes_made_after_the_first(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', config('app.seed.admin_email'))->firstOrFail();
        $admin->update(['password' => 'clave-nueva']);
        $product = Product::where('sku', '00118ET3E143T-W22')->firstOrFail();
        $product->update(['price' => 999000, 'stock' => 1]);

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check('clave-nueva', $admin->fresh()->password));
        $this->assertEquals(999000, $product->fresh()->price);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_deleted_product_is_not_recreated(): void
    {
        $this->seed(DatabaseSeeder::class);
        Product::where('sku', '00118ET3E143T-W22')->firstOrFail()->delete();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Product::where('sku', '00118ET3E143T-W22')->count());
        $this->assertSame(1, Product::onlyTrashed()->where('sku', '00118ET3E143T-W22')->count());
    }

    public function test_user_creation_errors_never_show_the_password(): void
    {
        // Simula el error del motor (p. ej. un duplicado): el SQL de QueryException lleva el hash
        User::creating(function (User $user) {
            $pdo = new PDOException('SQLSTATE[23000]: Duplicate entry');
            $pdo->errorInfo = ['23000', 1062, "Duplicate entry 'x' for key 'users_email_unique'"];

            throw new QueryException('mysql', 'insert into `users` (`password`) values (?)', [$user->password], $pdo);
        });

        $seeder = new class
        {
            use SeedsUsers;

            public function make(): User
            {
                return $this->seedUser('Prueba', 'prueba@imagenelectrica.test', 'clave-secreta-123', UserRole::Cliente);
            }
        };

        try {
            $seeder->make();
            $this->fail('Debía lanzar una excepción.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('prueba@imagenelectrica.test', $e->getMessage());
            $this->assertStringContainsString('Duplicate entry', $e->getMessage());
            $this->assertStringNotContainsString('clave-secreta-123', (string) $e);
            $this->assertStringNotContainsString('$2y$', (string) $e);
            $this->assertNull($e->getPrevious());
        }
    }

    public function test_missing_password_message_names_the_variable_only(): void
    {
        config(['app.seed.admin_password' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Falta definir SEED_ADMIN_PASSWORD en el archivo .env');

        $this->seed(DatabaseSeeder::class);
    }
}
