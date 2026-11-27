<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    private const PER_PAGE = 8;

    public string $search = '';

    public string $tab = 'all';

    public bool $formOpen = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $sku = '';

    public int $quantity = 0;

    public string $price = '0.00';

    public string $description = '';

    public ?TemporaryUploadedFile $sellerPhoto = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $product = $this->ownedProducts()->findOrFail($id);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku ?? '';
        $this->quantity = $product->quantity;
        $this->price = $product->price;
        $this->description = $product->description ?? '';
        $this->formOpen = true;
        $this->resetValidation();
        $this->dispatch('product-edit');
    }

    public function selectTab(string $tab): void
    {
        abort_unless(in_array($tab, ['all', 'low-stock', 'out-of-stock', 'trash'], true), 404);

        $this->tab = $tab;
        $this->resetPage();
    }

    public function save(): void
    {
        $userId = (int) Auth::id();
        $product = $this->editingId
            ? $this->ownedProducts()->findOrFail($this->editingId)
            : null;
        $skuRule = Rule::unique('products', 'sku')
            ->where(fn ($query) => $query->where('user_id', $userId));

        if ($product) {
            $skuRule->ignore($product->getKey());
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50', $skuRule],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['sku'] = filled($data['sku']) ? trim($data['sku']) : null;

        if ($this->editingId) {
            $product->update($data);
            $message = 'Produk berhasil diperbarui.';
        } else {
            Auth::user()->products()->create($data);
            $message = 'Produk berhasil ditambahkan.';
        }

        $this->forgetInventoryCache($userId);
        $this->resetPage();
        $this->resetForm();
        $this->dispatch('notify', message: $message);
    }

    public function delete(int $id): void
    {
        $this->ownedProducts()->findOrFail($id)->delete();
        $this->forgetInventoryCache((int) Auth::id());
        $this->resetPage();
        $this->dispatch('notify', message: 'Produk dipindahkan ke sampah.');
    }

    public function restore(int $id): void
    {
        $this->ownedProducts()->onlyTrashed()->findOrFail($id)->restore();
        $this->forgetInventoryCache((int) Auth::id());
        $this->resetPage();
        $this->dispatch('notify', message: 'Produk berhasil dipulihkan.');
    }

    public function forceDelete(int $id): void
    {
        $this->ownedProducts()->onlyTrashed()->findOrFail($id)->forceDelete();
        $this->forgetInventoryCache((int) Auth::id());
        $this->resetPage();
        $this->dispatch('notify', message: 'Produk dihapus permanen.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function saveSellerPhoto(): void
    {
        $this->validate([
            'sellerPhoto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2048,max_height=2048'],
        ]);

        $user = Auth::user();
        $oldPhotoPath = $user->profile_photo_path;
        $newPhotoPath = $this->sellerPhoto?->store('seller-photos/'.$user->getKey(), 'local');

        if (! is_string($newPhotoPath)) {
            throw new RuntimeException('Foto profil gagal disimpan. Silakan coba kembali.');
        }

        if (! $user->update(['profile_photo_path' => $newPhotoPath])) {
            if (! Storage::disk('local')->delete($newPhotoPath)) {
                logger()->warning('Unable to remove an unsaved seller profile photo.', [
                    'user_id' => $user->getKey(),
                    'path' => $newPhotoPath,
                ]);
            }

            throw new RuntimeException('Foto profil gagal disimpan. Silakan coba kembali.');
        }

        if (filled($oldPhotoPath) && ! Storage::disk('local')->delete($oldPhotoPath)) {
            logger()->warning('Unable to delete the previous seller profile photo.', [
                'user_id' => $user->getKey(),
                'path' => $oldPhotoPath,
            ]);
        }

        $this->reset('sellerPhoto');
        $this->resetValidation();
        $this->dispatch('notify', message: 'Foto profil berhasil diperbarui.');
    }

    public function render()
    {
        $user = Auth::user();
        $userId = (int) $user->getKey();
        $stats = Cache::remember(
            "inventory:{$userId}:stats",
            now()->addSeconds(60),
            fn () => [
                'products' => Product::query()->where('user_id', $userId)->count(),
                'units' => (int) Product::query()->where('user_id', $userId)->sum('quantity'),
                'value' => (float) Product::query()
                    ->where('user_id', $userId)
                    ->selectRaw('COALESCE(SUM(quantity * price), 0) as total')
                    ->value('total'),
                'archived' => Product::onlyTrashed()->where('user_id', $userId)->count(),
            ],
        );

        $inventoryRows = Cache::remember(
            "inventory:{$userId}:products:v2",
            now()->addSeconds(60),
            fn () => Product::withTrashed()
                ->where('user_id', $userId)
                ->latest()
                ->get()
                ->toArray(),
        );
        $inventory = Product::hydrate($inventoryRows);

        $activeProducts = $inventory->reject(fn (Product $product): bool => $product->trashed());
        $tabCounts = [
            'all' => $activeProducts->count(),
            'low-stock' => $activeProducts
                ->filter(fn (Product $product): bool => $product->quantity > 0 && $product->quantity <= 5)
                ->count(),
            'out-of-stock' => $activeProducts
                ->filter(fn (Product $product): bool => $product->quantity === 0)
                ->count(),
            'trash' => $inventory->filter(fn (Product $product): bool => $product->trashed())->count(),
        ];

        $readinessItems = [
            ['label' => 'Unggah foto penjual', 'complete' => filled($user->profile_photo_path)],
            ['label' => 'Tambahkan produk pertama', 'complete' => $stats['products'] > 0],
            ['label' => 'Sediakan stok tersedia', 'complete' => $stats['units'] > 0],
        ];
        $completedReadinessSteps = count(array_filter($readinessItems, fn (array $item): bool => $item['complete']));
        $readinessProgress = (int) round($completedReadinessSteps / count($readinessItems) * 100);
        $readinessMessage = match ($readinessProgress) {
            100 => 'Toko siap beroperasi',
            0 => 'Mulai dari langkah pertama',
            default => 'Sedikit lagi menuju siap',
        };

        $search = mb_strtolower(trim($this->search));
        $matchingProducts = $inventory
            ->filter(function (Product $product) use ($search): bool {
                if ($this->tab === 'trash') {
                    if (! $product->trashed()) {
                        return false;
                    }
                } elseif ($product->trashed()) {
                    return false;
                }

                if ($this->tab === 'low-stock' && ($product->quantity < 1 || $product->quantity > 5)) {
                    return false;
                }

                if ($this->tab === 'out-of-stock' && $product->quantity !== 0) {
                    return false;
                }

                return $search === ''
                    || mb_stripos($product->name, $search) !== false
                    || mb_stripos((string) $product->sku, $search) !== false;
            })
            ->values();

        $page = $this->getPage();
        $products = new LengthAwarePaginator(
            $matchingProducts->forPage($page, self::PER_PAGE)->values(),
            $matchingProducts->count(),
            self::PER_PAGE,
            $page,
            ['path' => request()->url(), 'pageName' => 'page'],
        );

        return view('livewire.product-manager', [
            'products' => $products,
            'stats' => $stats,
            'tabCounts' => $tabCounts,
            'readinessItems' => $readinessItems,
            'completedReadinessSteps' => $completedReadinessSteps,
            'readinessProgress' => $readinessProgress,
            'readinessMessage' => $readinessMessage,
            'sellerPhotoPath' => $user->profile_photo_path,
        ]);
    }

    private function ownedProducts(): Builder
    {
        return Product::query()->where('user_id', Auth::id());
    }

    private function forgetInventoryCache(int $userId): void
    {
        Cache::forget("inventory:{$userId}:products:v2");
        Cache::forget("inventory:{$userId}:stats");
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'sku', 'quantity', 'price', 'description', 'formOpen']);
        $this->resetValidation();
    }
}
