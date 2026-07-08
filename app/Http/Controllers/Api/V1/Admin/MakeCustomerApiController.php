<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\MakeCustomer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MakeCustomerApiController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Store new customer via API
     */
    public function store(Request $request)
    {
        // ✅ Validation
        $validated = $request->validate([
            'shop_name'                => 'required|string|max:255',
            'owner_name'               => 'required|string|max:255',
            'phone_number'             => 'required|string|max:20',
            'email'                    => 'nullable|email',
            'shop_category_id'         => 'nullable|integer|exists:product_categories,id',
            'pincode'                  => 'nullable|string|max:10',
            'address_line_1'           => 'nullable|string',
            'address_line_2'           => 'nullable|string',
            'area'                     => 'nullable|string|max:255',
            'city'                     => 'nullable|string|max:255',
            'state'                    => 'nullable|string|max:255',
            'country'                  => 'nullable|string|max:255',
            'latitude'                 => 'nullable|string|max:50',
            'longitude'                => 'nullable|string|max:50',
            'business_type'            => 'nullable|string',
            'gst_number'               => 'nullable|string|max:50',
            'license_no'               => 'nullable|string|max:50',
            'payment_terms'            => 'nullable|string|max:255',
            'preferred_payment_method' => 'nullable|string',
            'bank_name'                => 'nullable|string|max:255',
            'ifsc_code'                => 'nullable|string|max:50',
            'account_no'               => 'nullable|string|max:50',
            'notes'                    => 'nullable|string',
            'status'                   => 'nullable|string|in:Enable,Disable',

            // ✅ created_by_id input se lena hai
            'created_by_id'            => 'required|integer|exists:users,id',
        ]);

        // ✅ Auto-generate customer code (CUST0001, CUST0002, ...)
        $lastCustomer = MakeCustomer::orderBy('id', 'desc')->first();
        $nextId = $lastCustomer ? $lastCustomer->id + 1 : 1;
        $customerCode = 'CUST' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // ✅ Create Customer
        $makeCustomer = MakeCustomer::create(
            $validated + [
                'customer_code' => $customerCode
            ]
        );

        // ✅ Media Uploads
        if ($request->hasFile('shop_image')) {
            $makeCustomer->addMedia($request->file('shop_image'))->toMediaCollection('shop_image');
        }

        if ($request->hasFile('id_proof')) {
            $makeCustomer->addMedia($request->file('id_proof'))->toMediaCollection('id_proof');
        }

        if ($request->hasFile('gst_certificate')) {
            $makeCustomer->addMedia($request->file('gst_certificate'))->toMediaCollection('gst_certificate');
        }

        // ✅ Response
        return response()->json([
            'success'  => true,
            'message'  => 'Customer created successfully',
            'customer' => $makeCustomer->load(['shop_category', 'created_by']),
        ], Response::HTTP_CREATED);
    }
    
    
    /**
     * Fetch all customers created by a specific user
     */
    public function getCustomersByUser($userId)
    {
        $customers = MakeCustomer::with(['shop_category', 'created_by'])
            ->where('created_by_id', $userId)
            ->get()
            ->map(function ($customer) {
                return [
                    'id'            => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'shop_name'     => $customer->shop_name,
                    'owner_name'    => $customer->owner_name,
                    'phone_number'  => $customer->phone_number,
                    'email'         => $customer->email,
                    'address'       => [
                        'line_1' => $customer->address_line_1,
                        'line_2' => $customer->address_line_2,
                        'area'   => $customer->area,
                        'city'   => $customer->city,
                        'state'  => $customer->state,
                        'country'=> $customer->country,
                        'pincode'=> $customer->pincode,
                    ],
                    'business_type' => $customer->business_type,
                    'gst_number'    => $customer->gst_number,
                    'license_no'    => $customer->license_no,
                    'status'        => $customer->status,
                    'created_at'    => $customer->created_at,
                    'updated_at'    => $customer->updated_at,
                    'media'         => [
                        'shop_image'      => $customer->getFirstMediaUrl('shop_image'),
                        'id_proof'        => $customer->getFirstMediaUrl('id_proof'),
                        'gst_certificate' => $customer->getFirstMediaUrl('gst_certificate'),
                    ],
                    'created_by'    => [
                        'id'   => $customer->created_by->id ?? null,
                        'name' => $customer->created_by->name ?? null,
                    ],
                    'shop_category' => $customer->shop_category ? [
                        'id'   => $customer->shop_category->id,
                        'name' => $customer->shop_category->name,
                    ] : null,
                ];
            });

        return response()->json([
            'success'   => true,
            'message'   => 'Customers fetched successfully',
            'customers' => $customers,
        ], Response::HTTP_OK);
    }
    
    
    
}
