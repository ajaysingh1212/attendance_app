@extends('layouts.admin')
@section('content')

<div class="container mt-4" id="invoice">

    {{-- Header / Company Info --}}
    <table class="w-100 mb-4" style="border:none;">
        <tr>
            <td style="width: 20%; text-align:left; border:none;">
                <img src="{{ asset('logo.jpg') }}" alt="Company Logo" style="height:80px;">
            </td>
            <td style="width: 60%; text-align:center; border:none;">
                <h2 class="mb-1">EEMOTRACK INDIA PRIVATE LIMITED</h2>
                <p class="mb-0">HQ-2: GPS House, Kamla Market, R.K Bhattacharya Road,<br> Patna, Bihar - 800001</p>
                <p class="mb-0"><strong>GST NO:</strong> 10AQFPK9218DM1ZI</p>
                <h4 class="mt-2">Order Invoice</h4>
            </td>
            <td style="width: 20%; text-align:right; border:none;">
                <p class="mb-1"><strong>Date:</strong> {{ now()->format('d-m-Y') }}</p>
                <p class="mb-0"><strong>Order ID:</strong> {{ $order->id }}</p>
            </td>
        </tr>
    </table>

    {{-- Customer Info --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Customer Information</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-0">
                <tr>
                    <th style="width: 20%;">Customer Code:</th>
                    <td>{{ $order->select_customer->customer_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Customer Name:</th>
                    <td>{{ $order->select_customer->owner_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Shop Name:</th>
                    <td>{{ $order->select_customer->shop_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td>
                        {!! $order->select_customer->address_line_1 ?? '' !!},
                        {{ $order->select_customer->city ?? '' }},
                        {{ $order->select_customer->state ?? '' }},
                        {{ $order->select_customer->pincode ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td>{{ $order->select_customer->phone_number ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Product Info --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Products</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price (₹)</th>
                        <th>Discount</th>
                        <th>Discount Type</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($order->products as $index => $product)
                        @php $grandTotal += $product->pivot->total; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->pivot->quantity }}</td>
                            <td>{{ number_format($product->pivot->price, 2) }}</td>
                            <td>{{ $product->pivot->discount ?? 0 }}</td>
                            <td>{{ $product->pivot->discount_type ?? '-' }}</td>
                            <td><strong>{{ number_format($product->pivot->total, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($order->products->count() > 0)
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Grand Total (₹)</th>
                        <th><strong>{{ number_format($grandTotal, 2) }}</strong></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-4">
        <p class="mb-1">Thank you for your order!</p>
        <small>For any queries contact: support@eemotrack.com | +91-9876543210</small>
    </div>

    {{-- Print Button --}}
    <div class="mt-4 text-end">
         <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-success btn-download">
            <i class="fa fa-download"></i> Download PDF
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

</div>

@endsection
