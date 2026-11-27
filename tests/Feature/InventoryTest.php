<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\LogoutButton;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\ProductManager;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_through_livewire_with_a_hashed_password(): void
    {
        $this->withSession([]);

        Livewire::test(RegisterForm::class)
            ->set('name', 'Pemilik Toko')
            ->set('email', 'OWNER@example.com')
            ->set('password', 'rahasia-kuat-123')
            ->set('password_confirmation', 'rahasia-kuat-123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('rahasia-kuat-123', $user->password));
    }

    public function test_registration_normalizes_email_before_checking_uniqueness(): void
    {
        User::factory()->create(['email' => 'student@example.com']);

        Livewire::test(RegisterForm::class)
            ->set('name', 'Pemilik Toko')
            ->set('email', 'STUDENT@example.com')
            ->set('password', 'rahasia-kuat-123')
            ->set('password_confirmation', 'rahasia-kuat-123')
            ->call('register')
            ->assertHasErrors('email');

        $this->assertSame(1, User::query()->where('email', 'student@example.com')->count());
    }

    public function test_livewire_login_authenticates_the_user_and_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('rahasia-kuat-123'),
        ]);
        $this->withSession([]);

        Livewire::test(LoginForm::class)
            ->set('email', 'OWNER@example.com')
            ->set('password', 'rahasia-kuat-123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        auth()->logout();

        Livewire::test(LoginForm::class)
            ->set('email', 'owner@example.com')
            ->set('password', 'kata-sandi-salah')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_livewire_logout_ends_the_authenticated_session(): void
    {
        $this->withSession([]);
        $this->actingAs(User::factory()->create());

        Livewire::test(LogoutButton::class)
            ->call('logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_dashboard_shows_a_clear_logout_action(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Keluar');
    }

    public function test_a_user_can_create_edit_archive_and_restore_their_product(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ProductManager::class)
            ->set('name', 'Buku Tulis')
            ->set('sku', 'BK-001')
            ->set('quantity', 12)
            ->set('price', '8500')
            ->set('description', 'Stok awal')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify', message: 'Produk berhasil ditambahkan.');

        $product = Product::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(12, $product->quantity);

        Livewire::test(ProductManager::class)
            ->call('edit', $product->id)
            ->set('name', 'Buku Tulis Baru')
            ->call('save')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame('Buku Tulis Baru', $product->name);

        Livewire::test(ProductManager::class)
            ->call('delete', $product->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted($product);

        Livewire::test(ProductManager::class)
            ->call('selectTab', 'trash')
            ->call('restore', $product->id)
            ->assertHasNoErrors();

        $this->assertNotSoftDeleted($product);
    }

    public function test_store_readiness_progress_updates_as_the_seller_completes_each_step(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);

        $dashboard = Livewire::test(ProductManager::class)
            ->assertSee('0%')
            ->set('sellerPhoto', UploadedFile::fake()->image('seller.png', 600, 600))
            ->assertDontSeeHtml('disabled="disabled"')
            ->call('saveSellerPhoto')
            ->assertHasNoErrors()
            ->assertSee('33%');

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('local')->assertExists($user->profile_photo_path);

        $dashboard
            ->set('name', 'Produk pertama')
            ->set('quantity', 0)
            ->set('price', '12000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('67%');

        $product = $user->products()->firstOrFail();

        $dashboard
            ->call('edit', $product->id)
            ->set('quantity', 4)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('100%')
            ->assertSee('Toko siap beroperasi');
    }

    public function test_seller_photo_save_action_is_available_and_requires_a_photo(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductManager::class)
            ->assertDontSeeHtml(' disabled=')
            ->call('saveSellerPhoto')
            ->assertHasErrors(['sellerPhoto' => 'required']);
    }

    public function test_seller_profile_photo_endpoint_serves_the_authenticated_users_photo_privately(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->image('seller.png')->store('seller-photos/'.$user->id, 'local');
        $user->update(['profile_photo_path' => $path]);
        $this->actingAs($user);

        $this->get(route('seller.photo'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_seller_profile_photo_endpoint_does_not_expose_another_users_photo(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $path = UploadedFile::fake()->image('seller.png')->store('seller-photos/'.$owner->id, 'local');
        $owner->update(['profile_photo_path' => $path]);
        $this->actingAs(User::factory()->create());

        $this->get(route('seller.photo'))->assertNotFound();
    }

    public function test_seller_profile_photo_endpoint_redirects_guests_to_login(): void
    {
        $this->get(route('seller.photo'))->assertRedirect(route('login'));
    }

    public static function invalidSellerPhotoCases(): array
    {
        return [
            'non-image file' => ['non-image', 'image'],
            'file exceeds the size limit' => ['oversized', 'max'],
            'image exceeds the dimension limit' => ['oversized-dimensions', 'dimensions'],
        ];
    }

    #[DataProvider('invalidSellerPhotoCases')]
    public function test_seller_profile_photo_upload_rejects_invalid_files(string $case, string $rule): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $photo = match ($case) {
            'non-image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            'oversized' => UploadedFile::fake()->image('large.png')->size(2049),
            'oversized-dimensions' => UploadedFile::fake()->image('wide.png', 2049, 2048),
        };

        Livewire::test(ProductManager::class)
            ->set('sellerPhoto', $photo)
            ->call('saveSellerPhoto')
            ->assertHasErrors(['sellerPhoto' => $rule]);

        $this->assertNull($user->fresh()->profile_photo_path);
        $this->assertFalse(Storage::disk('local')->exists('seller-photos/'.$user->id));
    }

    public function test_a_user_can_permanently_delete_a_trashed_product(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'user_id' => $user->id,
            'name' => 'Produk permanen',
            'quantity' => 1,
            'price' => 1000,
        ]);
        $product->delete();
        $this->actingAs($user);

        Livewire::test(ProductManager::class)
            ->call('selectTab', 'trash')
            ->call('forceDelete', $product->id)
            ->assertHasNoErrors()
            ->assertDispatched('notify', message: 'Produk dihapus permanen.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_a_user_cannot_permanently_delete_another_users_trashed_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::query()->create([
            'user_id' => $owner->id,
            'name' => 'Produk privat',
            'quantity' => 1,
            'price' => 1000,
        ]);
        $product->delete();
        $this->actingAs(User::factory()->create());

        Livewire::test(ProductManager::class)
            ->call('forceDelete', $product->id)
            ->assertStatus(404);

        $this->assertSoftDeleted($product);
    }

    public function test_a_user_only_sees_their_own_inventory(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherProduct = Product::query()->create([
            'user_id' => $otherUser->id,
            'name' => 'Barang milik orang lain',
            'quantity' => 1,
            'price' => 1000,
        ]);

        $this->actingAs($user);

        Livewire::test(ProductManager::class)
            ->assertDontSee($otherProduct->name);
    }

    public function test_dashboard_stats_are_cached_and_invalidated_after_a_change(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ProductManager::class);
        $cacheKey = "inventory:{$user->id}:stats";
        $productsCacheKey = "inventory:{$user->id}:products:v2";
        $this->assertTrue(cache()->has($cacheKey));
        $this->assertTrue(cache()->has($productsCacheKey));
        $this->assertSame(0, cache()->get($cacheKey)['products']);

        Livewire::test(ProductManager::class)
            ->set('name', 'Barang baru')
            ->set('quantity', 3)
            ->set('price', '1000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Barang baru');

        $this->assertSame(1, cache()->get($cacheKey)['products']);
        $this->assertSame(3, cache()->get($cacheKey)['units']);
        $this->assertSame('Barang baru', cache()->get($productsCacheKey)[0]['name']);

        Livewire::test(ProductManager::class)
            ->assertSee('Barang baru');
    }

    public function test_dashboard_ignores_a_stale_legacy_product_cache_with_database_cache_store(): void
    {
        $user = User::factory()->create();
        $user->products()->create([
            'name' => 'Barang tersimpan',
            'quantity' => 3,
            'price' => 1000,
        ]);
        $this->actingAs($user);
        config(['cache.default' => 'database']);

        cache()->put("inventory:{$user->id}:products", new \stdClass, now()->addMinute());

        Livewire::test(ProductManager::class)
            ->assertSee('Barang tersimpan');

        $cachedRows = cache()->get("inventory:{$user->id}:products:v2");

        $this->assertIsArray($cachedRows);
        $this->assertSame('Barang tersimpan', $cachedRows[0]['name']);
    }

    public function test_search_and_stock_tabs_filter_the_cached_product_list(): void
    {
        $user = User::factory()->create();
        $user->products()->createMany([
            ['name' => 'Mouse Wireless', 'sku' => 'EL-001', 'quantity' => 4, 'price' => 50000],
            ['name' => 'Buku Catatan', 'sku' => 'ATK-002', 'quantity' => 0, 'price' => 10000],
            ['name' => 'Pulpen', 'sku' => 'ATK-003', 'quantity' => 20, 'price' => 5000],
        ]);
        $this->actingAs($user);

        Livewire::test(ProductManager::class)
            ->set('search', 'Mouse')
            ->assertSee('Mouse Wireless')
            ->assertDontSee('Buku Catatan')
            ->set('search', '')
            ->call('selectTab', 'low-stock')
            ->assertSee('Mouse Wireless')
            ->assertDontSee('Buku Catatan')
            ->assertDontSee('Pulpen')
            ->call('selectTab', 'out-of-stock')
            ->assertSee('Buku Catatan')
            ->assertDontSee('Mouse Wireless')
            ->call('selectTab', 'trash')
            ->assertSee('Sampah masih kosong')
            ->assertDontSee('Mouse Wireless');
    }

    public function test_product_names_and_descriptions_are_escaped_in_the_dashboard(): void
    {
        $user = User::factory()->create();
        $dangerousText = '<script>alert("xss")</script>';
        $user->products()->create([
            'name' => $dangerousText,
            'description' => $dangerousText,
            'quantity' => 1,
            'price' => 1000,
        ]);
        $this->actingAs($user);

        Livewire::test(ProductManager::class)
            ->assertSee($dangerousText)
            ->assertDontSee($dangerousText, false);
    }

    public function test_login_page_shows_keranjangku_brand_and_slogan(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('KeranjangKu')
            ->assertSee('Stok rapi,')
            ->assertSee('vibes happy.');
    }

    public function test_registration_page_renders_the_branded_livewire_form(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('KeranjangKu')
            ->assertSee('Buat akun')
            ->assertSee('Bikin akun, mulai sat-set.');
    }

    public function test_database_seeder_creates_five_products_for_the_demo_account(): void
    {
        $this->seed();

        $demoUser = User::query()->where('email', 'demo@example.com')->firstOrFail();

        $this->assertSame(5, $demoUser->products()->count());
    }
}
