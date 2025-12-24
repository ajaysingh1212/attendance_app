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
    
    
    Route::post('login', 'UsersApiController@login')->name('login');
     
    Route::get('user-details/{id}', 'UsersApiController@getUserById')->name('user.details');
     
    Route::get('all-notifications', 'NotificationApiController@getAllNotifications')->name('notifications.all');
    
    Route::post('submit-leave-request', 'LeaveRequestApiController@submitLeaveRequest')->name('leave-request.customSubmit');
    
    Route::get('leave-requests-by-user/{userId}', 'LeaveRequestApiController@getLeaveRequestsByUser')->name('leave-request.by-user');
    
    // ✅ Attendance Punch In/Out
    Route::post('attendance/punch', 'AttendanceDetailApiController@punchAttendance')->name('attendance.punch');
    
    // ✅ Today Attendance by user
    Route::get('attendance/today/{userId}', 'AttendanceDetailApiController@todayAttendance')->name('attendance.today');
    
    Route::post('user-update-image/{id}', 'UsersApiController@updateUserImage')->name('user.update.image');
    
    Route::get('attendance/report/{userId}', 'AttendanceDetailApiController@attendanceReport')->name('attendance.report');
    
    // ✅ Attendance Calendar Report (month view)
    Route::get('attendance/calendar/{userId}', 'AttendanceCalendarApiController@getCalendarReport')
    ->name('attendance.calendar');
    
    
    Route::post('visit/submit', 'VisitApiController@submitVisit')->name('visit.submit');
    
    Route::get('visits/by-user/{userId}', 'VisitApiController@getVisitsByUser')->name('visits.by-user');
 







    
    



});



