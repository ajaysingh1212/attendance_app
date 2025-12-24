<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddRequestAmount;
use App\Models\Expense;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;

class ExpenseReportController extends Controller
{
 public function index(Request $request)
{
    // ✅ Only approved incomes & expenses
    $query = AddRequestAmount::where('status', 'accept');
    $expenseQuery = Expense::with('expense_category')->where('status', 'accept');

    // 🔹 Filter by employee_id only
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
        $expenseQuery->where('employee_id', $request->employee_id);
    }

    // 🔹 Date filter based on filter_type
    if ($request->filled('filter_type')) {
        $now = now();
        switch ($request->filter_type) {
            case 'day':
                $query->whereDate('created_at', $now);
                $expenseQuery->whereDate('created_at', $now);
                break;
            case 'week':
                $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                $expenseQuery->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                $expenseQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                break;
            case 'half_year':
                $query->where('created_at', '>=', $now->subMonths(6));
                $expenseQuery->where('created_at', '>=', $now->subMonths(6));
                break;
            case 'year':
                $query->whereYear('created_at', $now->year);
                $expenseQuery->whereYear('created_at', $now->year);
                break;
            case 'custom':
                if ($request->filled('from_date') && $request->filled('to_date')) {
                    $from = Carbon::parse($request->from_date)->startOfDay();
                    $to = Carbon::parse($request->to_date)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                    $expenseQuery->whereBetween('created_at', [$from, $to]);
                }
                break;
        }
    }

    $employees = Employee::all();

    // 🔹 Income Grouped by Description
    $incomeData = $query->get()->groupBy('description');
    $groupedIncome = [];
    foreach ($incomeData as $desc => $items) {
        $groupedIncome[$desc ?? 'Uncategorized'] = $items->sum('amount');
    }

    // 🔹 Expenses Grouped by category and description
    $expenseData = $expenseQuery->get()->groupBy(function ($item) {
        $category = $item->expense_category->name ?? 'Uncategorized';
        $desc = $item->description ?? 'No description';
        return "{$category} - {$desc}";
    });

    $groupedExpenses = [];
    foreach ($expenseData as $label => $items) {
        $groupedExpenses[$label] = $items->sum('amount');
    }

    $totalIncome = array_sum($groupedIncome);
    $totalExpense = array_sum($groupedExpenses);
    $profit = $totalIncome - $totalExpense;

    return view('admin.expenseReports.index', compact(
        'employees', 'groupedIncome', 'groupedExpenses', 'totalIncome', 'totalExpense', 'profit'
    ));
}


}

