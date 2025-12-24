<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - EEMOTRACK INDIA PRIVATE LIMITED</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; color:#333; }
        .invoice-box {
            max-width: 900px; margin:auto; background:#fff; padding:25px;
            border:1px solid #ddd; box-shadow:0 0 10px rgba(0,0,0,.1);
        }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        table td, table th { padding:10px; border:1px solid #ddd; font-size:14px; }
        table th { background:#f1f1f1; text-align:left; }
        .no-border td, .no-border th { border:none; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .grand-total { font-weight:bold; font-size:15px; background:#f9f9f9; }
        h2, h3 { margin:5px 0; }
        .footer { text-align:center; font-size:12px; color:#555; margin-top:30px; }
        .sign-table td { height:60px; vertical-align:bottom; }
    </style>
</head>
<body>
<div class="invoice-box">

    <!-- Company Header -->
    <table class="no-border">
        <tr>
            <td style="width:30%;">
                <img src="{{ public_path('logo.jpg') }}" alt="Company Logo" style="max-height:70px;">
            </td>
            <td class="text-center" style="width:40%;">
                <h2>EEMOTRACK INDIA PRIVATE LIMITED</h2>
                <p>HQ-2: GPS House, Kamla Market, R.K Bhattacharya Road,<br>Patna, Bihar - 800001</p>
                <p><strong>GST NO:</strong> 10AQFPK9218DM1ZI</p>
            </td>
            <td class="text-right" style="width:30%;">
                <h3>INVOICE</h3>
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>Date:</strong> {{ now()->format('d-m-Y') }}</p>
            </td>
        </tr>
    </table>

    <!-- Customer Info -->
    <h3>Customer Information</h3>
    <table>
        <tr>
            <th style="width:25%;">Customer Code</th>
            <td>{{ $order->select_customer->customer_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Customer Name</th>
            <td>{{ $order->select_customer->owner_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Shop Name</th>
            <td>{{ $order->select_customer->shop_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>
                {{ $order->select_customer->address_line_1 ?? '' }},
                {{ $order->select_customer->city ?? '' }},
                {{ $order->select_customer->state ?? '' }} - 
                {{ $order->select_customer->pincode ?? '' }}
            </td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $order->select_customer->phone_number ?? '-' }}</td>
        </tr>
    </table>

    <!-- Products -->
    <h3>Products</h3>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Price (₹)</th>
            <th class="text-center">Discount</th>
            <th class="text-center">Type</th>
            <th class="text-right">Total (₹)</th>
        </tr>
        </thead>
        <tbody>
        @php $grandTotal = 0; @endphp
        @forelse($order->products as $i => $product)
            @php $grandTotal += $product->pivot->total; @endphp
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-center">{{ $product->pivot->quantity }}</td>
                <td class="text-right">{{ number_format($product->pivot->price, 2) }}</td>
                <td class="text-center">{{ $product->pivot->discount ?? 0 }}</td>
                <td class="text-center">{{ $product->pivot->discount_type ?? '-' }}</td>
                <td class="text-right">{{ number_format($product->pivot->total, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">No products found</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="grand-total">
            <td colspan="6" class="text-right">Grand Total</td>
            <td class="text-right">₹{{ number_format($grandTotal, 2) }}</td>
        </tr>
        </tfoot>
    </table>

    <!-- Signatures -->
    <table class="sign-table no-border">
        <tr>
            <td class="text-center">Customer Signature</td>
            <td class="text-center">Authorized Signatory</td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your order!</p>
        <p>For queries contact: support@eemotrack.com | +91-9876543210</p>
        <p>Terms: Payment due within 30 days of invoice date</p>
    </div>

</div>
</body>
</html>
