<?php

use App\Http\Controllers\Admin\AttendanceDetailController;
use App\Http\Controllers\Admin\EmployeeMonthlyAttendanceController;
use App\Http\Controllers\Admin\HolidayController as AdminHolidayController;
use App\Http\Controllers\Admin\PayrollAdjustmentController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\OfficeBranchController;
use App\Http\Controllers\Admin\SalaryStructureController;
use App\Http\Controllers\Admin\TrackMemberController;
use App\Http\Controllers\Admin\SalaryIncrementController;
use App\Http\Controllers\Admin\UsersController;

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes();

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::post('permissions/parse-csv-import', 'PermissionsController@parseCsvImport')->name('permissions.parseCsvImport');
    Route::post('permissions/process-csv-import', 'PermissionsController@processCsvImport')->name('permissions.processCsvImport');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::post('roles/parse-csv-import', 'RolesController@parseCsvImport')->name('roles.parseCsvImport');
    Route::post('roles/process-csv-import', 'RolesController@processCsvImport')->name('roles.processCsvImport');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::post('users/media', 'UsersController@storeMedia')->name('users.storeMedia');
    Route::post('users/ckmedia', 'UsersController@storeCKEditorImages')->name('users.storeCKEditorImages');
    Route::post('users/parse-csv-import', 'UsersController@parseCsvImport')->name('users.parseCsvImport');
    Route::post('users/process-csv-import', 'UsersController@processCsvImport')->name('users.processCsvImport');
    Route::resource('users', 'UsersController');

    // Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

    // Company
    Route::delete('companies/destroy', 'CompanyController@massDestroy')->name('companies.massDestroy');
    Route::post('companies/media', 'CompanyController@storeMedia')->name('companies.storeMedia');
    Route::post('companies/ckmedia', 'CompanyController@storeCKEditorImages')->name('companies.storeCKEditorImages');
    Route::post('companies/parse-csv-import', 'CompanyController@parseCsvImport')->name('companies.parseCsvImport');
    Route::post('companies/process-csv-import', 'CompanyController@processCsvImport')->name('companies.processCsvImport');
    Route::resource('companies', 'CompanyController');

    // Branch
    Route::delete('branches/destroy', 'BranchController@massDestroy')->name('branches.massDestroy');
    Route::post('branches/media', 'BranchController@storeMedia')->name('branches.storeMedia');
    Route::post('branches/ckmedia', 'BranchController@storeCKEditorImages')->name('branches.storeCKEditorImages');
    Route::post('branches/parse-csv-import', 'BranchController@parseCsvImport')->name('branches.parseCsvImport');
    Route::post('branches/process-csv-import', 'BranchController@processCsvImport')->name('branches.processCsvImport');
    Route::resource('branches', 'BranchController');

    // Attendance Detail
    Route::post('attendance-details/update-status', [App\Http\Controllers\Admin\AttendanceDetailController::class, 'updateStatus'])->name('attendance-details.updateStatus');


    // ⛳️ CSV, Media, and Custom Routes
    Route::delete('attendance-details/destroy', [AttendanceDetailController::class, 'massDestroy'])->name('attendance-details.massDestroy');
    Route::post('attendance-details/media', [AttendanceDetailController::class, 'storeMedia'])->name('attendance-details.storeMedia');
    Route::post('attendance-details/ckmedia', [AttendanceDetailController::class, 'storeCKEditorImages'])->name('attendance-details.storeCKEditorImages');
    Route::post('attendance-details/parse-csv-import', [AttendanceDetailController::class, 'parseCsvImport'])->name('attendance-details.parseCsvImport');
    Route::post('attendance-details/process-csv-import', [AttendanceDetailController::class, 'processCsvImport'])->name('attendance-details.processCsvImport');

    // 🟢 Place BEFORE resource to avoid conflict
    Route::get('attendance-details/user/{user}', [AttendanceDetailController::class, 'calendarData'])->name('attendance-details.calendarData');
    Route::get('admin/attendance-details/fetch-detail', [AttendanceDetailController::class, 'fetchDetail'])->name('admin.attendance-details.fetchDetail');
    Route::get('attendance-details/summary', [AttendanceDetailController::class, 'summary'])
     ->name('summary');
    
    // 🟩 Resource Route
    Route::resource('attendance-details', AttendanceDetailController::class);

    //leave tyepe
    Route::resource('leave-types', \App\Http\Controllers\Admin\LeaveTypeController::class);
    
    //holiday

    Route::resource('holidays', AdminHolidayController::class);

    //salary structure
    Route::resource('salary-structures', SalaryStructureController::class);

    Route::delete('leave-requests/destroy', 'LeaveRequestController@massDestroy')->name('leave-requests.massDestroy');
    Route::post('leave-requests/media', 'LeaveRequestController@storeMedia')->name('leave-requests.storeMedia');
    Route::post('leave-requests/ckmedia', 'LeaveRequestController@storeCKEditorImages')->name('leave-requests.storeCKEditorImages');
    Route::post('leave-requests/parse-csv-import', 'LeaveRequestController@parseCsvImport')->name('leave-requests.parseCsvImport');
    Route::post('leave-requests/process-csv-import', 'LeaveRequestController@processCsvImport')->name('leave-requests.processCsvImport');
    Route::resource('leave-requests', 'LeaveRequestController');

    // Notification
    Route::delete('notifications/destroy', 'NotificationController@massDestroy')->name('notifications.massDestroy');
    Route::post('notifications/media', 'NotificationController@storeMedia')->name('notifications.storeMedia');
    Route::post('notifications/ckmedia', 'NotificationController@storeCKEditorImages')->name('notifications.storeCKEditorImages');
    Route::post('notifications/parse-csv-import', 'NotificationController@parseCsvImport')->name('notifications.parseCsvImport');
    Route::post('notifications/process-csv-import', 'NotificationController@processCsvImport')->name('notifications.processCsvImport');
    Route::resource('notifications', 'NotificationController');

    // App Updates
    Route::delete('app-updates/destroy', 'AppUpdatesController@massDestroy')->name('app-updates.massDestroy');
    Route::post('app-updates/media', 'AppUpdatesController@storeMedia')->name('app-updates.storeMedia');
    Route::post('app-updates/ckmedia', 'AppUpdatesController@storeCKEditorImages')->name('app-updates.storeCKEditorImages');
    Route::post('app-updates/parse-csv-import', 'AppUpdatesController@parseCsvImport')->name('app-updates.parseCsvImport');
    Route::post('app-updates/process-csv-import', 'AppUpdatesController@processCsvImport')->name('app-updates.processCsvImport');
    Route::resource('app-updates', 'AppUpdatesController');

     // Expense Category
    Route::delete('expense-categories/destroy', 'ExpenseCategoryController@massDestroy')->name('expense-categories.massDestroy');
    Route::post('expense-categories/parse-csv-import', 'ExpenseCategoryController@parseCsvImport')->name('expense-categories.parseCsvImport');
    Route::post('expense-categories/process-csv-import', 'ExpenseCategoryController@processCsvImport')->name('expense-categories.processCsvImport');
    Route::resource('expense-categories', 'ExpenseCategoryController');

    // Income Category
    Route::delete('income-categories/destroy', 'IncomeCategoryController@massDestroy')->name('income-categories.massDestroy');
    Route::post('income-categories/parse-csv-import', 'IncomeCategoryController@parseCsvImport')->name('income-categories.parseCsvImport');
    Route::post('income-categories/process-csv-import', 'IncomeCategoryController@processCsvImport')->name('income-categories.processCsvImport');
    Route::resource('income-categories', 'IncomeCategoryController');

    // Expense
    Route::delete('expenses/destroy', 'ExpenseController@massDestroy')->name('expenses.massDestroy');
    Route::post('expenses/media', 'ExpenseController@storeMedia')->name('expenses.storeMedia');
    Route::post('expenses/ckmedia', 'ExpenseController@storeCKEditorImages')->name('expenses.storeCKEditorImages');
    Route::post('expenses/parse-csv-import', 'ExpenseController@parseCsvImport')->name('expenses.parseCsvImport');
    Route::post('expenses/process-csv-import', 'ExpenseController@processCsvImport')->name('expenses.processCsvImport');
    Route::resource('expenses', 'ExpenseController');

    // Income
    Route::delete('incomes/destroy', 'IncomeController@massDestroy')->name('incomes.massDestroy');
    Route::post('incomes/parse-csv-import', 'IncomeController@parseCsvImport')->name('incomes.parseCsvImport');
    Route::post('incomes/process-csv-import', 'IncomeController@processCsvImport')->name('incomes.processCsvImport');
    Route::resource('incomes', 'IncomeController');

    // Expense Report
    Route::delete('expense-reports/destroy', 'ExpenseReportController@massDestroy')->name('expense-reports.massDestroy');
    Route::resource('expense-reports', 'ExpenseReportController');


    // Add Request Amount
    Route::delete('add-request-amounts/destroy', 'AddRequestAmountController@massDestroy')->name('add-request-amounts.massDestroy');
    Route::post('add-request-amounts/parse-csv-import', 'AddRequestAmountController@parseCsvImport')->name('add-request-amounts.parseCsvImport');
    Route::post('add-request-amounts/process-csv-import', 'AddRequestAmountController@processCsvImport')->name('add-request-amounts.processCsvImport');
    Route::resource('add-request-amounts', 'AddRequestAmountController');

    //payroll
    
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/id', [EmployeeController::class, 'id'])->name('employees.id');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('payroll.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::match(['get', 'post'], '/employees/{employee}/download-pdf', [EmployeeController::class, 'downloadPdf'])
    ->name('employees.downloadPdf');
    // Route::get('employees/offer-letter', [App\Http\Controllers\Admin\EmployeeController::class, 'offerLetter'])
    // ->name('employees.offer-letter');
    Route::get('employees/offer-letter/{employee}', [EmployeeController::class, 'offerLetterView'])
        ->name('employees.offerLetter');

        Route::get('employees/{employee}/terms-status', [UsersController::class, 'termsStatus'])
        ->name('employees.termsStatus');

    Route::post('employees/save-photo', [UsersController::class, 'savePhoto'])
        ->name('employees.savePhoto');

    Route::post('employees/save-signature', [UsersController::class, 'saveSignature'])
        ->name('employees.saveSignature');


    //payroll adjustment
    Route::resource('payroll-adjustments', 'PayrollAdjustmentController');
    Route::get('payroll/{id}/salary-details', [PayrollController::class, 'getSalaryDetails']);
    Route::get('salary-structures/history', [SalaryStructureController::class, 'show'])->name('salary-structures.history');
    Route::get('payrolls/manual-adjust/{payrollId}', [PayrollController::class, 'manualAdjustmentForm'])->name('payrolls.manualAdjustForm');
    Route::put('payrolls/manual-adjust/{payrollId}', [PayrollController::class, 'manualAdjustmentUpdate'])->name('payrolls.manualAdjust');

    Route::get('payrolls/download/{format}', [PayrollController::class, 'downloadPayrollPdf'])->name('payrolls.download');
    

    Route::get('payroll', [PayrollController::class, 'index'])
        ->name('payroll.index');

    Route::post('payroll/generate', [PayrollController::class, 'generate'])
        ->name('payroll.generate');

    Route::post('payroll/verify-master', [PayrollController::class, 'verifyMasterPassword'])
        ->name('payroll.verifyMaster');

    Route::get('payroll/list', [PayrollController::class, 'list'])
        ->name('payroll.list');

    Route::get('employees/{id}/salary-details', [PayrollController::class, 'getSalaryDetails']);

    Route::get('payrolls/download', [PayrollController::class, 'download'])
        ->name('payrolls.download');

    Route::get('payrolls/details', [PayrollController::class, 'details'])
        ->name('payrolls.details');

 


    
    // Leave Request

    // monthly attendence report 
        Route::get('employee-monthly-attendance', [EmployeeMonthlyAttendanceController::class, 'index'])
        ->name('employee_monthly_attendance.index');

    // Product Category
    Route::delete('product-categories/destroy', 'ProductCategoryController@massDestroy')->name('product-categories.massDestroy');
    Route::post('product-categories/media', 'ProductCategoryController@storeMedia')->name('product-categories.storeMedia');
    Route::post('product-categories/ckmedia', 'ProductCategoryController@storeCKEditorImages')->name('product-categories.storeCKEditorImages');
    Route::resource('product-categories', 'ProductCategoryController');

    // Product Tag
    Route::delete('product-tags/destroy', 'ProductTagController@massDestroy')->name('product-tags.massDestroy');
    Route::resource('product-tags', 'ProductTagController');

    // Product
    Route::delete('products/destroy', 'ProductController@massDestroy')->name('products.massDestroy');
    Route::post('products/media', 'ProductController@storeMedia')->name('products.storeMedia');
    Route::post('products/ckmedia', 'ProductController@storeCKEditorImages')->name('products.storeCKEditorImages');
    Route::resource('products', 'ProductController');

         // Track Member
    Route::delete('track-members/destroy', 'TrackMemberController@massDestroy')->name('track-members.massDestroy');
    Route::post('track-members/parse-csv-import', 'TrackMemberController@parseCsvImport')->name('track-members.parseCsvImport');
    Route::post('track-members/process-csv-import', 'TrackMemberController@processCsvImport')->name('track-members.processCsvImport');
    Route::resource('track-members', 'TrackMemberController');
    Route::post('/track-location', [TrackMemberController::class, 'trackLocation'])->name('track.location');
    Route::post('track-members/live-location', [TrackMemberController::class, 'getLatestLocation'])
    ->name('track-members.liveLocation');
    Route::post('track-members/fetch-user-data', [TrackMemberController::class, 'getUserTrackData'])
    ->name('track-members.fetchUserData');


        // Report
    Route::delete('reports/destroy', 'ReportController@massDestroy')->name('reports.massDestroy');
    Route::post('reports/parse-csv-import', 'ReportController@parseCsvImport')->name('reports.parseCsvImport');
    Route::post('reports/process-csv-import', 'ReportController@processCsvImport')->name('reports.processCsvImport');
    Route::resource('reports', 'ReportController');
    Route::post('reports/fetch-track-history', [App\Http\Controllers\Admin\ReportController::class, 'fetchTrackHistory'])->name('reports.fetchHistory');


        // Order
    Route::delete('orders/destroy', 'OrderController@massDestroy')->name('orders.massDestroy');
    Route::resource('orders', 'OrderController');

    // Make Customer
    Route::delete('make-customers/destroy', 'MakeCustomerController@massDestroy')->name('make-customers.massDestroy');
    Route::post('make-customers/media', 'MakeCustomerController@storeMedia')->name('make-customers.storeMedia');
    Route::post('make-customers/ckmedia', 'MakeCustomerController@storeCKEditorImages')->name('make-customers.storeCKEditorImages');
    Route::resource('make-customers', 'MakeCustomerController');

        // Visit
    Route::delete('visits/destroy', 'VisitController@massDestroy')->name('visits.massDestroy');
    Route::post('visits/media', 'VisitController@storeMedia')->name('visits.storeMedia');
    Route::post('visits/ckmedia', 'VisitController@storeCKEditorImages')->name('visits.storeCKEditorImages');
    Route::post('visits/parse-csv-import', 'VisitController@parseCsvImport')->name('visits.parseCsvImport');
    Route::post('visits/process-csv-import', 'VisitController@processCsvImport')->name('visits.processCsvImport');
    Route::resource('visits', 'VisitController');

     Route::post('visits/start', [App\Http\Controllers\Admin\VisitController::class, 'start'])->name('visits.start');

    // Check-out (Out) — same visit ko close karega
    Route::post('visits/{visit}/out', [App\Http\Controllers\Admin\VisitController::class, 'out'])->name('visits.out');
        // Report
    Route::delete('performance-reports/destroy', 'PerformanceReportController@massDestroy')->name('reports.massDestroy');
    Route::resource('performance-reports', 'PerformanceReportController');

    // Show Report
    Route::delete('show-reports/destroy', 'ShowReportController@massDestroy')->name('show-reports.massDestroy');
    Route::resource('show-reports', 'ShowReportController');
    // part payment
    Route::get('/admin/payrolls/{id}/manual-adjust', [PayrollController::class, 'manualAdjustPage'])->name('payrolls.manualAdjustPage');
    Route::get('/admin/payrolls/{id}/part-payment', [PayrollController::class, 'partPaymentPage'])->name('payrolls.partPaymentPage');
    Route::post('/admin/payrolls/{id}/part-payment', [PayrollController::class, 'savePartPayment'])->name('payrolls.partPayment');
    Route::get('/payrolls/part-payments', [PayrollController::class, 'partPaymentsList'])
    ->name('payrolls.partPaymentsList');

    Route::get('salary-increments', [SalaryIncrementController::class,'index'])->name('salary-increments.index');
    Route::get('salary-increments/create', [SalaryIncrementController::class,'create'])->name('salary-increments.create');
    Route::post('salary-increments', [SalaryIncrementController::class,'store'])->name('salary-increments.store');

    Route::get('salary-increments/{id}/edit', [SalaryIncrementController::class,'edit'])->name('salary-increments.edit');
    Route::put('salary-increments/{id}', [SalaryIncrementController::class,'update'])->name('salary-increments.update');

    Route::post('salary-increments/{id}/approve', [SalaryIncrementController::class,'approve'])->name('salary-increments.approve');
    Route::post('salary-increments/{id}/reject', [SalaryIncrementController::class,'reject'])->name('salary-increments.reject');

    Route::post('get-employee-salary', [SalaryIncrementController::class,'getEmployeeSalary'])->name('get.employee.salary');

    Route::get('salary-increments/{id}/letter', [SalaryIncrementController::class,'downloadLetter'])->name('salary-increments.letter');


});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});

Route::get('admin/attendance/pdf', [App\Http\Controllers\Admin\HomeController::class, 'downloadPdf'])->name('admin.attendance.downloadPdf');

    Route::post('/attendance/save', [App\Http\Controllers\Admin\HomeController::class, 'saveAttendance'])->name('admin.attendance.save');
Route::get('orders/{order}/invoice', [App\Http\Controllers\Admin\OrderController::class, 'downloadInvoice'])->name('admin.orders.invoice');

