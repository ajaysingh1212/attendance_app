<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand d-md-down-none">
        <a class="c-sidebar-brand-full h4" href="#">
            {{ trans('panel.site_title') }}
        </a>
    </div>

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.home") }}" class="c-sidebar-nav-link">
                <i class="c-sidebar-nav-icon fas fa-fw fa-tachometer-alt">

                </i>
                {{ trans('global.dashboard') }}
            </a>
        </li>
        @can('user_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/permissions*") ? "c-show" : "" }} {{ request()->is("admin/roles*") ? "c-show" : "" }} {{ request()->is("admin/users*") ? "c-show" : "" }} {{ request()->is("admin/audit-logs*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-users c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.userManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('permission_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.permissions.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/permissions") || request()->is("admin/permissions/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-unlock-alt c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.permission.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('role_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.roles.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/roles") || request()->is("admin/roles/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.role.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('user_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.users.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/users") || request()->is("admin/users/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-user c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.user.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('audit_log_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.audit-logs.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/audit-logs") || request()->is("admin/audit-logs/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.auditLog.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('master_data_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/companies*") ? "c-show" : "" }} {{ request()->is("admin/branches*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-database c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.masterData.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">

                    @can('branch_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.branches.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/branches") || request()->is("admin/branches/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.branch.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
       
      @can('payroll_access')
    <li class="c-sidebar-nav-dropdown {{ request()->is('admin/payroll-details*') ? 'c-show' : '' }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-money-check-alt c-sidebar-nav-icon"></i>
            {{ trans('cruds.payroll.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            
            @can('payroll_detail_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.employees.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/attendance-details') || request()->is('admin/attendance-details/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-list-alt c-sidebar-nav-icon"></i>
                        {{ trans('cruds.payrollDetail.title') }}
                    </a>
                </li>
            @endcan

            @can('payroll_structure_create')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.payroll-adjustments.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/attendance-details') || request()->is('admin/attendance-details/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-sliders-h c-sidebar-nav-icon"></i>
                        Payroll Adjustments
                    </a>
                </li>
            @endcan

            {{-- @can('salary_structure_create')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.salary-structures.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/attendance-details') || request()->is('admin/attendance-details/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-sitemap c-sidebar-nav-icon"></i>
                        Salary Structure
                    </a>
                </li>
            @endcan --}}
            @can('salary_increment_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.salary-increments.index') }}"
                    class="c-sidebar-nav-link {{ request()->is('admin/salary-increments') || request()->is('admin/salary-increments/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-level-up-alt c-sidebar-nav-icon"></i>
                        Salary Increment
                    </a>
                </li>
            @endcan

            

            @can('salary_payroll_create')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.payroll.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/attendance-details') || request()->is('admin/attendance-details/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-coins c-sidebar-nav-icon"></i>
                        Salary Payroll
                    </a>
                </li>
            @endcan

            @can('payroll_idcard')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.employees.id') }}" class="c-sidebar-nav-link {{ request()->is('admin/employees/id') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-id-badge c-sidebar-nav-icon"></i>
                        Employee ID
                    </a>
                </li>
            @endcan
            @can('payroll_offer_letter')
    <li class="c-sidebar-nav-item">
        <a href="{{ route('admin.employees.offer-letter') }}"
           class="c-sidebar-nav-link {{ request()->is('admin/employees/offer-letter') ? 'c-active' : '' }}">
            <i class="fa-fw fas fa-file-contract c-sidebar-nav-icon"></i>
            Offer Letter
        </a>
    </li>
@endcan


        </ul>
    </li>
@endcan

        @can('product_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/product-categories*") ? "c-show" : "" }} {{ request()->is("admin/product-tags*") ? "c-show" : "" }} {{ request()->is("admin/products*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-shopping-cart c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.productManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('company_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.companies.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/companies") || request()->is("admin/companies/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-copyright c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.company.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('product_category_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.product-categories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/product-categories") || request()->is("admin/product-categories/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-folder c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.productCategory.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('product_tag_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.product-tags.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/product-tags") || request()->is("admin/product-tags/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-folder c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.productTag.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('product_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.products.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/products") || request()->is("admin/products/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-shopping-cart c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.product.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
            @can('create_order_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/orders*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-cart-arrow-down c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.createOrder.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('order_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.orders.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/orders") || request()->is("admin/orders/*") ? "c-active" : "" }}">
                                <i class="fa-fw fab fa-accusoft c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.order.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('customer_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/make-customers*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-user-friends c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.customer.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('make_customer_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.make-customers.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/make-customers") || request()->is("admin/make-customers/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-user-edit c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.makeCustomer.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan   
       @can('attendance_access')
    <li class="c-sidebar-nav-dropdown {{ request()->is('admin/attendance-details*') ? 'c-show' : '' }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-user-clock c-sidebar-nav-icon"></i>
            {{ trans('cruds.attendance.title') }}
        </a>

        <ul class="c-sidebar-nav-dropdown-items">
            @can('attendance_detail_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.attendance-details.index') }}"
                       class="c-sidebar-nav-link {{ request()->is('admin/attendance-details') || request()->is('admin/attendance-details/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                        {{ trans('cruds.attendanceDetail.title') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
        @can('customer_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/make-customers*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-user-friends c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.customer.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('make_customer_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.make-customers.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/make-customers") || request()->is("admin/make-customers/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-user-edit c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.makeCustomer.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('performance_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/reports*") ? "c-show" : "" }} {{ request()->is("admin/show-reports*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-balance-scale c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.performance.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('report_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.performance-reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/reports") || request()->is("admin/reports/*") ? "c-active" : "" }}">
                                <i class="fa-fw fab fa-accusoft c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.performancereport.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('show_report_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.show-reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/show-reports") || request()->is("admin/show-reports/*") ? "c-active" : "" }}">
                                <i class="fa-fw far fa-address-card c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.showReport.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

    @can('leave_access')
    <li class="c-sidebar-nav-dropdown {{ request()->is('admin/holidays*') || request()->is('admin/leave-types*') || request()->is('admin/leave-requests*') ? 'c-show' : '' }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-calendar-alt c-sidebar-nav-icon"></i>
            Leave Management
        </a>

        <ul class="c-sidebar-nav-dropdown-items">
            @can('holidays_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.holidays.index') }}"
                       class="c-sidebar-nav-link {{ request()->is('admin/holidays') || request()->is('admin/holidays/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-umbrella-beach c-sidebar-nav-icon"></i>
                        Holidays
                    </a>
                </li>
            @endcan

            @can('leave_request_type')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.leave-types.index') }}"
                       class="c-sidebar-nav-link {{ request()->is('admin/leave-types') || request()->is('admin/leave-types/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-stream c-sidebar-nav-icon"></i>
                        Leave Types
                    </a>
                </li>
            @endcan

            @can('leave_request_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.leave-requests.index') }}"
                       class="c-sidebar-nav-link {{ request()->is('admin/leave-requests') || request()->is('admin/leave-requests/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-calendar-check c-sidebar-nav-icon"></i>
                        Leave Requests
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan


@can('tracking_access')
    <li class="c-sidebar-nav-dropdown {{ request()->is('admin/add-mambers*') || request()->is('admin/track-members*') || request()->is('admin/reports*') ? 'c-show' : '' }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-street-view c-sidebar-nav-icon"></i>
            {{ trans('cruds.tracking.title') }}
        </a>

        <ul class="c-sidebar-nav-dropdown-items">
            {{-- Track Members --}}
            @can('track_member_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.track-members.index') }}" 
                       class="c-sidebar-nav-link {{ request()->is('admin/track-members') || request()->is('admin/track-members/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-map-marked c-sidebar-nav-icon"></i>
                        {{ trans('cruds.trackMember.title') }}
                    </a>
                </li>
            @endcan

            {{-- Reports --}}
            @can('report_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.reports.index') }}" 
                       class="c-sidebar-nav-link {{ request()->is('admin/reports') || request()->is('admin/reports/*') ? 'c-active' : '' }}">
                        <i class="fa-fw far fa-address-card c-sidebar-nav-icon"></i>
                        {{ trans('cruds.report.title') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
@can('counter_visit_access')
    <li class="c-sidebar-nav-dropdown {{ request()->is('admin/visits*') ? 'c-show' : '' }}">
        <a class="c-sidebar-nav-dropdown-toggle" href="#">
            <i class="fa-fw fas fa-shoe-prints c-sidebar-nav-icon"></i>
            {{ trans('cruds.counterVisit.title') }}
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            @can('visit_access')
                <li class="c-sidebar-nav-item">
                    <a href="{{ route('admin.visits.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/visits') || request()->is('admin/visits/*') ? 'c-active' : '' }}">
                        <i class="fa-fw fas fa-eye c-sidebar-nav-icon"></i>
                        {{ trans('cruds.visit.title') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan


        @can('notification_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.notifications.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/notifications") || request()->is("admin/notifications/*") ? "c-active" : "" }}">
                    <i class="fa-fw fas fa-bell c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.notification.title') }}
                </a>
            </li>
        @endcan
        @can('app_update_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.app-updates.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/app-updates") || request()->is("admin/app-updates/*") ? "c-active" : "" }}">
                    <i class="fa-fw fas fa-upload c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.appUpdate.title') }}
                </a>
            </li>
        @endcan

         @can('expense_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/expense-categories*") ? "c-show" : "" }} {{ request()->is("admin/add-request-amounts*") ? "c-show" : "" }} {{ request()->is("admin/income-categories*") ? "c-show" : "" }} {{ request()->is("admin/expenses*") ? "c-show" : "" }} {{ request()->is("admin/incomes*") ? "c-show" : "" }} {{ request()->is("admin/expense-reports*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-money-bill c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.expenseManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('expense_category_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.expense-categories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/expense-categories") || request()->is("admin/expense-categories/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-list c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.expenseCategory.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('add_request_amount_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.add-request-amounts.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/add-request-amounts") || request()->is("admin/add-request-amounts/*") ? "c-active" : "" }}">
                                <i class="fa-fw fab fa-amazon-pay c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.addRequestAmount.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('income_category_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.income-categories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/income-categories") || request()->is("admin/income-categories/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-list c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.incomeCategory.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('expense_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.expenses.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/expenses") || request()->is("admin/expenses/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-arrow-circle-right c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.expense.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('income_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.incomes.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/incomes") || request()->is("admin/incomes/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-arrow-circle-right c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.income.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('expense_report_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.expense-reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/expense-reports") || request()->is("admin/expense-reports/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-chart-line c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.expenseReport.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'c-active' : '' }}" href="{{ route('profile.password.edit') }}">
                        <i class="fa-fw fas fa-key c-sidebar-nav-icon">
                        </i>
                        {{ trans('global.change_password') }}
                    </a>
                </li>
            @endcan
        @endif
        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                <i class="c-sidebar-nav-icon fas fa-fw fa-sign-out-alt">

                </i>
                {{ trans('global.logout') }}
            </a>
        </li>
    </ul>

</div>