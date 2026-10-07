<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/coupons/Index', [
            'coupons' => Coupon::query()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Coupon $coupon): array => $this->couponPayload($coupon))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/coupons/Create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        Coupon::query()->create($this->couponData($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Coupon created.')]);

        return to_route('admin.coupons.index');
    }

    public function edit(Coupon $coupon): Response
    {
        return Inertia::render('admin/coupons/Edit', [
            'coupon' => $this->couponPayload($coupon),
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->couponData($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Coupon updated.')]);

        return to_route('admin.coupons.index');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Coupon deleted.')]);

        return to_route('admin.coupons.index');
    }

    public function toggleStatus(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $coupon->is_active ? __('Coupon activated.') : __('Coupon deactivated.'),
        ]);

        return to_route('admin.coupons.index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function couponData(array $data): array
    {
        $data['discount_type'] = $data['discount_type'] === 'flat' ? 'fixed' : 'percentage';
        $data['is_active'] = $data['status'] === 'active';
        unset($data['status']);

        return $data;
    }

    /**
     * @return array{
     *     id: int,
     *     code: string,
     *     discount_type: 'percentage'|'flat',
     *     discount_value: float,
     *     minimum_order_amount: float,
     *     maximum_discount_amount: float|null,
     *     usage_limit: int|null,
     *     used_count: int,
     *     expires_at: string|null,
     *     status: 'active'|'inactive'
     * }
     */
    private function couponPayload(Coupon $coupon): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type === 'fixed' ? 'flat' : 'percentage',
            'discount_value' => (float) $coupon->discount_value,
            'minimum_order_amount' => (float) $coupon->minimum_order_amount,
            'maximum_discount_amount' => $coupon->maximum_discount_amount !== null
                ? (float) $coupon->maximum_discount_amount
                : null,
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
            'expires_at' => $coupon->expires_at?->format('Y-m-d\TH:i'),
            'status' => $coupon->is_active ? 'active' : 'inactive',
        ];
    }
}
