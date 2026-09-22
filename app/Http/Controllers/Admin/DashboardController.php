<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\OrderValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use OrderValidationRules;

    private const int REVENUE_CHART_DAYS = 30;

    /**
     * Display the admin analytics dashboard.
     */
    public function index(): Response
    {
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $currentMonthStart->copy()->subMonth();
        $previousMonthEnd = $currentMonthStart->copy()->subSecond();

        $paidOrdersQuery = Order::query()->where('payment_status', 'paid');

        $totalRevenue = (float) (clone $paidOrdersQuery)->sum('total');
        $currentMonthRevenue = (float) (clone $paidOrdersQuery)
            ->where('placed_at', '>=', $currentMonthStart)
            ->sum('total');
        $previousMonthRevenue = (float) (clone $paidOrdersQuery)
            ->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total');

        $totalOrders = Order::query()->count();
        $currentMonthOrders = Order::query()
            ->where('placed_at', '>=', $currentMonthStart)
            ->count();
        $previousMonthOrders = Order::query()
            ->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $paidOrderCount = (int) (clone $paidOrdersQuery)->count();
        $averageOrderValue = $paidOrderCount > 0
            ? round($totalRevenue / $paidOrderCount, 2)
            : 0.0;

        return Inertia::render('admin/dashboard/Index', [
            'overview' => [
                'total_revenue' => $totalRevenue,
                'revenue_change_percent' => $this->percentChange($currentMonthRevenue, $previousMonthRevenue),
                'total_orders' => $totalOrders,
                'orders_change_percent' => $this->percentChange((float) $currentMonthOrders, (float) $previousMonthOrders),
                'average_order_value' => $averageOrderValue,
                'pending_orders' => Order::query()->where('status', 'pending')->count(),
                'total_customers' => User::query()->where('role', 'customer')->count(),
                'new_customers_this_month' => User::query()
                    ->where('role', 'customer')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->count(),
                'total_products' => Product::query()->count(),
                'active_products' => Product::query()->where('is_active', true)->count(),
                'out_of_stock_products' => Product::query()->where('stock_status', 'stock_out')->count(),
                'total_wishlists' => Wishlist::query()->count(),
            ],
            'revenue_chart' => $this->revenueChart(),
            'orders_by_status' => $this->ordersByStatus(),
            'payment_status_breakdown' => $this->paymentStatusBreakdown(),
            'payment_method_breakdown' => $this->paymentMethodBreakdown(),
            'top_products' => $this->topProducts(),
            'top_categories' => $this->topCategories(),
            'recent_orders' => $this->recentOrders(),
        ]);
    }

    /**
     * @return list<array{date: string, label: string, revenue: float, orders: int}>
     */
    private function revenueChart(): array
    {
        $startDate = now()->subDays(self::REVENUE_CHART_DAYS - 1)->startOfDay();

        /** @var Collection<string, object{date: string, revenue: string|float|null, orders: int|string}> $grouped */
        $grouped = Order::query()
            ->where('payment_status', 'paid')
            ->where('placed_at', '>=', $startDate)
            ->select([
                DB::raw('DATE(placed_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chart = [];

        for ($day = 0; $day < self::REVENUE_CHART_DAYS; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateKey = $date->toDateString();
            $row = $grouped->get($dateKey);

            $chart[] = [
                'date' => $dateKey,
                'label' => $date->format('M j'),
                'revenue' => round((float) ($row->revenue ?? 0), 2),
                'orders' => (int) ($row->orders ?? 0),
            ];
        }

        return $chart;
    }

    /**
     * @return list<array{status: string, label: string, count: int}>
     */
    private function ordersByStatus(): array
    {
        $counts = Order::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return collect(self::orderStatuses())
            ->map(fn (string $status): array => [
                'status' => $status,
                'label' => $this->statusLabel($status),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{status: string, label: string, count: int}>
     */
    private function paymentStatusBreakdown(): array
    {
        $counts = Order::query()
            ->select('payment_status', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status');

        return collect(self::paymentStatuses())
            ->map(fn (string $status): array => [
                'status' => $status,
                'label' => $this->statusLabel($status),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{method: string, label: string, count: int}>
     */
    private function paymentMethodBreakdown(): array
    {
        $counts = Order::query()
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method');

        return collect(['cod', 'sslcommerz'])
            ->map(fn (string $method): array => [
                'method' => $method,
                'label' => $method === 'cod' ? 'Cash on Delivery' : 'SSLCommerz',
                'count' => (int) ($counts[$method] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     sold_count: int,
     *     price: float,
     *     category_name: string
     * }>
     */
    private function topProducts(): array
    {
        return Product::query()
            ->with('category:id,name')
            ->orderByDesc('sold_count')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sold_count' => (int) $product->sold_count,
                'price' => (float) $product->price,
                'category_name' => $product->category?->name ?? '—',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, products_count: int}>
     */
    private function topCategories(): array
    {
        return Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'products_count' => (int) $category->products_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{
     *     id: int,
     *     order_number: string,
     *     customer_name: string,
     *     total: float,
     *     status: string,
     *     payment_status: string,
     *     placed_at: string|null
     * }>
     */
    private function recentOrders(): array
    {
        return Order::query()
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'placed_at' => $order->placed_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusLabel(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }
}
