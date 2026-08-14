<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // Permissions
    Route::apiResource('permissions', 'PermissionsApiController');

    // Roles
    Route::apiResource('roles', 'RolesApiController');

    // Users
    Route::post('users/media', 'UsersApiController@storeMedia')->name('users.storeMedia');
    Route::apiResource('users', 'UsersApiController');

    // Company
    Route::post('companies/media', 'CompanyApiController@storeMedia')->name('companies.storeMedia');
    Route::apiResource('companies', 'CompanyApiController');

    // Branch
    Route::post('branches/media', 'BranchApiController@storeMedia')->name('branches.storeMedia');
    Route::apiResource('branches', 'BranchApiController');

    // Attendance Detail
    Route::post('attendance-details/media', 'AttendanceDetailApiController@storeMedia')->name('attendance-details.storeMedia');
    Route::apiResource('attendance-details', 'AttendanceDetailApiController');

    // Leave Request
    Route::post('leave-requests/media', 'LeaveRequestApiController@storeMedia')->name('leave-requests.storeMedia');
    Route::apiResource('leave-requests', 'LeaveRequestApiController');

    // Notification
    Route::post('notifications/media', 'NotificationApiController@storeMedia')->name('notifications.storeMedia');
    Route::apiResource('notifications', 'NotificationApiController');

    // App Updates
    Route::post('app-updates/media', 'AppUpdatesApiController@storeMedia')->name('app-updates.storeMedia');
    Route::apiResource('app-updates', 'AppUpdatesApiController');

     Route::apiResource('expense-categories', 'ExpenseCategoryApiController');

    // Income Category
    Route::apiResource('income-categories', 'IncomeCategoryApiController');

    // Expense
    Route::post('expenses/media', 'ExpenseApiController@storeMedia')->name('expenses.storeMedia');
    Route::apiResource('expenses', 'ExpenseApiController');

    // Income
    Route::apiResource('incomes', 'IncomeApiController');

    // Add Request Amount
    Route::apiResource('add-request-amounts', 'AddRequestAmountApiController');

    // Visit
    Route::post('visits/media', 'VisitApiController@storeMedia')->name('visits.storeMedia');
    Route::apiResource('visits', 'VisitApiController');
});


Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {

    // 🔑 Login API
    Route::post('login', 'UsersApiController@login')->name('login');
     
    // 👤 Get user details by ID
    Route::get('user-details/{id}', 'UsersApiController@getUserById')->name('user.details');
     
    // 🔔 Get all notifications
    Route::get('all-notifications', 'NotificationApiController@getAllNotifications')->name('notifications.all');
    
    // 📌 LeaveType API
    Route::get('leave-types', 'LeaveTypeApiController@index')->name('leave-types.index');
    
    // 📝 Submit Leave Request
    Route::post('submit-leave-request', 'LeaveRequestApiController@submitLeaveRequest')->name('leave-request.customSubmit');
    
    // 📄 Get Leave Requests by User
    Route::get('leave-requests-by-user/{userId}', 'LeaveRequestApiController@getLeaveRequestsByUser')->name('leave-request.by-user');
    
    // ⏰ Attendance Punch In/Out
    Route::post('attendance/punch', 'AttendanceDetailApiController@punchAttendance')->name('attendance.punch');
    
    // 📅 Today Attendance by User
    Route::get('attendance/today/{userId}', 'AttendanceDetailApiController@todayAttendance')->name('attendance.today');
    
    // 🖼 Update User Profile Image
    Route::post('user-update-image/{id}', 'UsersApiController@updateUserImage')->name('user.update.image');
    
    // 📊 Attendance Report by User
    Route::get('attendance/report/{userId}', 'AttendanceDetailApiController@attendanceReport')->name('attendance.report');
    
    // 📆 Attendance Calendar Report (Month View)
    Route::get('attendance/calendar/{userId}', 'AttendanceCalendarApiController@getCalendarReport')->name('attendance.calendar');
    
    // 🚶 Submit Visit
    Route::post('visit/submit', 'VisitApiController@submitVisit')->name('visit.submit');
    
    // 📋 Get Visits by User
    Route::get('visits/by-user/{userId}', 'VisitApiController@getVisitsByUser')->name('visits.by-user');
    
    // 🆔 ID Card Preview
    Route::get('id-card/{id}/preview', 'IdCardApiController@preview')->name('idcard.preview');
    
    // 🖨 ID Card Download
    Route::get('id-card/{id}/download', 'IdCardApiController@download')->name('idcard.download');
    
    // 🛠 Manual Attendance (Date-wise add/update)
    Route::post('attendance/manual', 'AttendanceDetailApiController@manualAttendance')->name('attendance.manual');
    
    // 💰 Submit Amount Request
    Route::post('request-amount/submit', 'AddRequestAmountApiController@submitRequestAmount')->name('request-amount.submit');

    // 🕒 Amount Request History by User
    Route::get('amount-request/history/{userId}', 'AddRequestAmountApiController@requestHistory')->name('amount-request.history');
    
    // 💸 Submit Expense
    Route::post('expense/submit', 'ExpenseApiController@submitExpense')->name('expense.submit');
    
    // 📖 Expense History by User
    Route::get('expense/history/{userId}', 'ExpenseApiController@expenseHistory')->name('expense.history');
    
    // 🗂 Expense Categories List
    Route::get('expense-categories', 'ExpenseCategoryApiController@index')->name('expense-categories.index');
        
    // 💳 Get Balance by User
    Route::get('balance/{userId}', 'ExpenseApiController@getBalance')->name('balance.show');
    
    // 🏪 Create Customer and Fetch customers by creator
    Route::post('make-customer/store', 'MakeCustomerApiController@store')->name('make-customer.store');
    Route::get('customers/by-user/{userId}', 'MakeCustomerApiController@getCustomersByUser');
    
    // 🛒 Product APIs
    Route::get('products', 'ProductApiController@index')->name('products.index'); // Get All Products
    Route::get('products/{id}', 'ProductApiController@show')->name('products.show'); // Get Single Product
    
    // ✅ Order API
    Route::post('order/store', 'OrderApiController@store')->name('order.store'); // Create Order + Save Products
    Route::get('orders/by-user/{userId}', 'OrderApiController@getOrdersByUser'); // Get Orders by User
    
    // 🧾 Get order counts for a user
    Route::get('orders/counts/{userId}', 'OrderApiController@getOrderCounts')->name('orders.counts');
    
    // 🧾 Fix Employee Ids
    Route::post('attendance/fix-employee-ids', 'AttendanceDetailApiController@fixEmployeeIds')->name('attendance.fix-employee-ids');
    
    // 🧾 Send Daily Attendance Report
    Route::post('attendance/send-daily-report','AttendanceDetailApiController@sendDailyAttendanceReport')->name('attendance.send-daily-report');

    // 🧾 Send Daily Attendance Report Pdf
    Route::post('attendance/daily-report-pdf','AttendanceDetailApiController@downloadDailyAttendancePdf');

    // 🧾 Send User Montly Attendance Report
    Route::post('attendance/send-monthly-report','AttendanceDetailApiController@sendMonthlyAttendanceReport')->name('attendance.send-monthly-report');
    
    Route::get('attendance/images/{userId}', 'AttendanceDetailApiController@attendanceImages')->name('attendance.images');

    // 💰 Salary Details by User + Month + Year
    Route::get('salary/{userId}/{month}/{year}','PayrollApiController@getSalaryDetails')->name('salary.details');

    Route::get('salary-slip/{userId}/{month}/{year}','PayrollApiController@downloadSalarySlip')->name('salary-slip.download');

    Route::get('policy/download', 'DocumentApiController@downloadPolicy')->name('policy.download');


    // 📅 Date-wise All Employees Attendance
    Route::get('attendance/date/{date}','AttendanceDetailApiController@attendanceByDate')->name('attendance.date');

    
   
    
    
    
});




