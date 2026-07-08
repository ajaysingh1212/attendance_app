<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Carbon\Carbon;

class OrderApiController extends Controller
{
    /**
     * ✅ API: Create Order
     */
    public function store(Request $request)
    {
        // Validate request
        $data = $request->validate([
            'select_customer_id' => 'required|exists:make_customers,id',
            'created_by_id'      => 'required|exists:users,id',
            'grand_total'        => 'required|numeric|min:1',
            'total_discount'     => 'nullable|numeric',
            'order_items'        => 'required|array|min:1', // array of products
            'order_items.*.product_id'      => 'required|exists:products,id',
            'order_items.*.quantity'        => 'required|numeric|min:1',
            'order_items.*.price'           => 'required|numeric|min:0',
            'order_items.*.discount'        => 'nullable|numeric|min:0',
            'order_items.*.discount_type'   => 'nullable|string',
            'order_items.*.total'           => 'required|numeric|min:0',
        ]);

        // Generate unique order_id
        $orderId = 'ET' . now()->format('YmdHis') . rand(1000, 9999);

        // Save to orders table
        $order = Order::create([
            'select_customer_id' => $data['select_customer_id'],
            'created_by_id'      => $data['created_by_id'],
            'grand_total'        => $data['grand_total'],
            'total_discount'     => $data['total_discount'] ?? 0,
            'order_id'           => $orderId,
        ]);

        // Attach products to order
        foreach ($data['order_items'] as $item) {
            $order->products()->attach($item['product_id'], [
                'quantity'      => $item['quantity'],
                'price'         => $item['price'],
                'discount'      => $item['discount'] ?? 0,
                'discount_type' => $item['discount_type'] ?? null,
                'total'         => $item['total'],
            ]);
        }

        // Return JSON response
        return response()->json([
            'success'  => true,
            'message'  => 'Order created successfully',
            'order_id' => $order->order_id,
            'order'    => $order->load('products')
        ], Response::HTTP_CREATED);
    }
    
    
    /**
     * ✅ Fetch orders by created_by_id (user id)
     */
    public function getOrdersByUser($userId)
    {
        // Orders with products and customer
        $orders = Order::with(['products', 'select_customer'])
            ->where('created_by_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found for this user',
                'data'    => []
            ], Response::HTTP_OK);
        }

        // Format response
        $formatted = $orders->map(function ($order) {
    // Calculate grand_total dynamically
    $grandTotal = $order->products->sum(function ($product) {
        return $product->pivot->total;
    });

    return [
        'id'         => $order->id,
        'order_id'   => $order->order_id,
        'created_at' => $order->created_at->toDateTimeString(),
        'grand_total'=> $grandTotal,  // dynamically calculated
        'total_discount' => 0,        // as you don't need discount now
        'customer'   => $order->select_customer ? [
            'id'   => $order->select_customer->id,
            'code' => $order->select_customer->customer_code,
            'shop' => $order->select_customer->shop_name,
        ] : null,
        'products' => $order->products->map(function ($product) {
            return [
                'id'    => $product->id,
                'name'  => $product->name,
                'slug'  => $product->slug, // add this line
                'pivot' => [
                    'quantity'      => $product->pivot->quantity,
                    'price'         => $product->pivot->price,
                    'discount'      => $product->pivot->discount,
                    'discount_type' => $product->pivot->discount_type,
                    'total'         => $product->pivot->total,
                ]
            ];
        })

    ];
});


        return response()->json([
            'success' => true,
            'message' => 'Orders fetched successfully',
            'data'    => $formatted
        ], Response::HTTP_OK);
    }
    
    
    /**
     * ✅ Get order counts for a user
     */
    public function getOrderCounts($userId)
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalOrders = Order::where('created_by_id', $userId)->count();

        $todayOrders = Order::where('created_by_id', $userId)
            ->whereDate('created_at', $today)
            ->count();

        $monthOrders = Order::where('created_by_id', $userId)
            ->whereDate('created_at', '>=', $startOfMonth)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Order counts fetched successfully',
            'data' => [
                'today' => $todayOrders,
                'this_month' => $monthOrders,
                'total' => $totalOrders,
            ]
        ], Response::HTTP_OK);
    }
    
    
}
