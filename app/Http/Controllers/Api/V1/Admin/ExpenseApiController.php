<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpenseApiController extends Controller
{
    use MediaUploadingTrait;

    // ✅ Submit Expense API (Multipart-ready)
    public function submitExpense(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'entry_date'          => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'nullable|string',
            'status'              => 'in:pending,accept,reject',
            'upload_image'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // max 10MB
        ]);

        // Auto fetch employee_id from user
        $user = User::find($data['user_id']);
        $data['employee_id'] = $user && $user->employee ? $user->employee->id : null;

        // Default status
        $data['status'] = $data['status'] ?? 'pending';

        // Create Expense
        $expense = Expense::create($data);

        // Handle file upload
        if ($request->hasFile('upload_image')) {
            try {
                $expense->addMedia($request->file('upload_image'))
                        ->toMediaCollection('upload_image');
            } catch (\Exception $e) {
                \Log::error('Media upload failed: '.$e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Expense submitted successfully',
            'data' => [
                'id'          => $expense->id,
                'amount'      => $expense->amount,
                'description' => $expense->description,
                'status'      => $expense->status,
                'created_at'  => $expense->created_at->toDateTimeString(),
            ],
        ], Response::HTTP_CREATED);
    }

    // Expense History by User
    public function expenseHistory($userId)
    {
        $expenses = Expense::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    
        $result = $expenses->map(function($expense) {
            return [
                'amount'      => $expense->amount,
                'description' => $expense->description,
                'status'      => $expense->status,
                'created_at'  => $expense->created_at->toDateTimeString(),
                'upload_image'=> $expense->upload_image ? $expense->upload_image->url : null,
            ];
        });
    
        return response()->json([
            'data' => $result
        ], Response::HTTP_OK);
    }
    
    
    // ✅ Balance API
    public function getBalance($userId)
    {
        // Approved credits
        $totalCredit = \App\Models\AddRequestAmount::where('user_id', $userId)
                        ->where('status', 'accept')
                        ->sum('amount');
    
        // Approved expenses
        $totalExpense = \App\Models\Expense::where('user_id', $userId)
                        ->where('status', 'accept')
                        ->sum('amount');
    
        // ✅ Logic
        if ($totalExpense > $totalCredit) {
            $availableBalance = 0;  // jitna mila tha wo pura khatam
            $unbilledBalance  = $totalExpense - $totalCredit; // pocket se lag gya
        } else {
            $availableBalance = $totalCredit - $totalExpense; // abhi bacha hua
            $unbilledBalance  = 0; // credit ke andar hi kharch hua
        }
    
        return response()->json([
            'data' => [
                'available_balance' => $availableBalance,
                'unbilled_balance'  => $unbilledBalance,
            ]
        ], Response::HTTP_OK);
    }




}
