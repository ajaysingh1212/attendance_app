@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.order.title_singular') }}
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.orders.store') }}" enctype="multipart/form-data" id="orderForm">
            @csrf
            
            {{-- Customer Select --}}
            <div class="form-group">
                <label class="required" for="select_customer_id">{{ trans('cruds.order.fields.select_customer') }}</label>
                <select class="form-control select2" name="select_customer_id" id="select_customer_id" required>
                    @foreach($select_customers as $id => $entry)
                        <option value="{{ $id }}">{{ $entry }}</option>
                    @endforeach
                </select>
                @error('select_customer_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Product Selection Section --}}
            <div class="form-group">
                <label for="product_select">Select Product</label>
                <select id="product_select" class="form-control select2">
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->quantity }}"
                            data-name="{{ $product->name }}">
                            {{ $product->name }} (₹{{ number_format($product->price, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Product Details Card --}}
            <div class="row" id="product_cards" style="display:none;">
                <div class="col-md-6">
                    <div class="card shadow p-3 mb-4">
                        <h5 id="prod_name"></h5>
                        <p>Price: ₹<span id="prod_price"></span></p>
                        <p>Available Qty: <span id="prod_stock"></span></p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow p-3 mb-4">
                        <input type="hidden" id="edit_index" value="-1">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" id="order_qty" class="form-control" min="1" value="1">
                            <small class="text-danger" id="qty_error" style="display:none;"></small>
                        </div>
                        <div class="form-group">
                            <label>Discount Type</label>
                            <select id="disc_type" class="form-control">
                                <option value="percent">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="form-group" id="disc_percent_group">
                            <label>Discount (%)</label>
                            <input type="number" id="disc_percent" class="form-control" min="0" max="100" value="0">
                        </div>
                        <div class="form-group" id="disc_value_group" style="display:none;">
                            <label>Discount (₹)</label>
                            <input type="number" id="disc_value" class="form-control" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label>Final Price</label>
                            <input type="text" id="final_price" class="form-control" readonly>
                        </div>
                        <div class="btn-group">
                            <button type="button" id="add_to_order" class="btn btn-primary">Add to Order</button>
                            <button type="button" id="update_order" class="btn btn-success" style="display:none;">Update</button>
                            <button type="button" id="cancel_edit" class="btn btn-secondary" style="display:none;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Items Table --}}
            <div class="table-responsive">
                <table class="table table-bordered" id="order_table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows will be added here -->
                    </tbody>
                </table>
            </div>

            {{-- Order Summary --}}
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <strong>Subtotal:</strong>
                                <span id="subtotal">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Total Discount:</strong>
                                <span id="total_discount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Grand Total:</strong>
                                <span id="grand_total">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden Fields for Form Submission --}}
            <input type="hidden" name="total_discount" id="hidden_total_discount" value="0">
            <input type="hidden" name="grand_total" id="hidden_grand_total" value="0">
            <input type="hidden" name="order_items" id="order_items" value="">

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fa fa-save"></i> {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize select2
    $('.select2').select2();

    // Product selection handler
    $('#product_select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const productId = $(this).val();
        
        if (!productId) {
            $('#product_cards').hide();
            return;
        }

        // Show product cards and populate data
        $('#product_cards').show();
        $('#prod_name').text(selectedOption.data('name'));
        $('#prod_price').text(parseFloat(selectedOption.data('price')).toFixed(2));
        $('#prod_stock').text(selectedOption.data('stock'));

        // Reset form values
        $('#order_qty').val(1).trigger('input');
        $('#disc_percent').val(0);
        $('#disc_value').val(0);
        $('#disc_type').val('percent').trigger('change');
        $('#qty_error').hide();
    });

    // Discount type toggle
    $('#disc_type').on('change', function() {
        if ($(this).val() === 'percent') {
            $('#disc_percent_group').show();
            $('#disc_value_group').hide();
            $('#disc_percent').trigger('input');
        } else {
            $('#disc_percent_group').hide();
            $('#disc_value_group').show();
            $('#disc_value').trigger('input');
        }
    });

    // Quantity/discount change handlers
    $('#order_qty, #disc_percent, #disc_value').on('input', recalcFinalPrice);

    // Add to order button
    $('#add_to_order').on('click', addToOrder);

    // Update order button
    $('#update_order').on('click', updateOrderItem);

    // Cancel edit button
    $('#cancel_edit').on('click', cancelEdit);

    // Remove row handler
    $('#order_table').on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    // Edit row handler
    $('#order_table').on('click', '.edit-item', function() {
        const row = $(this).closest('tr');
        const rowIndex = row.index();
        const productId = row.data('product-id');
        
        // Set edit mode
        $('#edit_index').val(rowIndex);
        $('#product_select').val(productId).trigger('change');
        
        // Populate form with existing values
        $('#order_qty').val(row.data('quantity'));
        
        const discount = parseFloat(row.data('discount'));
        const price = parseFloat(row.data('price'));
        const qty = parseFloat(row.data('quantity'));
        const subtotal = price * qty;
        
        // Determine discount type
        if (row.data('discount-type') === 'percent') {
            $('#disc_type').val('percent').trigger('change');
            $('#disc_percent').val((discount / subtotal * 100).toFixed(2));
        } else {
            $('#disc_type').val('fixed').trigger('change');
            $('#disc_value').val(discount.toFixed(2));
        }
        
        // Show update buttons
        $('#add_to_order').hide();
        $('#update_order').show();
        $('#cancel_edit').show();
    });

    // Form submission handler
    $('#orderForm').on('submit', function(e) {
        if ($('#order_table tbody tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one product to the order');
            return false;
        }
        return true;
    });
});

function recalcFinalPrice() {
    const qty = parseFloat($('#order_qty').val()) || 0;
    const price = parseFloat($('#prod_price').text()) || 0;
    const discType = $('#disc_type').val();
    const subtotal = qty * price;
    let discount = 0;

    if (discType === 'percent') {
        const discPercent = parseFloat($('#disc_percent').val()) || 0;
        discount = subtotal * discPercent / 100;
    } else {
        discount = parseFloat($('#disc_value').val()) || 0;
    }

    // Validate discount doesn't exceed price
    if (discount > subtotal) {
        discount = subtotal;
        if (discType === 'percent') {
            $('#disc_percent').val(100);
        } else {
            $('#disc_value').val(subtotal.toFixed(2));
        }
    }

    const total = subtotal - discount;
    $('#final_price').val(total.toFixed(2));
}

function addToOrder() {
    const productSelect = $('#product_select');
    const selectedOption = productSelect.find('option:selected');
    const productId = productSelect.val();
    
    if (!productId) {
        alert('Please select a product');
        return;
    }

    const qty = parseFloat($('#order_qty').val()) || 0;
    if (qty <= 0) {
        $('#qty_error').text('Quantity must be greater than 0').show();
        return;
    }

    const availableStock = parseInt(selectedOption.data('stock'));
    if (qty > availableStock) {
        $('#qty_error').text(`Only ${availableStock} available in stock`).show();
        return;
    }

    const price = parseFloat(selectedOption.data('price'));
    const subtotal = qty * price;
    const discType = $('#disc_type').val();
    let discount = 0;

    if (discType === 'percent') {
        discount = subtotal * (parseFloat($('#disc_percent').val()) || 0) / 100;
    } else {
        discount = parseFloat($('#disc_value').val()) || 0;
    }

    // Ensure discount doesn't exceed price
    discount = Math.min(discount, subtotal);
    const total = subtotal - discount;

    // Add row to the table
    const rowId = `item_${Date.now()}`;
    const row = `
    <tr id="${rowId}" data-product-id="${productId}" data-quantity="${qty}" 
        data-price="${price}" data-discount="${discount}" 
        data-discount-type="${discType}" data-total="${total}">
        <td>${selectedOption.data('name')}</td>
        <td>${qty}</td>
        <td>₹${price.toFixed(2)}</td>
        <td>${discType === 'percent' ? $('#disc_percent').val() + '%' : '₹' + discount.toFixed(2)}</td>
        <td>₹${total.toFixed(2)}</td>
        <td>
            <button type="button" class="btn btn-sm btn-primary edit-item mr-1">
                <i class="fa fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>`;

    $('#order_table tbody').append(row);
    updateTotals();
    resetProductForm();
}

function updateOrderItem() {
    const rowIndex = $('#edit_index').val();
    const row = $('#order_table tbody tr').eq(rowIndex);
    
    const productSelect = $('#product_select');
    const selectedOption = productSelect.find('option:selected');
    const productId = productSelect.val();
    
    if (!productId) {
        alert('Please select a product');
        return;
    }

    const qty = parseFloat($('#order_qty').val()) || 0;
    if (qty <= 0) {
        $('#qty_error').text('Quantity must be greater than 0').show();
        return;
    }

    const availableStock = parseInt(selectedOption.data('stock'));
    if (qty > availableStock) {
        $('#qty_error').text(`Only ${availableStock} available in stock`).show();
        return;
    }

    const price = parseFloat(selectedOption.data('price'));
    const subtotal = qty * price;
    const discType = $('#disc_type').val();
    let discount = 0;

    if (discType === 'percent') {
        discount = subtotal * (parseFloat($('#disc_percent').val()) || 0) / 100;
    } else {
        discount = parseFloat($('#disc_value').val()) || 0;
    }

    // Ensure discount doesn't exceed price
    discount = Math.min(discount, subtotal);
    const total = subtotal - discount;

    // Update the row data attributes
    row.attr('data-product-id', productId)
       .attr('data-quantity', qty)
       .attr('data-price', price)
       .attr('data-discount', discount)
       .attr('data-discount-type', discType)
       .attr('data-total', total);

    // Update the row content
    row.find('td:eq(0)').text(selectedOption.data('name'));
    row.find('td:eq(1)').text(qty);
    row.find('td:eq(2)').text('₹' + price.toFixed(2));
    row.find('td:eq(3)').text(discType === 'percent' ? $('#disc_percent').val() + '%' : '₹' + discount.toFixed(2));
    row.find('td:eq(4)').text('₹' + total.toFixed(2));

    updateTotals();
    resetProductForm();
}

function cancelEdit() {
    resetProductForm();
}

function resetProductForm() {
    $('#product_select').val('').trigger('change');
    $('#product_cards').hide();
    $('#edit_index').val('-1');
    $('#add_to_order').show();
    $('#update_order').hide();
    $('#cancel_edit').hide();
    $('#qty_error').hide();
}

function updateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let grandTotal = 0;
    const orderItems = [];

    $('#order_table tbody tr').each(function() {
        const price = parseFloat($(this).data('price'));
        const qty = parseFloat($(this).data('quantity'));
        const discount = parseFloat($(this).data('discount'));
        const total = parseFloat($(this).data('total'));
        
        subtotal += price * qty;
        totalDiscount += discount;
        grandTotal += total;

        orderItems.push({
            product_id: $(this).data('product-id'),
            quantity: qty,
            price: price,
            discount: discount,
            discount_type: $(this).data('discount-type'),
            total: total
        });
    });

    // Update display
    $('#subtotal').text('₹' + subtotal.toFixed(2));
    $('#total_discount').text('₹' + totalDiscount.toFixed(2));
    $('#grand_total').text('₹' + grandTotal.toFixed(2));
    
    // Update hidden fields
    $('#hidden_total_discount').val(totalDiscount.toFixed(2));
    $('#hidden_grand_total').val(grandTotal.toFixed(2));
    $('#order_items').val(JSON.stringify(orderItems));
}
</script>
@endsection