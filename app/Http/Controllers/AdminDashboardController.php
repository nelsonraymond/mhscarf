<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin operational dashboard.
     */
    public function index(Request $request): View
    {
        $actionableStatuses = ['confirmed', 'processing', 'packed'];

        $totalOrders = Order::query()->count();
        $awaitingPayment = Order::query()
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->count();
        $needsFulfillment = Order::query()
            ->whereIn('status', $actionableStatuses)
            ->count();
        $activeProducts = Product::query()->where('is_active', true)->count();
        $stockUnits = (int) ProductVariant::query()->sum('stock_qty');
        $lowStockCount = ProductVariant::query()
            ->where(function ($query) {
                $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                    ->orWhere('stock_qty', '<=', 0);
            })
            ->count();
        $paidRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
        $unpublishedReviews = Review::query()->where('is_published', false)->count();

        $recentOrders = Order::query()
            ->with('user:id,name,email')
            ->latest('placed_at')
            ->latest('id')
            ->limit(8)
            ->get();

        $actionableOrders = Order::query()
            ->with('user:id,name,email')
            ->where(function ($query) use ($actionableStatuses) {
                $query->where(function ($pending) {
                    $pending->where('payment_status', 'pending')
                        ->where('status', '!=', 'cancelled');
                })->orWhereIn('status', $actionableStatuses);
            })
            ->latest('placed_at')
            ->latest('id')
            ->limit(8)
            ->get();

        $topProducts = OrderItem::query()
            ->selectRaw('product_id, MAX(product_name) as product_name, SUM(quantity) as units_sold, SUM(subtotal) as revenue')
            ->whereHas('order', function ($query) {
                $query->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled');
            })
            ->groupBy('product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $lowStockVariants = ProductVariant::query()
            ->with('product:id,name,sku')
            ->where(function ($query) {
                $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                    ->orWhere('stock_qty', '<=', 0);
            })
            ->orderBy('stock_qty')
            ->limit(8)
            ->get();

        $paymentBreakdown = Order::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $refundOrders = Order::query()
    ->with('user:id,name,email')
    ->whereHas('payments', fn ($q) => $q->where('metadata->needs_refund', true))
    ->latest('id')
    ->limit(5)
    ->get();

$refundCount = Order::query()
    ->whereHas('payments', fn ($q) => $q->where('metadata->needs_refund', true))
    ->count();

        return view('admin.dashboard', [
            'admin' => $request->user(),
            'totalOrders' => $totalOrders,
            'awaitingPayment' => $awaitingPayment,
            'needsFulfillment' => $needsFulfillment,
            'activeProducts' => $activeProducts,
            'stockUnits' => $stockUnits,
            'lowStockCount' => $lowStockCount,
            'paidRevenue' => $paidRevenue,
            'unpublishedReviews' => $unpublishedReviews,
            'recentOrders' => $recentOrders,
            'actionableOrders' => $actionableOrders,
            'topProducts' => $topProducts,
            'lowStockVariants' => $lowStockVariants,
            'paymentBreakdown' => $paymentBreakdown,
            'refundOrders' => $refundOrders,
'refundCount' => $refundCount,
        ]);
    }
}
