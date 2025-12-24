<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\MakeCustomer;
use App\Models\Order;
use App\Models\Product;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PDF;

class OrderController extends Controller
{
  public function index()
{
    abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $orders = Order::with(['select_products', 'select_customer', 'created_by'])
        ->where('created_by_id', auth()->id()) // 👈 filter by logged-in user
        ->get();

    return view('admin.orders.index', compact('orders'));
}



public function create()
{
    abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Dropdown ke liye
    $select_products = Product::pluck('name', 'id');

    // Customer code select box
    $select_customers = MakeCustomer::pluck('customer_code', 'id')
        ->prepend(trans('global.pleaseSelect'), '');

    // Product details JSON ke liye
$products = Product::select('id','name','price','quantity')->get(); // collection without toArray

    return view('admin.orders.create', compact('select_customers', 'select_products', 'products'));
}



public function store(Request $request)
{
    $data = $request->validate([
        'select_customer_id' => 'required|exists:make_customers,id',
        'order_items'        => 'required|json',
        'grand_total'        => 'required|numeric|min:1',
        'total_discount'     => 'nullable|numeric',
    ]);

    // ✅ Generate unique order_id (Date + Random)
    $orderId = 'ET' . now()->format('YmdHis') . rand(1000, 9999);

    $order = Order::create([
        'select_customer_id' => $data['select_customer_id'],
        'grand_total'        => $data['grand_total'],
        'total_discount'     => $data['total_discount'],
        'created_by_id'      => auth()->id(),
        'order_id'           => $orderId, // 👈 yaha save ho raha hai
    ]);

    $items = json_decode($data['order_items'], true);

    foreach ($items as $item) {
        $order->products()->attach($item['product_id'], [
            'quantity'      => $item['quantity'],
            'price'         => $item['price'],
            'discount'      => $item['discount'],
            'discount_type' => $item['discount_type'],
            'total'         => $item['total'],
        ]);
    }

    return redirect()->route('admin.orders.index')->with('success', 'Order created successfully!');
}



    public function edit(Order $order)
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $select_products = Product::pluck('name', 'id');

        $select_customers = MakeCustomer::pluck('customer_code', 'id')->prepend(trans('global.pleaseSelect'), '');

        $order->load('select_products', 'select_customer');

        return view('admin.orders.edit', compact('order', 'select_customers', 'select_products'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->all());
        $order->select_products()->sync($request->input('select_products', []));

        return redirect()->route('admin.orders.index');
    }

    public function show(Order $order)
    {
        abort_if(Gate::denies('order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->load('select_products', 'select_customer');

        return view('admin.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        abort_if(Gate::denies('order_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->delete();

        return back();
    }

    public function massDestroy(MassDestroyOrderRequest $request)
    {
        $orders = Order::find(request('ids'));

        foreach ($orders as $order) {
            $order->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function downloadInvoice(Order $order)
{
    $order->load('products', 'select_customer');

    $pdf = Pdf::loadView('admin.orders.invoice', compact('order'))
              ->setPaper('a4', 'portrait');

    return $pdf->download('invoice_'.$order->id.'.pdf');
}
}