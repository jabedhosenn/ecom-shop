<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function __construct(
        private readonly ProductImageService $productImageService,
    ) {}

    /**
     * Display a listing of wishlist items.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $wishlists = Wishlist::query()
            ->with([
                'user:id,name,email',
                'product' => fn ($query) => $query->with([
                    'images' => fn ($imageQuery) => $imageQuery->where('is_primary', true),
                ]),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('product', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Wishlist $wishlist): array => $this->listPayload($wishlist));

        return Inertia::render('admin/wishlists/Index', [
            'wishlists' => $wishlists,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     user: array{id: int, name: string, email: string},
     *     product: array{
     *         id: int,
     *         name: string,
     *         slug: string,
     *         price: float,
     *         stock_status: string,
     *         is_active: bool,
     *         image: string|null
     *     },
     *     created_at: string
     * }
     */
    private function listPayload(Wishlist $wishlist): array
    {
        $product = $wishlist->product;
        $primaryImage = $product->images->firstWhere('is_primary', true)
            ?? $product->images->first();

        return [
            'id' => $wishlist->id,
            'user' => [
                'id' => $wishlist->user->id,
                'name' => $wishlist->user->name,
                'email' => $wishlist->user->email,
            ],
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'stock_status' => $product->stock_status,
                'is_active' => $product->is_active,
                'image' => $this->productImageService->resolveUrl($primaryImage?->image_path),
            ],
            'created_at' => $wishlist->created_at?->toIso8601String() ?? '',
        ];
    }
}
