<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $landingPage = \App\SchoolSetting::get('landing_page', 'logins');
    return redirect('/' . $landingPage);
});

// Dynamic PWA manifest
Route::get('/manifest.json', 'ManifestController@manifest')->name('manifest');

Route::get('/logins','UserController@login')->name('login');

Auth::routes();

// Add GET logout route to avoid 419 error
Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.get');

// Parent self-registration routes (public access, no auth required)
Route::get('/parent/register/{token}', 'ParentsController@showRegistrationForm')->name('parent.register.form');
Route::post('/parent/register/{token}', 'ParentsController@completeRegistration')->name('parent.register.complete');
Route::get('/parent/registration-success', 'ParentsController@registrationSuccess')->name('parent.register.success');

Route::get('/web', 'websiteController@index')->name('website.index');
Route::get('/about', 'websiteController@about')->name('website.about');
Route::get('/contact', 'websiteController@contact')->name('website.contact');
Route::get('/courses', 'websiteController@courses')->name('website.courses');
Route::get('/news', 'websiteController@news')->name('website.News');
Route::get('/result', 'websiteController@results')->name('website.results');
Route::get('/success', 'websiteController@success')->name('website.success');

// Student Application Routes
Route::get('/application', 'ApplicationController@index')->name('website.application');
Route::post('/application', 'ApplicationController@store')->name('website.application.store');
Route::get('/application/success', 'ApplicationController@success')->name('website.application.success');

Route::get('/newsletter', 'NewsletterController@showNewsletters')->name('website.News');
//Route::get('/newslettersshow/{id}','NewsletterController@show')->name('website.newsletter.show');
Route::get('/newslettersshow/{id}','NewsletterController@show')->name('website.newsletter.show');

// Shop Routes (Public)
Route::get('/shop', 'ShopController@index')->name('shop.index');
Route::get('/shop/{id}', 'ShopController@show')->name('shop.show');

Route::get('/image/{filename}', 'ImageController@showImage')->name('image.show');






Route::get('/home', 'HomeController@index')->name('home');
Route::get('/class/{id}/subjects', 'HomeController@classSubjects')->name('class.subjects');
Route::get('/api/assessment-stats', 'HomeController@getFilteredAssessmentStats')->name('api.assessment.stats');
Route::get('/api/gender-stats', 'HomeController@getFilteredGenderStats')->name('api.gender.stats');
Route::get('/api/assessment-stats-by-gender', 'HomeController@getFilteredAssessmentStatsByGender')->name('api.assessment.stats.by.gender');
Route::get('/profile', 'HomeController@profile')->name('profile');
Route::get('/profile/edit', 'HomeController@profileEdit')->name('profile.edit');
Route::put('/profile/update', 'HomeController@profileUpdate')->name('profile.update');
Route::get('/profile/changepassword', 'HomeController@changePasswordForm')->name('profile.change.password');
Route::post('/profile/changepassword', 'HomeController@changePassword')->name('profile.changepassword');

// Student password change routes
Route::get('/student/change-password', 'StudentController@showChangePasswordForm')->name('student.change-password');
Route::post('/student/update-password', 'StudentController@updatePassword')->name('student.update-password');
Route::post('/student/update-chair-desk', 'StudentController@updateChairDesk')->name('student.update-chair-desk');

// Force password change for users with must_change_password flag
Route::get('/user/force-change-password', 'HomeController@showForceChangePasswordForm')->name('user.force-change-password')->middleware('auth');
Route::post('/user/force-change-password', 'HomeController@forceChangePassword')->name('user.force-change-password.update')->middleware('auth');

// Parent timetable route
Route::get('/child-timetable', 'TimetableController@parentView')->name('parent.timetable')->middleware(['auth', 'role:Parent']);

// User Notifications Inbox (for all authenticated users)
Route::get('/notifications', 'SchoolNotificationController@inbox')->name('notifications.inbox')->middleware('auth');
Route::post('/notifications/{id}/read', 'SchoolNotificationController@markAsRead')->name('notifications.read')->middleware('auth');

// Shared routes for Admin and Teacher - Student creation
Route::group(['middleware' => ['auth', 'role:Admin|Teacher']], function () {
    Route::get('student/create', 'StudentController@create')->name('shared.student.create');
    Route::post('student', 'StudentController@store')->name('shared.student.store');
    Route::get('student-with-parents/create', 'StudentController@createWithParents')->name('shared.student.create-with-parents');
    Route::post('student-with-parents', 'StudentController@storeWithParents')->name('shared.student.store-with-parents');
    Route::post('student-with-parents/import', 'StudentController@importStudentsWithParents')->name('shared.student.import-with-parents');
    Route::get('student-with-parents/template', 'StudentController@downloadStudentTemplate')->name('shared.student.template');
    Route::get('student/generate-roll-number', 'StudentController@generateRollNumberAjax')->name('student.generate-roll-number');
});

Route::group(['middleware' => ['auth','role_or_permission:Admin|sidebar-finance|sidebar-student-payments|sidebar-parents-arrears|sidebar-school-income|sidebar-school-expenses|sidebar-financial-statements|sidebar-grocery-arrears|sidebar-grocery-stock|sidebar-inventory|sidebar-student-groceries|sidebar-library-records|sidebar-classes|sidebar-subjects|sidebar-teachers|sidebar-parents|sidebar-students|sidebar-results|sidebar-attendance|sidebar-timetable|sidebar-settings']], function ()
{
    Route::get('/roles-permissions', 'RolePermissionController@roles')->name('roles-permissions');
    Route::get('/role-create', 'RolePermissionController@createRole')->name('role.create');
    Route::post('/role-store', 'RolePermissionController@storeRole')->name('role.store');
    Route::get('/role-edit/{id}', 'RolePermissionController@editRole')->name('role.edit');
    Route::put('/role-update/{id}', 'RolePermissionController@updateRole')->name('role.update');
    Route::get('/permission-create', 'RolePermissionController@createPermission')->name('permission.create');
    Route::post('/permission-store', 'RolePermissionController@storePermission')->name('permission.store');
    Route::get('/permission-edit/{id}', 'RolePermissionController@editPermission')->name('permission.edit');
    Route::put('/permission-update/{id}', 'RolePermissionController@updatePermission')->name('permission.update');
    Route::get('/sidebar-permissions', 'RolePermissionController@manageSidebarPermissions')->name('sidebar.permissions');
    Route::post('/sidebar-permissions/update', 'RolePermissionController@updateUserSidebarPermissions')->name('sidebar.permissions.update');
    Route::get('assign-subject-to-class/{id}', 'GradeController@assignSubject')->name('class.assign.subject');
    Route::post('assign-subject-to-class/{id}', 'GradeController@storeAssignedSubject')->name('store.class.assign.subject');
    Route::resource('assignrole', 'RoleAssign');
    Route::resource('classes', 'GradeController');
    /////
    // Show the post and view student results

    Route::post('/adminshowclassresults/year', 'ResultController@adminshowResult')->name('adminresults.classresults');
     Route::get('/classnameadmin/{class_id}','ResultController@adminclassnames')->name('adminresults.classname');
    Route::get('/studentadmin/show/{student}', 'ResultController@adminStuntentname')->name('adminresults.yearsubject');


    // Store the updated results status
    Route::get('manageresults', 'GradeController@adminindex')->name('manageresults.index');
    ////
    // Admin Subject Management Routes
    Route::get('admin/subjects', 'AdminSubjectController@index')->name('admin.subjects.index');
    Route::get('admin/subjects/class/{classId}', 'AdminSubjectController@showByClass')->name('admin.subjects.byClass');
    Route::get('admin/subjects/create', 'AdminSubjectController@create')->name('admin.subjects.create');
    Route::post('admin/subjects', 'AdminSubjectController@store')->name('admin.subjects.store');
    Route::get('admin/subjects/assign', 'AdminSubjectController@assignForm')->name('admin.subjects.assign');
    Route::post('admin/subjects/assign', 'AdminSubjectController@assign')->name('admin.subjects.assign.store');
    Route::post('admin/subjects/assign/import', 'AdminSubjectController@importAssignments')->name('admin.subjects.assign.import');
    Route::get('admin/subjects/assign/template', 'AdminSubjectController@downloadTemplate')->name('admin.subjects.assign.template');
    Route::delete('admin/subjects/unassign/{id}', 'AdminSubjectController@unassign')->name('admin.subjects.unassign');
    Route::post('admin/subjects/bulk-unassign', 'AdminSubjectController@bulkUnassign')->name('admin.subjects.bulkUnassign');
    Route::get('admin/subjects/{subject}/edit', 'AdminSubjectController@edit')->name('admin.subjects.edit');
    Route::put('admin/subjects/{subject}', 'AdminSubjectController@update')->name('admin.subjects.update');
    Route::delete('admin/subjects/{subject}', 'AdminSubjectController@destroy')->name('admin.subjects.destroy');
    
    // Admin Syllabus Topics Management Routes
    Route::get('admin/syllabus', 'AdminSyllabusController@index')->name('admin.syllabus.index');
    Route::get('admin/syllabus/create', 'AdminSyllabusController@create')->name('admin.syllabus.create');
    Route::post('admin/syllabus', 'AdminSyllabusController@store')->name('admin.syllabus.store');
    Route::get('admin/syllabus/{id}/edit', 'AdminSyllabusController@edit')->name('admin.syllabus.edit');
    Route::put('admin/syllabus/{id}', 'AdminSyllabusController@update')->name('admin.syllabus.update');
    Route::delete('admin/syllabus/{id}', 'AdminSyllabusController@destroy')->name('admin.syllabus.destroy');
    
    // Onboard Subject Routes
    Route::get('admin/onboard-subjects', 'OnboardSubjectController@index')->name('admin.onboard-subjects.index');
    Route::post('admin/onboard-subjects', 'OnboardSubjectController@store')->name('admin.onboard-subjects.store');
    Route::delete('admin/onboard-subjects/{id}', 'OnboardSubjectController@destroy')->name('admin.onboard-subjects.destroy');
    Route::get('api/onboard-subjects', 'OnboardSubjectController@getSubjects')->name('api.onboard-subjects');
    
    Route::resource('subject', 'SubjectController');
    Route::resource('teacher', 'TeacherController')->except(['show']);
    Route::get('teacher/sessions', 'TeacherController@sessions')->name('teacher.sessions');
    Route::post('teacher/update-sessions', 'TeacherController@updateSessions')->name('teacher.update-sessions');
    Route::resource('parents', 'ParentsController');
    Route::post('parents/{id}/force-password-reset', 'ParentsController@forcePasswordReset')->name('parents.force-password-reset');
    Route::resource('student', 'StudentController')->except(['create', 'store']);
    Route::post('student/{id}/force-password-reset', 'StudentController@forcePasswordReset')->name('student.force-password-reset');
    Route::post('students/bulk-update-to-existing', 'StudentController@bulkUpdateToExisting')->name('students.bulk-update-to-existing');
    Route::post('teacher/{id}/force-password-reset', 'TeacherController@forcePasswordReset')->name('teacher.force-password-reset');

    // Admin Applicants Routes
    Route::get('admin/applicants', 'AdminApplicantController@index')->name('admin.applicants.index');
    Route::get('admin/applicants/{id}', 'AdminApplicantController@show')->name('admin.applicants.show');
    Route::patch('admin/applicants/{id}/status', 'AdminApplicantController@updateStatus')->name('admin.applicants.updateStatus');
    Route::delete('admin/applicants/{id}', 'AdminApplicantController@destroy')->name('admin.applicants.destroy');

    // Teacher Device Management Routes
    Route::get('admin/teacher-devices', 'TeacherDeviceController@index')->name('teacher-devices.index');
    Route::get('admin/teacher-devices/{id}', 'TeacherDeviceController@show')->name('teacher-devices.show');
    Route::post('admin/teacher-devices/{id}/enable', 'TeacherDeviceController@enableRegistration')->name('teacher-devices.enable');
    Route::post('admin/teacher-devices/{id}/allow-change', 'TeacherDeviceController@allowPhoneChange')->name('teacher-devices.allow-change');
    Route::post('admin/teacher-devices/{id}/reset', 'TeacherDeviceController@resetDevice')->name('teacher-devices.reset');
    Route::post('admin/teacher-devices/device/{deviceId}/revoke', 'TeacherDeviceController@revokeDevice')->name('teacher-devices.revoke');
    Route::post('admin/teacher-devices/bulk-enable', 'TeacherDeviceController@bulkEnableRegistration')->name('teacher-devices.bulk-enable');

    // School Staff Routes
    Route::get('admin/staff', 'AdminStaffController@index')->name('admin.staff.index');
    Route::get('admin/staff/create', 'AdminStaffController@create')->name('admin.staff.create');
    Route::post('admin/staff', 'AdminStaffController@store')->name('admin.staff.store');
    Route::get('admin/staff/{id}', 'AdminStaffController@show')->name('admin.staff.show');
    Route::get('admin/staff/{id}/edit', 'AdminStaffController@edit')->name('admin.staff.edit');
    Route::put('admin/staff/{id}', 'AdminStaffController@update')->name('admin.staff.update');
    Route::delete('admin/staff/{id}', 'AdminStaffController@destroy')->name('admin.staff.destroy');

    // Admin User Management Routes
    Route::get('admin/users', 'AdminUserController@index')->name('admin.users.index');
    Route::get('admin/users/create', 'AdminUserController@create')->name('admin.users.create');
    Route::post('admin/users', 'AdminUserController@store')->name('admin.users.store');
    Route::get('admin/users/{id}', 'AdminUserController@show')->name('admin.users.show');
    Route::get('admin/users/{id}/edit', 'AdminUserController@edit')->name('admin.users.edit');
    Route::put('admin/users/{id}', 'AdminUserController@update')->name('admin.users.update');
    Route::delete('admin/users/{id}', 'AdminUserController@destroy')->name('admin.users.destroy');

    // Admin Teacher Schemes & Syllabus Overview Routes
    Route::get('admin/teacher-schemes', 'AdminSchemeController@index')->name('admin.schemes.index');
    Route::get('admin/teacher-schemes/{id}', 'AdminSchemeController@show')->name('admin.schemes.show');
    Route::get('admin/teacher-syllabus', 'AdminSchemeController@syllabusIndex')->name('admin.teacher-syllabus.index');
    Route::get('admin/teacher/{teacherId}/schemes', 'AdminSchemeController@teacherSchemes')->name('admin.schemes.teacher');

    // Teacher Attendance Scanner Routes (QR Code Based)
    Route::get('attendance/logbook', 'AttendanceScanController@index')->name('attendance.logbook');
    Route::get('attendance/logbook/export', 'AttendanceScanController@exportLogbook')->name('attendance.logbook.export');
    Route::post('attendance/scan', 'AttendanceScanController@scan')->name('attendance.scan');
    Route::get('attendance/availability', 'AttendanceScanController@availability')->name('attendance.availability');
    Route::get('attendance/teacher/{teacherId}/history', 'AttendanceScanController@teacherHistory')->name('attendance.teacher.history');
    Route::post('attendance/teacher/{teacherId}/generate-qr', 'AttendanceScanController@generateQrCode')->name('attendance.generate.qr');
    Route::post('attendance/teacher/{teacherId}/regenerate-qr', 'AttendanceScanController@regenerateQrCode')->name('attendance.regenerate.qr');
    Route::post('attendance/teacher/{teacherId}/clear-qr', 'AttendanceScanController@clearQrCode')->name('attendance.clear.qr');
    Route::post('attendance/clear-all-qr', 'AttendanceScanController@clearAllQrCodes')->name('attendance.clear.all.qr');
    Route::get('attendance/teacher/{teacherId}/qr', 'AttendanceScanController@getQrCode')->name('attendance.get.qr');
    Route::get('attendance/teacher/{teacherId}/print-qr', 'AttendanceScanController@printQrCode')->name('attendance.print.qr');
    
    // Teacher Attendance History Management
    Route::get('attendance/history', 'AttendanceScanController@attendanceHistory')->name('attendance.history');
    Route::post('attendance/store', 'AttendanceScanController@storeAttendance')->name('attendance.store');
    Route::put('attendance/{id}/update', 'AttendanceScanController@updateAttendance')->name('attendance.update');
    Route::delete('attendance/{id}/delete', 'AttendanceScanController@deleteAttendance')->name('attendance.delete');
    
    // Attendance Settings
    Route::get('admin/attendance/settings', 'AttendanceSettingsController@index')->name('admin.attendance.settings');
    Route::post('admin/attendance/settings', 'AttendanceSettingsController@update')->name('admin.attendance.settings.update');

    // Leave Management Routes
    Route::get('admin/leave', 'AdminLeaveController@index')->name('admin.leave.index');
    Route::get('admin/leave/{id}', 'AdminLeaveController@show')->name('admin.leave.show');
    Route::post('admin/leave/{id}/approve', 'AdminLeaveController@approve')->name('admin.leave.approve');
    Route::post('admin/leave/{id}/reject', 'AdminLeaveController@reject')->name('admin.leave.reject');
    Route::get('admin/leave/calendar/data', 'AdminLeaveController@calendar')->name('admin.leave.calendar');

    // Non-Teaching Staff Management
    Route::get('admin/non-teaching-staff', 'NonTeachingStaffController@index')->name('admin.non-teaching-staff.index');
    Route::get('admin/non-teaching-staff/create', 'NonTeachingStaffController@create')->name('admin.non-teaching-staff.create');
    Route::post('admin/non-teaching-staff', 'NonTeachingStaffController@store')->name('admin.non-teaching-staff.store');
    Route::get('admin/non-teaching-staff/{id}', 'NonTeachingStaffController@show')->name('admin.non-teaching-staff.show');
    Route::get('admin/non-teaching-staff/{id}/edit', 'NonTeachingStaffController@edit')->name('admin.non-teaching-staff.edit');
    Route::put('admin/non-teaching-staff/{id}', 'NonTeachingStaffController@update')->name('admin.non-teaching-staff.update');
    Route::delete('admin/non-teaching-staff/{id}', 'NonTeachingStaffController@destroy')->name('admin.non-teaching-staff.destroy');

    // Finance - Payroll Routes
    Route::get('admin/finance/payroll', 'PayrollController@index')->name('admin.finance.payroll.index');
    Route::get('admin/finance/payroll/salaries', 'PayrollController@salaries')->name('admin.finance.payroll.salaries');
    Route::get('admin/finance/payroll/salaries/create', 'PayrollController@createSalary')->name('admin.finance.payroll.create-salary');
    Route::post('admin/finance/payroll/salaries', 'PayrollController@storeSalary')->name('admin.finance.payroll.store-salary');
    Route::get('admin/finance/payroll/salaries/{id}/edit', 'PayrollController@editSalary')->name('admin.finance.payroll.edit-salary');
    Route::put('admin/finance/payroll/salaries/{id}', 'PayrollController@updateSalary')->name('admin.finance.payroll.update-salary');
    Route::get('admin/finance/payroll/generate', 'PayrollController@generate')->name('admin.finance.payroll.generate');
    Route::post('admin/finance/payroll/generate', 'PayrollController@processGenerate')->name('admin.finance.payroll.process-generate');
    Route::get('admin/finance/payroll/{id}', 'PayrollController@show')->name('admin.finance.payroll.show');
    Route::post('admin/finance/payroll/{id}/approve', 'PayrollController@approve')->name('admin.finance.payroll.approve');
    Route::post('admin/finance/payroll/{id}/mark-paid', 'PayrollController@markPaid')->name('admin.finance.payroll.mark-paid');
    Route::get('admin/finance/payroll/{id}/payslip', 'PayrollController@payslip')->name('admin.finance.payroll.payslip');

    // Finance - Cash Book Routes
    Route::get('admin/finance/cashbook', 'CashBookController@index')->name('admin.finance.cashbook.index');
    Route::get('admin/finance/cashbook/create', 'CashBookController@create')->name('admin.finance.cashbook.create');
    Route::post('admin/finance/cashbook', 'CashBookController@store')->name('admin.finance.cashbook.store');
    Route::get('admin/finance/cashbook/report', 'CashBookController@report')->name('admin.finance.cashbook.report');
    Route::get('admin/finance/cashbook/{id}', 'CashBookController@show')->name('admin.finance.cashbook.show');
    Route::get('admin/finance/cashbook/{id}/edit', 'CashBookController@edit')->name('admin.finance.cashbook.edit');
    Route::put('admin/finance/cashbook/{id}', 'CashBookController@update')->name('admin.finance.cashbook.update');
    Route::delete('admin/finance/cashbook/{id}', 'CashBookController@destroy')->name('admin.finance.cashbook.destroy');

    // Finance - Dashboard & Reports
    Route::get('admin/finance/dashboard', 'FinanceDashboardController@index')->name('admin.finance.dashboard');
    Route::get('admin/finance/reports/income-statement', 'FinanceDashboardController@incomeStatement')->name('admin.finance.reports.income-statement');
    Route::get('admin/finance/reports/balance-sheet', 'FinanceDashboardController@balanceSheet')->name('admin.finance.reports.balance-sheet');
    Route::get('admin/finance/reports/expense-report', 'FinanceDashboardController@expenseReport')->name('admin.finance.reports.expense-report');
    Route::get('admin/finance/reports/fee-report', 'FinanceDashboardController@feeReport')->name('admin.finance.reports.fee-report');

    // Finance - Inventory Management
    Route::get('finance/inventory', 'ProductController@inventory')->name('finance.inventory.index');
    Route::get('finance/inventory/movements', 'ProductController@stockMovements')->name('finance.inventory.movements');

    // Finance - Product Categories
    Route::get('finance/categories', 'ProductController@categories')->name('finance.categories.index');
    Route::post('finance/categories', 'ProductController@storeCategory')->name('finance.categories.store');
    Route::delete('finance/categories/{id}', 'ProductController@deleteCategory')->name('finance.categories.delete');

    // Finance - Products & Sales
    Route::get('finance/products', 'ProductController@index')->name('finance.products');
    Route::get('finance/products/create', 'ProductController@create')->name('finance.products.create');
    Route::post('finance/products/store', 'ProductController@store')->name('finance.products.store');
    Route::get('finance/products/pos', 'ProductController@pos')->name('finance.products.pos');
    Route::get('finance/products/find-by-barcode', 'ProductController@findByBarcode')->name('finance.products.find-by-barcode');
    Route::post('finance/products/process-sale', 'ProductController@processSale')->name('finance.products.process-sale');
    Route::get('finance/products/search-students', 'ProductController@searchAllStudents')->name('finance.products.search-students');
    Route::get('finance/products/student-purchases', 'ProductController@studentPurchases')->name('finance.products.student-purchases');
    Route::get('finance/products/student-purchases/{id}', 'ProductController@studentPurchaseHistory')->name('finance.products.student-purchase-history');
    Route::get('finance/products/sales-history', 'ProductController@salesHistory')->name('finance.products.sales-history');
    Route::get('finance/products/sales/{id}/receipt', 'ProductController@saleReceipt')->name('finance.products.sale-receipt');
    Route::get('finance/products/{id}', 'ProductController@show')->name('finance.products.show');
    Route::get('finance/products/{id}/edit', 'ProductController@edit')->name('finance.products.edit');
    Route::put('finance/products/{id}', 'ProductController@update')->name('finance.products.update');
    Route::post('finance/products/{id}/adjust-stock', 'ProductController@adjustStock')->name('finance.products.adjust-stock');

    // Finance - Student Collections (Uniforms, Report Cards, IDs, etc.)
    Route::get('finance/student-collections', 'ProductController@uniformCollections')->name('finance.uniform-collections');
    Route::get('finance/student-collections/search-student', 'ProductController@searchStudentForUniform')->name('finance.uniform-collections.search-student');
    Route::get('finance/student-collections/search-all-students', 'ProductController@searchAllStudents')->name('finance.uniform-collections.search-all-students');
    Route::get('finance/student-collections/student/{id}', 'ProductController@studentUniformHistory')->name('finance.uniform-collections.student');
    Route::post('finance/student-collections/record-uniform', 'ProductController@recordUniformCollection')->name('finance.uniform-collections.record');
    Route::post('finance/student-collections/record-item', 'ProductController@recordItemCollection')->name('finance.uniform-collections.record-item');
    Route::post('finance/student-collections/{id}/mark-collected', 'ProductController@markUniformCollected')->name('finance.uniform-collections.mark-collected');

    // Finance - Expenses
    Route::get('admin/finance/expenses', 'ExpenseController@index')->name('admin.finance.expenses.index');
    Route::get('admin/finance/expenses/create', 'ExpenseController@create')->name('admin.finance.expenses.create');
    Route::post('admin/finance/expenses', 'ExpenseController@store')->name('admin.finance.expenses.store');
    Route::get('admin/finance/expenses/categories', 'ExpenseController@categories')->name('admin.finance.expenses.categories');
    Route::post('admin/finance/expenses/categories', 'ExpenseController@storeCategory')->name('admin.finance.expenses.store-category');
    Route::get('admin/finance/expenses/report', 'ExpenseController@report')->name('admin.finance.expenses.report');
    Route::get('admin/finance/expenses/{id}', 'ExpenseController@show')->name('admin.finance.expenses.show');
    Route::get('admin/finance/expenses/{id}/edit', 'ExpenseController@edit')->name('admin.finance.expenses.edit');
    Route::put('admin/finance/expenses/{id}', 'ExpenseController@update')->name('admin.finance.expenses.update');
    Route::post('admin/finance/expenses/{id}/approve', 'ExpenseController@approve')->name('admin.finance.expenses.approve');

    // Finance - Purchase Orders
    Route::get('admin/finance/purchase-orders', 'PurchaseOrderController@index')->name('admin.finance.purchase-orders.index');
    Route::get('admin/finance/purchase-orders/create', 'PurchaseOrderController@create')->name('admin.finance.purchase-orders.create');
    Route::post('admin/finance/purchase-orders', 'PurchaseOrderController@store')->name('admin.finance.purchase-orders.store');
    Route::get('admin/finance/purchase-orders/suppliers', 'PurchaseOrderController@suppliers')->name('admin.finance.purchase-orders.suppliers');
    Route::post('admin/finance/purchase-orders/suppliers', 'PurchaseOrderController@storeSupplier')->name('admin.finance.purchase-orders.store-supplier');
    Route::get('admin/finance/purchase-orders/{id}', 'PurchaseOrderController@show')->name('admin.finance.purchase-orders.show');
    Route::post('admin/finance/purchase-orders/{id}/approve', 'PurchaseOrderController@approve')->name('admin.finance.purchase-orders.approve');
    Route::post('admin/finance/purchase-orders/{id}/mark-ordered', 'PurchaseOrderController@markOrdered')->name('admin.finance.purchase-orders.mark-ordered');
    Route::post('admin/finance/purchase-orders/{id}/mark-received', 'PurchaseOrderController@markReceived')->name('admin.finance.purchase-orders.mark-received');
    Route::post('admin/finance/purchase-orders/{id}/record-invoice', 'PurchaseOrderController@recordInvoice')->name('admin.finance.purchase-orders.record-invoice');
    Route::post('admin/finance/purchase-orders/{id}/record-payment', 'PurchaseOrderController@recordPayment')->name('admin.finance.purchase-orders.record-payment');

    // Timetable Routes
    Route::get('admin/timetable', 'AdminTimetableController@index')->name('admin.timetable.index');
    Route::get('admin/timetable/master', 'AdminTimetableController@master')->name('admin.timetable.master');
    Route::get('admin/timetable/create', 'AdminTimetableController@create')->name('admin.timetable.create');
    Route::post('admin/timetable', 'AdminTimetableController@store')->name('admin.timetable.store');
    Route::get('admin/timetable/{id}', 'AdminTimetableController@show')->name('admin.timetable.show');
    Route::get('admin/timetable/{id}/edit', 'AdminTimetableController@edit')->name('admin.timetable.edit');
    Route::put('admin/timetable/{id}', 'AdminTimetableController@update')->name('admin.timetable.update');
    Route::delete('admin/timetable/{id}', 'AdminTimetableController@destroy')->name('admin.timetable.destroy');
    Route::post('admin/timetable/check-conflicts', 'AdminTimetableController@checkConflicts')->name('admin.timetable.check-conflicts');
    Route::post('admin/timetable/clear', 'AdminTimetableController@clear')->name('admin.timetable.clear');

    // Resend parent SMS (Admin only)
    Route::post('student/{student}/resend-parent-sms', 'StudentController@resendParentSms')->name('student.resend-parent-sms');

    // SMS Test Routes
    Route::get('sms-test', 'SmsTestController@index')->name('sms-test.index');
    Route::post('sms-test/send', 'SmsTestController@send')->name('sms-test.send');

    Route::get('attendance', 'AttendanceController@index')->name('attendance.index');
    Route::get('attendance/class/{class_id}', 'AttendanceController@classDetail')->name('attendance.class-detail');
    Route::delete('attendance/clean/{class_id}', 'AttendanceController@cleanAttendance')->name('attendance.clean');
    ///banner
    Route::get('/banner', 'BannerController@index')->name('banner.index');
    Route::post('/banner', 'BannerController@store')->name('banner.store');
    ///Newsletter
    Route::get('newsletters', 'NewsletterController@index')->name('newsletters.index');
    Route::get('newsletters/create', 'NewsletterController@create')->name('newsletters.create');
    Route::post('newsletters', 'NewsletterController@store')->name('newsletters.store');
    Route::get('newsletters/{newsletter}', 'NewsletterController@show')->name('newsletters.show');
    Route::get('newsletters/{newsletter}/edit', 'NewsletterController@edit')->name('newsletters.edit');
    Route::put('newsletters/{newsletter}', 'NewsletterController@update')->name('newsletters.update');
    Route::delete('newsletters/{newsletter}', 'NewsletterController@destroy')->name('newsletters.destroy');
    // Routes for ResultsStatus management
    Route::get('/Term/create', 'ResultsStatusController@create')->name('results_status.create');
    Route::post('/Term', 'ResultsStatusController@store')->name('results_status.store');
    Route::get('/Term/{id}/edit', 'ResultsStatusController@edit')->name('results_status.edit');
    Route::put('/Term/{id}', 'ResultsStatusController@update')->name('results_status.update');
    Route::get('/Term', 'ResultsStatusController@index')->name('results_status.index');
    Route::delete('/results-status/{id}', 'ResultsStatusController@destroy')->name('results_status.destroy');

    // Fee Level Groups management
    Route::get('/fee-level-groups', 'FeeLevelGroupController@index')->name('fee-level-groups.index');
    Route::get('/fee-level-groups/create', 'FeeLevelGroupController@create')->name('fee-level-groups.create');
    Route::post('/fee-level-groups', 'FeeLevelGroupController@store')->name('fee-level-groups.store');
    Route::post('/fee-level-groups/apply-to-new-students', 'FeeLevelGroupController@applyToNewStudents')->name('fee-level-groups.apply-to-new-students');
    Route::get('/fee-level-groups/{id}/edit', 'FeeLevelGroupController@edit')->name('fee-level-groups.edit');
    Route::put('/fee-level-groups/{id}', 'FeeLevelGroupController@update')->name('fee-level-groups.update');
    Route::delete('/fee-level-groups/{id}', 'FeeLevelGroupController@destroy')->name('fee-level-groups.destroy');

    // Fee Types management
    Route::get('/fee-types', 'FeeTypeController@index')->name('fee_types.index');
    Route::get('/fee-types/create', 'FeeTypeController@create')->name('fee_types.create');
    Route::post('/fee-types', 'FeeTypeController@store')->name('fee_types.store');
    Route::get('/fee-types/{id}/edit', 'FeeTypeController@edit')->name('fee_types.edit');
    Route::put('/fee-types/{id}', 'FeeTypeController@update')->name('fee_types.update');
    Route::delete('/fee-types/{id}', 'FeeTypeController@destroy')->name('fee_types.destroy');
    ///resultactive
    Route::get('/activeresults', 'ResultController@resultsactive')->name('activeresults.index');
    Route::get('/viewstudent/{student}', 'ResultController@changestatus')->name('viewstudentstatus.results');
    Route::get('/Admin/results/{class_id}','ResultController@activelistResults')->name('active.results');
    Route::get('/viewstudent/viewresults/{student}', 'ResultController@viewupdateresults')->name('viewstudent.results');
    //fees import
    Route::post('/students/import', 'StudentController@import')->name('students.import');
    //fees paymeny
    Route::get('payments/create/{studentId}', 'PaymentController@create')->name('payments.create');
    Route::post('payments', 'PaymentController@store')->name('payments.store');
    Route::get('payments', 'PaymentController@index')->name('payments.index');
    Route::get('payments/receipt/{id}', 'PaymentController@receipt')->name('payments.receipt');
    Route::resource('fee_categories', 'FeeCategoryController');
    Route::get('payments/receipt/download/{id}', 'PaymentController@downloadReceipt')->name('payments.receipt.download');
    Route::get('/reports', 'ReportController@index')->name('reports.index');
     //webcam and student ID
    Route::get('/webcam', 'WebcamController@index')->name('Webcam.index');
    Route::post('/webcam/upload', 'WebcamController@upload')->name('webcam.upload');
    Route::get('/webcam/students/{classId}', 'WebcamController@getStudentsByClass')->name('webcam.students');
    Route::get('/webcam/student/{id}', 'WebcamController@getStudent')->name('webcam.student');
    Route::post('/webcam/capture', 'WebcamController@capturePhoto')->name('webcam.capture');
    Route::get('/webcam/id-card/{studentId}', 'WebcamController@generateIdCard')->name('webcam.idcard');
    //news letters
    Route::resource('newsletters', 'NewsletterController')->except(['edit', 'update']);
    Route::post('newsletters/{id}/publish', 'NewsletterController@publish')->name('newsletters.publish');
    
    // Events
    Route::get('events', 'EventController@index')->name('events.index');
    Route::get('events/create', 'EventController@create')->name('events.create');
    Route::post('events', 'EventController@store')->name('events.store');
    Route::get('events/{event}/edit', 'EventController@edit')->name('events.edit');
    Route::put('events/{event}', 'EventController@update')->name('events.update');
    Route::delete('events/{event}', 'EventController@destroy')->name('events.destroy');
    
    // Admin Notifications
    Route::get('/admin/notifications', 'SchoolNotificationController@adminIndex')->name('admin.notifications.index');
    Route::get('/admin/notifications/create', 'SchoolNotificationController@create')->name('admin.notifications.create');
    Route::post('/admin/notifications', 'SchoolNotificationController@store')->name('admin.notifications.store');
    Route::post('/admin/notifications/send-sms', 'SchoolNotificationController@sendSms')->name('admin.notifications.send-sms');
    Route::get('/admin/notifications/{id}', 'SchoolNotificationController@show')->name('admin.notifications.show');
    Route::delete('/admin/notifications/{id}', 'SchoolNotificationController@destroy')->name('admin.notifications.destroy');
    
    // Admin Payment Verification Routes
    Route::get('/admin/payment-verification', 'PaymentVerificationController@adminIndex')->name('admin.payment-verification.index');
    Route::get('/admin/payment-verification/{id}', 'PaymentVerificationController@show')->name('admin.payment-verification.show');
    Route::post('/admin/payment-verification/{id}/verify', 'PaymentVerificationController@verify')->name('admin.payment-verification.verify');
    Route::post('/admin/payment-verification/{id}/reject', 'PaymentVerificationController@reject')->name('admin.payment-verification.reject');
    
    //studentid
    Route::get('/student/{id}/id-card', 'StudentController@showid')->name('student.id_card');
    Route::get('/student/{id}/id-card/download', 'StudentController@downloadIdCard')->name('student.download_id_card');

    // Scholarships Routes
    Route::get('/admin/scholarships', 'Admin\ScholarshipController@index')->name('admin.scholarships.index');
    Route::put('/admin/scholarships/{student}', 'Admin\ScholarshipController@update')->name('admin.scholarships.update');
    Route::post('/admin/scholarships/bulk-update', 'Admin\ScholarshipController@bulkUpdate')->name('admin.scholarships.bulk-update');

    // Admin View Results Routes
    Route::get('/admin/view-results', 'ResultController@adminViewResults')->name('admin.view-results');
    Route::post('/admin/get-results', 'ResultController@getAdminResults')->name('admin.get-results');
    Route::post('/admin/clean-results', 'ResultController@cleanResults')->name('admin.clean-results');
    
    // Admin Results Approval Routes
    Route::get('/admin/results/pending-approval', 'ResultController@pendingApproval')->name('admin.results.pending-approval');
    Route::post('/admin/results/get-pending', 'ResultController@getPendingResults')->name('admin.results.get-pending');
    Route::post('/admin/results/approve', 'ResultController@approveResults')->name('admin.results.approve');
    Route::post('/admin/results/approve-all', 'ResultController@approveAllResults')->name('admin.results.approve-all');
    Route::post('/admin/results/reject', 'ResultController@rejectResults')->name('admin.results.reject');
    Route::post('/admin/results/exempt-student', 'ResultController@exemptStudentResults')->name('admin.results.exempt-student');
    Route::post('/admin/results/remove-exemption', 'ResultController@removeExemption')->name('admin.results.remove-exemption');
    Route::post('/admin/results/get-exempted', 'ResultController@getExemptedStudents')->name('admin.results.get-exempted');

    // Admin Assessment Marks Approval Routes
    Route::get('/admin/assessment-marks/pending', 'ResultController@pendingAssessmentMarks')->name('admin.assessment-marks.pending');
    Route::post('/admin/assessment-marks/get-pending', 'ResultController@getPendingAssessmentMarks')->name('admin.assessment-marks.get-pending');
    Route::post('/admin/assessment-marks/get-marks', 'ResultController@getAssessmentMarksForApproval')->name('admin.assessment-marks.get-marks');
    Route::post('/admin/assessment-marks/approve', 'ResultController@approveAssessmentMarks')->name('admin.assessment-marks.approve');
    Route::post('/admin/assessment-marks/approve-all', 'ResultController@approveAllAssessmentMarks')->name('admin.assessment-marks.approve-all');

    // Seat Assignment Routes (Admin)
    Route::get('/admin/seat-assignment', 'StudentController@seatAssignmentIndex')->name('admin.seat-assignment.index');
    Route::post('/admin/seat-assignment/{student}', 'StudentController@updateSeatAssignment')->name('admin.seat-assignment.update');

    // Disciplinary Records Routes (Admin)
    Route::get('/admin/disciplinary-records', 'DisciplinaryController@index')->name('admin.disciplinary.index');
    Route::post('/admin/disciplinary-records', 'DisciplinaryController@store')->name('admin.disciplinary.store');
    Route::get('/admin/disciplinary-records/class/{class_id}/students', 'DisciplinaryController@getStudentsByClass')->name('admin.disciplinary.students');
    Route::put('/admin/disciplinary-records/{id}', 'DisciplinaryController@update')->name('admin.disciplinary.update');
    Route::delete('/admin/disciplinary-records/{id}', 'DisciplinaryController@destroy')->name('admin.disciplinary.destroy');

    // Admin Medical Reports Routes
    Route::get('/admin/medical-reports', 'MedicalReportController@adminIndex')->name('admin.medical-reports.index');
    Route::get('/admin/medical-reports/{id}', 'MedicalReportController@adminShow')->name('admin.medical-reports.show');
    Route::post('/admin/medical-reports/{id}/acknowledge', 'MedicalReportController@adminAcknowledge')->name('admin.medical-reports.acknowledge');
    Route::post('/admin/medical-reports/{id}/review', 'MedicalReportController@adminReview')->name('admin.medical-reports.review');

    // Admin Marking Scheme Routes
    Route::get('/admin/marking-scheme', 'AdminMarkingSchemeController@index')->name('admin.marking-scheme.index');
    Route::get('/admin/marking-scheme/class/{class_id}', 'AdminMarkingSchemeController@classAssessments')->name('admin.marking-scheme.assessments');
    Route::get('/admin/marking-scheme/assessment/{assessment_id}', 'AdminMarkingSchemeController@assessmentMarks')->name('admin.marking-scheme.marks');
    Route::get('/api/admin/assessment/{assessment_id}/marks', 'AdminMarkingSchemeController@getAssessmentMarks')->name('admin.marking-scheme.api.marks');

    // Audit Trail Routes
    Route::get('/admin/audit-trail', 'AuditTrailController@index')->name('admin.audit-trail.index');
    Route::get('/admin/audit-trail/export', 'AuditTrailController@export')->name('admin.audit-trail.export');
    Route::get('/admin/audit-trail/{id}', 'AuditTrailController@show')->name('admin.audit-trail.show');
    Route::post('/admin/audit-trail/clear', 'AuditTrailController@clear')->name('admin.audit-trail.clear');

    // Student Class Upgrade Routes
    Route::get('/admin/student-upgrade', 'StudentUpgradeController@index')->name('admin.student-upgrade.index');
    Route::get('/admin/student-upgrade/preview', 'StudentUpgradeController@preview')->name('admin.student-upgrade.preview');
    Route::post('/admin/student-upgrade/execute', 'StudentUpgradeController@execute')->name('admin.student-upgrade.execute');
    Route::post('/admin/student-upgrade/rollback', 'StudentUpgradeController@rollback')->name('admin.student-upgrade.rollback');

    // School Settings Routes
    Route::get('/admin/settings/class-formats', 'SchoolSettingsController@classFormats')->name('admin.settings.class-formats');
    Route::post('/admin/settings/class-formats', 'SchoolSettingsController@storeClassFormat')->name('admin.settings.class-formats.store');
    Route::post('/admin/settings/class-formats/bulk', 'SchoolSettingsController@bulkStoreClassFormats')->name('admin.settings.class-formats.bulk-store');
    Route::put('/admin/settings/class-formats/{id}', 'SchoolSettingsController@updateClassFormat')->name('admin.settings.class-formats.update');
    Route::delete('/admin/settings/class-formats/{id}', 'SchoolSettingsController@deleteClassFormat')->name('admin.settings.class-formats.delete');
    Route::get('/admin/settings/upgrade-direction', 'SchoolSettingsController@upgradeDirection')->name('admin.settings.upgrade-direction');
    Route::put('/admin/settings/upgrade-direction', 'SchoolSettingsController@updateUpgradeDirection')->name('admin.settings.upgrade-direction.update');

    // School Geolocation Settings Routes
    Route::get('/admin/settings/geolocation', 'SchoolGeolocationController@index')->name('admin.settings.geolocation');
    Route::post('/admin/settings/geolocation', 'SchoolGeolocationController@store')->name('admin.settings.geolocation.store');
    Route::put('/admin/settings/geolocation/{id}', 'SchoolGeolocationController@update')->name('admin.settings.geolocation.update');
    Route::delete('/admin/settings/geolocation/{id}', 'SchoolGeolocationController@destroy')->name('admin.settings.geolocation.destroy');
    Route::post('/admin/settings/geolocation/{id}/set-active', 'SchoolGeolocationController@setActive')->name('admin.settings.geolocation.set-active');
    Route::get('/api/school-geolocation', 'SchoolGeolocationController@getActive')->name('api.school-geolocation');
    Route::post('/api/school-geolocation/check-point', 'SchoolGeolocationController@checkPoint')->name('api.school-geolocation.check-point');

    // SMS Settings Routes
    Route::get('/admin/settings/sms', 'SmsSettingsController@index')->name('admin.settings.sms');
    Route::put('/admin/settings/sms', 'SmsSettingsController@update')->name('admin.settings.sms.update');
    Route::post('/admin/settings/sms/preview', 'SmsSettingsController@preview')->name('admin.settings.sms.preview');
    Route::post('/admin/settings/sms/reset-count', 'SmsSettingsController@resetCount')->name('admin.settings.sms.reset-count');

    // Paynow Settings Routes
    Route::get('/admin/settings/paynow', 'PaynowSettingsController@index')->name('admin.settings.paynow');
    Route::post('/admin/settings/paynow', 'PaynowSettingsController@store')->name('admin.settings.paynow.store');
    Route::post('/admin/settings/paynow/test', 'PaynowSettingsController@test')->name('admin.settings.paynow.test');

    // Receipt Settings Routes
    Route::get('/admin/settings/receipt', 'SchoolSettingsController@receiptSettings')->name('admin.settings.receipt');
    Route::put('/admin/settings/receipt', 'SchoolSettingsController@updateReceiptSettings')->name('admin.settings.receipt.update');

    // Theme Settings Routes
    Route::get('/admin/settings/theme', 'SchoolSettingsController@themeSettings')->name('admin.settings.theme');
    Route::put('/admin/settings/theme', 'SchoolSettingsController@updateThemeSettings')->name('admin.settings.theme.update');

    // Website Settings Routes
    Route::get('/admin/website', 'WebsiteSettingController@index')->name('admin.website.index');
    Route::get('/admin/website/general', 'WebsiteSettingController@general')->name('admin.website.general');
    Route::get('/admin/website/colors', 'WebsiteSettingController@colors')->name('admin.website.colors');
    Route::get('/admin/website/images', 'WebsiteSettingController@images')->name('admin.website.images');
    Route::get('/admin/website/text', 'WebsiteSettingController@text')->name('admin.website.text');
    Route::get('/admin/website/pages', 'WebsiteSettingController@pages')->name('admin.website.pages');
    Route::get('/admin/website/homepage', 'WebsiteSettingController@homepage')->name('admin.website.homepage');
    Route::get('/admin/website/landing-page', 'WebsiteSettingController@landingPage')->name('admin.website.landing-page');
    Route::put('/admin/website/landing-page', 'WebsiteSettingController@updateLandingPage')->name('admin.website.landing-page.update');
    Route::put('/admin/website/update', 'WebsiteSettingController@update')->name('admin.website.update');
    Route::get('/admin/website/banners', 'WebsiteSettingController@banners')->name('admin.website.banners');
    Route::put('/admin/website/banners', 'WebsiteSettingController@updateBanners')->name('admin.website.banners.update');

    // Student Achievements Routes
    Route::get('/admin/achievements', 'StudentAchievementController@index')->name('admin.achievements.index');
    Route::get('/admin/achievements/create', 'StudentAchievementController@create')->name('admin.achievements.create');
    Route::post('/admin/achievements', 'StudentAchievementController@store')->name('admin.achievements.store');
    Route::get('/admin/achievements/{achievement}/edit', 'StudentAchievementController@edit')->name('admin.achievements.edit');
    Route::put('/admin/achievements/{achievement}', 'StudentAchievementController@update')->name('admin.achievements.update');
    Route::delete('/admin/achievements/{achievement}', 'StudentAchievementController@destroy')->name('admin.achievements.destroy');
    Route::post('/admin/achievements/{achievement}/toggle', 'StudentAchievementController@toggleStatus')->name('admin.achievements.toggle');

    // Student Analytics Routes
    Route::get('/admin/student-analytics', 'StudentAnalyticsController@index')->name('admin.student-analytics.index');
    Route::get('/api/student-analytics/students', 'StudentAnalyticsController@getStudentsByClass')->name('api.student-analytics.students');
    Route::get('/api/student-analytics/data', 'StudentAnalyticsController@getStudentAnalyticsApi')->name('api.student-analytics.data');

});

// Routes accessible by Admin OR users with specific permissions (for custom roles)
Route::group(['middleware' => ['auth']], function () {
    
    // Finance & Accounting Routes - accessible with Admin role OR sidebar-student-payments permission
    Route::middleware(['role_or_permission:Admin|sidebar-student-payments|sidebar-finance'])->group(function () {
        Route::get('/finance/student-payments', 'FinanceController@studentPayments')->name('finance.student-payments');
        Route::post('/finance/payments/store', 'FinanceController@storePayment')->name('finance.payments.store');
        Route::post('/finance/payments/paynow', 'FinanceController@paynowPayment')->name('finance.payments.paynow');
        Route::post('/finance/enforce-fees', 'FinanceController@enforceFees')->name('finance.enforce-fees');
        Route::get('/finance/student-payments/export', 'FinanceController@exportStudentPayments')->name('finance.student-payments.export');
        Route::get('/finance/student-fee-payments', 'FinanceController@getStudentFeePayments')->name('finance.student-fee-payments');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-parents-arrears|sidebar-finance'])->group(function () {
        Route::get('/finance/parents-arrears', 'FinanceController@parentsArrears')->name('finance.parents-arrears');
        Route::get('/finance/parents-arrears/export', 'FinanceController@exportParentsArrears')->name('finance.parents-arrears.export');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-school-income|sidebar-finance'])->group(function () {
        Route::get('/finance/school-income', 'FinanceController@schoolIncome')->name('finance.school-income');
        Route::post('/finance/school-income', 'FinanceController@storeIncome')->name('finance.income.store');
        Route::delete('/finance/school-income/{id}', 'FinanceController@destroyIncome')->name('finance.income.destroy');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-school-expenses|sidebar-finance'])->group(function () {
        Route::get('/finance/school-expenses', 'FinanceController@schoolExpenses')->name('finance.school-expenses');
        Route::post('/finance/school-expenses', 'FinanceController@storeExpense')->name('finance.expense.store');
        Route::delete('/finance/school-expenses/{id}', 'FinanceController@destroyExpense')->name('finance.expense.destroy');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-financial-statements|sidebar-finance'])->group(function () {
        Route::get('/finance/statements', 'FinanceController@financialStatements')->name('finance.statements');
        Route::get('/finance/accounting-guide', 'FinanceController@accountingGuide')->name('finance.accounting-guide');
        
        // Financial Reports
        Route::get('/finance/reports/trial-balance', 'FinancialReportsController@trialBalance')->name('finance.reports.trial-balance');
        Route::get('/finance/reports/profit-loss', 'FinancialReportsController@profitAndLoss')->name('finance.reports.profit-loss');
        Route::get('/finance/reports/balance-sheet', 'FinancialReportsController@balanceSheet')->name('finance.reports.balance-sheet');
        Route::get('/finance/reports/general-ledger', 'FinancialReportsController@generalLedger')->name('finance.reports.general-ledger');
    });

    // Asset Management Routes - Finance & Accounting
    Route::middleware(['role_or_permission:Admin|sidebar-assets|sidebar-finance|assets-view'])->group(function () {
        Route::get('/finance/assets', 'AssetController@index')->name('finance.assets.index');
        Route::get('/finance/assets/create', 'AssetController@create')->name('finance.assets.create');
        Route::post('/finance/assets', 'AssetController@store')->name('finance.assets.store');
        Route::get('/finance/assets/categories', 'AssetController@categories')->name('finance.assets.categories');
        Route::get('/finance/assets/categories/create', 'AssetController@createCategory')->name('finance.assets.categories.create');
        Route::post('/finance/assets/categories', 'AssetController@storeCategory')->name('finance.assets.categories.store');
        Route::get('/finance/assets/categories/{category}/edit', 'AssetController@editCategory')->name('finance.assets.categories.edit');
        Route::put('/finance/assets/categories/{category}', 'AssetController@updateCategory')->name('finance.assets.categories.update');
        Route::get('/finance/assets/locations', 'AssetController@locations')->name('finance.assets.locations');
        Route::get('/finance/assets/locations/create', 'AssetController@createLocation')->name('finance.assets.locations.create');
        Route::post('/finance/assets/locations', 'AssetController@storeLocation')->name('finance.assets.locations.store');
        Route::get('/finance/assets/locations/{location}/edit', 'AssetController@editLocation')->name('finance.assets.locations.edit');
        Route::put('/finance/assets/locations/{location}', 'AssetController@updateLocation')->name('finance.assets.locations.update');
        Route::get('/finance/assets/maintenance', 'AssetController@maintenance')->name('finance.assets.maintenance');
        Route::get('/finance/assets/depreciation', 'AssetController@depreciation')->name('finance.assets.depreciation');
        Route::post('/finance/assets/depreciation/run', 'AssetController@runDepreciation')->name('finance.assets.depreciation.run');
        Route::post('/finance/assets/depreciation/{depreciation}/post', 'AssetController@postDepreciation')->name('finance.assets.depreciation.post');
        Route::post('/finance/assets/depreciation/post-all', 'AssetController@postAllDepreciation')->name('finance.assets.depreciation.post-all');
        Route::get('/finance/assets/reports', 'AssetController@reports')->name('finance.assets.reports');
        Route::get('/finance/assets/reports/register', 'AssetController@assetRegister')->name('finance.assets.reports.register');
        Route::get('/finance/assets/reports/depreciation-schedule', 'AssetController@depreciationScheduleReport')->name('finance.assets.reports.depreciation-schedule');
        Route::get('/finance/assets/reports/maintenance-cost', 'AssetController@maintenanceCostReport')->name('finance.assets.reports.maintenance-cost');
        Route::get('/finance/assets/reports/disposed', 'AssetController@disposedAssetsReport')->name('finance.assets.reports.disposed');
        Route::get('/finance/assets/reports/by-location', 'AssetController@assetsByLocation')->name('finance.assets.reports.by-location');
        Route::get('/finance/assets/{asset}', 'AssetController@show')->name('finance.assets.show');
        Route::get('/finance/assets/{asset}/edit', 'AssetController@edit')->name('finance.assets.edit');
        Route::put('/finance/assets/{asset}', 'AssetController@update')->name('finance.assets.update');
        Route::delete('/finance/assets/{asset}', 'AssetController@destroy')->name('finance.assets.destroy');
        Route::get('/finance/assets/{asset}/assign', 'AssetController@showAssignForm')->name('finance.assets.assign.form');
        Route::post('/finance/assets/{asset}/assign', 'AssetController@assign')->name('finance.assets.assign');
        Route::post('/finance/assets/{asset}/unassign', 'AssetController@unassign')->name('finance.assets.unassign');
        Route::get('/finance/assets/{asset}/dispose', 'AssetController@showDisposeForm')->name('finance.assets.dispose.form');
        Route::post('/finance/assets/{asset}/dispose', 'AssetController@dispose')->name('finance.assets.dispose');
        Route::get('/finance/assets/{asset}/maintenance/create', 'AssetController@createMaintenance')->name('finance.assets.maintenance.create');
        Route::post('/finance/assets/{asset}/maintenance', 'AssetController@storeMaintenance')->name('finance.assets.maintenance.store');
        Route::post('/finance/assets/maintenance/{maintenance}/complete', 'AssetController@completeMaintenance')->name('finance.assets.maintenance.complete');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-grocery-arrears|sidebar-finance'])->group(function () {
        Route::get('/finance/grocery-arrears', 'GroceryController@groceryArrears')->name('finance.grocery-arrears');
        Route::get('/finance/grocery-arrears/export', 'GroceryController@exportGroceryArrears')->name('finance.grocery-arrears.export');
    });

    // General Journal Routes
    Route::middleware(['role_or_permission:Admin|sidebar-journals|sidebar-finance'])->group(function () {
        Route::get('/finance/journals', 'JournalController@index')->name('finance.journals.index');
        Route::get('/finance/journals/create', 'JournalController@create')->name('finance.journals.create');
        Route::post('/finance/journals', 'JournalController@store')->name('finance.journals.store');
        Route::get('/finance/journals/{id}', 'JournalController@show')->name('finance.journals.show');
        Route::get('/finance/journals/{id}/edit', 'JournalController@edit')->name('finance.journals.edit');
        Route::put('/finance/journals/{id}', 'JournalController@update')->name('finance.journals.update');
        Route::delete('/finance/journals/{id}', 'JournalController@destroy')->name('finance.journals.destroy');
        Route::post('/finance/journals/{id}/approve', 'JournalController@approve')->name('finance.journals.approve');
        Route::post('/finance/journals/{id}/post', 'JournalController@post')->name('finance.journals.post');
    });

    // Accounts Receivable Routes
    Route::middleware(['role_or_permission:Admin|sidebar-receivables|sidebar-finance'])->group(function () {
        Route::get('/finance/receivables', 'AccountsReceivableController@index')->name('finance.receivables.index');
        Route::get('/finance/receivables/invoices', 'AccountsReceivableController@invoices')->name('finance.receivables.invoices');
        Route::get('/finance/receivables/invoices/create', 'AccountsReceivableController@createInvoice')->name('finance.receivables.invoices.create');
        Route::post('/finance/receivables/invoices', 'AccountsReceivableController@storeInvoice')->name('finance.receivables.invoices.store');
        Route::get('/finance/receivables/invoices/{id}', 'AccountsReceivableController@showInvoice')->name('finance.receivables.invoices.show');
        Route::get('/finance/receivables/aging', 'AccountsReceivableController@aging')->name('finance.receivables.aging');
        Route::get('/finance/receivables/student/{id}/statement', 'AccountsReceivableController@studentStatement')->name('finance.receivables.statement');
    });

    // Accounts Payable Routes
    Route::middleware(['role_or_permission:Admin|sidebar-payables|sidebar-finance'])->group(function () {
        Route::get('/finance/payables', 'AccountsPayableController@index')->name('finance.payables.index');
        Route::get('/finance/payables/invoices', 'AccountsPayableController@invoices')->name('finance.payables.invoices');
        Route::get('/finance/payables/invoices/create', 'AccountsPayableController@createInvoice')->name('finance.payables.invoices.create');
        Route::post('/finance/payables/invoices', 'AccountsPayableController@storeInvoice')->name('finance.payables.invoices.store');
        Route::get('/finance/payables/invoices/{id}', 'AccountsPayableController@showInvoice')->name('finance.payables.invoices.show');
        Route::get('/finance/payables/invoices/{id}/pay', 'AccountsPayableController@showPaymentForm')->name('finance.payables.invoices.pay');
        Route::post('/finance/payables/invoices/{id}/pay', 'AccountsPayableController@recordPayment')->name('finance.payables.invoices.payment');
        Route::get('/finance/payables/aging', 'AccountsPayableController@aging')->name('finance.payables.aging');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-grocery-stock|sidebar-finance'])->group(function () {
        Route::get('/admin/grocery-stock', 'GroceryStockController@index')->name('admin.grocery-stock.index');
        Route::get('/admin/grocery-stock/items', 'GroceryStockController@items')->name('admin.grocery-stock.items');
        Route::post('/admin/grocery-stock/items', 'GroceryStockController@storeItem')->name('admin.grocery-stock.store-item');
        Route::put('/admin/grocery-stock/items/{id}', 'GroceryStockController@updateItem')->name('admin.grocery-stock.update-item');
        Route::get('/admin/grocery-stock/transactions', 'GroceryStockController@transactions')->name('admin.grocery-stock.transactions');
        Route::post('/admin/grocery-stock/transactions', 'GroceryStockController@storeTransaction')->name('admin.grocery-stock.store-transaction');
        Route::get('/admin/grocery-stock/record-usage', 'GroceryStockController@recordUsage')->name('admin.grocery-stock.record-usage');
        Route::post('/admin/grocery-stock/record-usage', 'GroceryStockController@storeUsage')->name('admin.grocery-stock.store-usage');
        Route::get('/admin/grocery-stock/record-bad-stock', 'GroceryStockController@recordBadStock')->name('admin.grocery-stock.record-bad-stock');
        Route::post('/admin/grocery-stock/record-bad-stock', 'GroceryStockController@storeBadStock')->name('admin.grocery-stock.store-bad-stock');
        Route::post('/admin/grocery-stock/carry-forward', 'GroceryStockController@carryForward')->name('admin.grocery-stock.carry-forward');
        Route::get('/admin/grocery-stock/print', 'GroceryStockController@print')->name('admin.grocery-stock.print');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-inventory|sidebar-finance'])->group(function () {
        Route::get('/finance/products-legacy', 'FinanceController@products')->name('finance.products.legacy');
        Route::post('/finance/products-legacy', 'FinanceController@storeProduct')->name('finance.product.store.legacy');
        Route::delete('/finance/products-legacy/{id}', 'FinanceController@destroyProduct')->name('finance.product.destroy.legacy');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-student-groceries|sidebar-finance'])->group(function () {
        Route::get('/admin/groceries', 'GroceryController@index')->name('admin.groceries.index');
        Route::post('/admin/groceries', 'GroceryController@store')->name('admin.groceries.store');
        Route::get('/admin/groceries/class/{classId}', 'GroceryController@showClass')->name('admin.groceries.class');
        Route::get('/admin/groceries/response/{responseId}', 'GroceryController@viewResponse')->name('admin.groceries.response');
        Route::put('/admin/groceries/{responseId}/acknowledge', 'GroceryController@acknowledge')->name('admin.groceries.acknowledge');
        Route::post('/admin/groceries/update-student', 'GroceryController@updateStudentGrocery')->name('admin.groceries.update-student');
        Route::put('/admin/groceries/{id}/close', 'GroceryController@close')->name('admin.groceries.close');
        Route::get('/admin/groceries/{id}/edit', 'GroceryController@edit')->name('admin.groceries.edit');
        Route::put('/admin/groceries/{id}', 'GroceryController@update')->name('admin.groceries.update');
        Route::put('/admin/groceries/{id}/lock', 'GroceryController@lock')->name('admin.groceries.lock');
        Route::get('/admin/groceries/student/{studentId}/history', 'GroceryController@studentHistory')->name('admin.groceries.student-history');
        Route::delete('/admin/groceries/{id}', 'GroceryController@destroy')->name('admin.groceries.destroy');
        Route::get('/admin/grocery-block-settings', 'GroceryController@blockSettings')->name('admin.grocery-block-settings');
        Route::post('/admin/grocery-block-settings', 'GroceryController@updateBlockSettings')->name('admin.grocery-block-settings.update');
        Route::post('/admin/grocery-exempt/{studentId}', 'GroceryController@toggleExemption')->name('admin.grocery-exempt');
        Route::get('/admin/groceries/student/{studentId}/print', 'GroceryController@printStudentHistory')->name('admin.groceries.student-history.print');
    });

    Route::middleware(['role_or_permission:Admin|sidebar-library-records'])->group(function () {
        Route::get('/admin/library', 'LibraryController@index')->name('admin.library.index');
        Route::get('/admin/library/create', 'LibraryController@create')->name('admin.library.create');
        Route::post('/admin/library', 'LibraryController@store')->name('admin.library.store');
        Route::get('/admin/library/search-students', 'LibraryController@searchStudents')->name('admin.library.search-students');
        Route::get('/admin/library/search-teachers', 'LibraryController@searchTeachers')->name('admin.library.search-teachers');
        Route::get('/admin/library/search-books', 'LibraryController@searchBooks')->name('admin.library.search-books');
        Route::get('/admin/library/student/{studentId}/history', 'LibraryController@studentHistory')->name('admin.library.student-history');
        Route::get('/admin/library/teacher/{teacherId}/history', 'LibraryController@teacherHistory')->name('admin.library.teacher-history');
        Route::patch('/admin/library/{id}/return', 'LibraryController@returnBook')->name('admin.library.return');
        Route::delete('/admin/library/{id}', 'LibraryController@destroy')->name('admin.library.destroy');
        Route::get('/admin/library/books', 'LibraryController@books')->name('admin.library.books');
        Route::get('/admin/library/books/create', 'LibraryController@createBook')->name('admin.library.books.create');
        Route::post('/admin/library/books', 'LibraryController@storeBook')->name('admin.library.books.store');
        Route::get('/admin/library/books/{id}/edit', 'LibraryController@editBook')->name('admin.library.books.edit');
        Route::put('/admin/library/books/{id}', 'LibraryController@updateBook')->name('admin.library.books.update');
        Route::get('/admin/library/books/{id}/history', 'LibraryController@bookHistory')->name('admin.library.books.history');
        Route::delete('/admin/library/books/{id}', 'LibraryController@destroyBook')->name('admin.library.books.destroy');
        Route::get('/admin/library/books/{id}/copies', 'LibraryController@bookCopies')->name('admin.library.books.copies');
        Route::get('/admin/library/books/{id}/copies/add', 'LibraryController@createBookCopies')->name('admin.library.books.copies.create');
        Route::post('/admin/library/books/{id}/copies', 'LibraryController@storeBookCopies')->name('admin.library.books.copies.store');
        Route::get('/admin/library/search-copies', 'LibraryController@searchBookCopies')->name('admin.library.search-copies');
    });
});

Route::group(['middleware' => ['auth','role:Teacher']], function ()
{
    // Teacher Attendance History (view only - marking done via QR scanner)
    Route::get('/teacher/my-attendance', 'TeacherController@myAttendance')->name('teacher.my-attendance');
    Route::post('/teacher/self-checkout', 'TeacherController@selfCheckout')->name('teacher.self-checkout');

    // Teacher Device Registration Routes
    Route::post('/teacher/device/register', 'TeacherDeviceController@registerDevice')->name('teacher.device.register');
    Route::get('/teacher/device/status', 'TeacherDeviceController@getDeviceStatus')->name('teacher.device.status');

    // Teacher Library - View borrowed books
    Route::get('/teacher/library', 'LibraryController@myTeacherLibrary')->name('teacher.library');

    // Teacher Leave Application Routes
    Route::get('/teacher/leave', 'TeacherLeaveController@index')->name('teacher.leave.index');
    Route::get('/teacher/leave/create', 'TeacherLeaveController@create')->name('teacher.leave.create');
    Route::post('/teacher/leave', 'TeacherLeaveController@store')->name('teacher.leave.store');
    Route::get('/teacher/leave/{id}', 'TeacherLeaveController@show')->name('teacher.leave.show');
    Route::delete('/teacher/leave/{id}', 'TeacherLeaveController@destroy')->name('teacher.leave.destroy');

    // Teacher Password Change Routes (must be first for middleware to work)
    Route::get('/teacher/change-password', 'TeacherController@showChangePasswordForm')->name('teacher.change-password');
    Route::post('/teacher/update-password', 'TeacherController@updatePassword')->name('teacher.update-password');

    // Student Record Routes - must be defined early to avoid conflicts
    Route::get('/teacher/student-record', 'TeacherController@studentRecord')->name('teacher.studentrecord');
    Route::get('/teacher/student-record/{class_id}', 'TeacherController@classStudents')->name('teacher.class.students');
    Route::post('/teacher/student/{student_id}/transfer', 'TeacherController@transferStudent')->name('teacher.student.transfer');
    
    // Teacher Student Analytics Route
    Route::get('/teacher/student-analytics', 'StudentAnalyticsController@teacherIndex')->name('teacher.student-analytics');

    // Class Teacher Routes (for teachers marked as class teachers)
    Route::get('/teacher/my-class-students', 'TeacherController@myClassStudents')->name('teacher.class-students');
    Route::get('/teacher/class-attendance', 'TeacherController@classAttendance')->name('teacher.class-attendance');
    Route::post('/teacher/class-attendance/store', 'TeacherController@storeClassAttendance')->name('teacher.class-attendance.store');
    

    // Disciplinary Records Routes
    Route::get('/teacher/disciplinary-records', 'DisciplinaryController@index')->name('teacher.disciplinary.index');
    Route::post('/teacher/disciplinary-records', 'DisciplinaryController@store')->name('teacher.disciplinary.store');
    Route::get('/teacher/disciplinary-records/class/{class_id}/students', 'DisciplinaryController@getStudentsByClass')->name('teacher.disciplinary.students');
    Route::put('/teacher/disciplinary-records/{id}', 'DisciplinaryController@update')->name('teacher.disciplinary.update');
    Route::delete('/teacher/disciplinary-records/{id}', 'DisciplinaryController@destroy')->name('teacher.disciplinary.destroy');

    Route::post('attendance', 'AttendanceController@store')->name('teacher.attendance.store');
    Route::get('attendance-create/{classid}', 'AttendanceController@createByTeacher')->name('teacher.attendance.create');

    // Subject routes for teachers
    Route::get('subject', 'SubjectController@index')->name('subject.index');
    Route::get('readingmaterails/{id}','AddsubjectController@show')->name('subject.Reading');

    Route::resource('readings', 'AddsubjectController');
    Route::post('subjects/{id}/upload','AddsubjectController@upload')->name('subject.upload');

    // Student Assessment
    Route::get('/teacher/assessment', 'TeacherController@assessment')->name('teacher.assessment');
    Route::get('/teacher/assessment/class/{class_id}', 'TeacherController@assessmentList')->name('teacher.assessment.list');
    Route::get('/teacher/assessment/create/{class_id}', 'TeacherController@createAssessment')->name('teacher.assessment.create');
    Route::post('/teacher/assessment/store', 'TeacherController@storeAssessment')->name('teacher.assessment.store');
    Route::get('/teacher/assessment/{id}/view', 'TeacherController@viewAssessment')->name('teacher.assessment.view');
    Route::get('/teacher/assessment/{id}/edit', 'TeacherController@editAssessment')->name('teacher.assessment.edit');
    Route::put('/teacher/assessment/{id}/update', 'TeacherController@updateAssessment')->name('teacher.assessment.update');
    Route::delete('/teacher/assessment/{id}/delete', 'TeacherController@deleteAssessment')->name('teacher.assessment.delete');
    Route::get('/teacher/assessment/marks/{class_id}', 'TeacherController@assessmentMarks')->name('teacher.assessment.marks');
    Route::post('/teacher/assessment/marks/save', 'TeacherController@saveAssessmentMark')->name('teacher.assessment.marks.save');
    Route::get('/teacher/assessment/marking-scheme/{class_id}', 'TeacherController@markingScheme')->name('teacher.assessment.marking.scheme');
    Route::get('/teacher/assessment/marking-scheme/{class_id}/export', 'TeacherController@exportMarkingScheme')->name('teacher.assessment.marking.export');
    Route::get('/api/assessment/{id}/marks', 'TeacherController@getAssessmentMarks');
    Route::post('/teacher/assessment/comment/store', 'TeacherController@storeAssessmentComment')->name('teacher.assessment.comment.store');
    Route::delete('/teacher/assessment/comment/{id}', 'TeacherController@deleteAssessmentComment')->name('teacher.assessment.comment.delete');

    // Teacher Syllabus Topics Management Routes
    Route::get('/teacher/syllabus', 'TeacherSyllabusController@index')->name('teacher.syllabus.index');
    Route::get('/teacher/syllabus/create', 'TeacherSyllabusController@create')->name('teacher.syllabus.create');
    Route::post('/teacher/syllabus/import', 'TeacherSyllabusController@importFromPdf')->name('teacher.syllabus.import');
    Route::post('/teacher/syllabus/import-cambridge', 'TeacherSyllabusController@importCambridgeSyllabus')->name('teacher.syllabus.import-cambridge');
    Route::post('/teacher/syllabus/import-zimsec', 'TeacherSyllabusController@importZimsecSyllabus')->name('teacher.syllabus.import-zimsec');
    Route::post('/teacher/syllabus/bulk-delete', 'TeacherSyllabusController@bulkDelete')->name('teacher.syllabus.bulk-delete');
    Route::post('/teacher/syllabus', 'TeacherSyllabusController@store')->name('teacher.syllabus.store');
    Route::get('/teacher/syllabus/{id}/edit', 'TeacherSyllabusController@edit')->name('teacher.syllabus.edit');
    Route::put('/teacher/syllabus/{id}', 'TeacherSyllabusController@update')->name('teacher.syllabus.update');
    Route::delete('/teacher/syllabus/{id}', 'TeacherSyllabusController@destroy')->name('teacher.syllabus.destroy');

    // Data-Driven Schemes of Work Routes
    Route::get('/teacher/schemes', 'SchemeController@index')->name('teacher.schemes.index');
    Route::get('/teacher/schemes/create', 'SchemeController@create')->name('teacher.schemes.create');
    Route::get('/teacher/schemes/syllabus-topics', 'SchemeController@getSyllabusTopics')->name('teacher.schemes.syllabus-topics');
    Route::get('/teacher/schemes/auto-generate', 'SchemeController@autoGenerateFromAssessments')->name('teacher.schemes.auto-generate');
    Route::get('/teacher/schemes/student-assessments', 'SchemeController@getStudentAssessments')->name('teacher.schemes.student-assessments');
    Route::get('/teacher/schemes/{id}/print', 'SchemeController@print')->name('teacher.schemes.print');
    Route::post('/teacher/schemes', 'SchemeController@store')->name('teacher.schemes.store');
    Route::post('/teacher/schemes/topic/{topicId}/status', 'SchemeController@updateTopicStatus')->name('teacher.schemes.topic.status');
    Route::post('/teacher/schemes/topic/{topicId}/remedial', 'SchemeController@createRemedial')->name('teacher.schemes.remedial.create');
    Route::post('/teacher/schemes/remedial/{remedialId}/complete', 'SchemeController@completeRemedial')->name('teacher.schemes.remedial.complete');
    Route::get('/teacher/schemes/{id}', 'SchemeController@show')->name('teacher.schemes.show');
    Route::get('/teacher/schemes/{id}/edit', 'SchemeController@edit')->name('teacher.schemes.edit');
    Route::put('/teacher/schemes/{id}', 'SchemeController@update')->name('teacher.schemes.update');
    Route::delete('/teacher/schemes/{id}', 'SchemeController@destroy')->name('teacher.schemes.destroy');
    Route::get('/teacher/schemes/{id}/evaluation-report', 'SchemeController@evaluationReport')->name('teacher.schemes.evaluation-report');

    Route::get('/results', 'ResultController@index')->name('results.index');
    Route::get('/viewresults', 'ResultController@recordindex')->name('results.record');
    Route::get('/selectyear', 'ResultController@viewresultsindex')->name('results.viewrecord');
    Route::get('/classnames', 'ResultController@viewresultsindex')->name('results.viewclassrecord');
    Route::post('postclassresultsview', 'ResultController@viewResults')->name('viewallresults.view');
    Route::get('/student/addresults/{student}', 'ResultController@Showssubject')->name('results.studentsubject');
    Route::post('/results/store', 'ResultController@store')->name('teacher.results.store');



    // Show the post and view student results

    Route::post('/showclassresults/year', 'ResultController@showResult')->name('results.classresults');
    Route::get('/classname/{class_id}','ResultController@Classnames')->name('results.classname');
    Route::get('/student/show/{student}', 'ResultController@Stuntentname')->name('results.yearsubject');
// Store the updated results status
    Route::get('/teacher/results/status/{status}', 'ResultController@viewstatus')->name('results.status');
   //studentresults
     Route::post('/results/update/{result}', 'ResultController@update')->name('results.update');
     Route::delete('/results/delete/{result}', 'ResultController@destroy')->name('results.delete');
     Route::get('/student/results/{student}', 'ResultController@showstudentresults')->name('student.results');
     Route::get('/teacher/results/create/{class_id}', 'ResultController@createByTeacher')->name('results.create');
     Route::get('/results/edit/{result}', 'ResultController@edit')->name('results.edit');
     Route::get('/results/student/{id}', 'ResultController@show')->name('results.show');
     Route::get('/teacher/results/{class_id}','ResultController@listResults')->name('results.results');
     Route::get('/teacher/timetable', 'TimetableController@teacherView')->name('teacher.timetable');

     // Push Notification Routes
     Route::post('/push/subscribe', 'PushNotificationController@subscribe')->name('push.subscribe');
     Route::post('/push/unsubscribe', 'PushNotificationController@unsubscribe')->name('push.unsubscribe');
     Route::get('/push/vapid-key', 'PushNotificationController@getVapidPublicKey')->name('push.vapid-key');

    // Online Exercises Routes (Teacher)
    Route::get('/teacher/exercises', 'ExerciseController@index')->name('exercises.index');
    Route::get('/teacher/exercises/create', 'ExerciseController@create')->name('exercises.create');
    Route::post('/teacher/exercises', 'ExerciseController@store')->name('exercises.store');
    Route::get('/teacher/exercises/{exercise}', 'ExerciseController@show')->name('exercises.show');
    Route::get('/teacher/exercises/{exercise}/edit', 'ExerciseController@edit')->name('exercises.edit');
    Route::put('/teacher/exercises/{exercise}', 'ExerciseController@update')->name('exercises.update');
    Route::delete('/teacher/exercises/{exercise}', 'ExerciseController@destroy')->name('exercises.destroy');
    Route::get('/teacher/exercises/{exercise}/questions', 'ExerciseController@editQuestions')->name('exercises.questions.edit');
    Route::post('/teacher/exercises/{exercise}/questions', 'ExerciseController@storeQuestion')->name('exercises.questions.store');
    Route::put('/teacher/exercises/{exercise}/questions/{question}', 'ExerciseController@updateQuestion')->name('exercises.questions.update');
    Route::delete('/teacher/exercises/{exercise}/questions/{question}', 'ExerciseController@destroyQuestion')->name('exercises.questions.destroy');
    Route::post('/teacher/exercises/{exercise}/toggle-publish', 'ExerciseController@togglePublish')->name('exercises.toggle-publish');
    Route::post('/teacher/exercises/{exercise}/toggle-results', 'ExerciseController@toggleResults')->name('exercises.toggle-results');
    Route::get('/teacher/exercises/{exercise}/submissions', 'ExerciseController@submissions')->name('exercises.submissions');
    Route::get('/teacher/exercises/{exercise}/submissions/{submission}/mark', 'ExerciseController@markSubmission')->name('exercises.submissions.mark');
    Route::post('/teacher/exercises/{exercise}/submissions/{submission}/save-marks', 'ExerciseController@saveMarks')->name('exercises.submissions.save-marks');
    Route::post('/teacher/exercises/{exercise}/auto-mark', 'ExerciseController@autoMark')->name('exercises.auto-mark');
    Route::get('/teacher/exercises/get-subjects/{classId}', 'ExerciseController@getSubjectsForClass')->name('exercises.get-subjects');

});

Route::group(['middleware' => ['auth','role:Parent']], function ()
{
    Route::get('studentattendance/{attendance}', 'AttendanceController@show')->name('attendance.show');
    Route::get('/viewstudentresults/viewstudentresults', 'ResultController@viewstudentshow')->name('parentviewresults.studentresults');
    Route::get('/studentviewresults/studentviewresults', 'AddsubjectController@studentviewsubject')->name('viewsubject.studentresults');
    
    // Parent Assessments Route
    Route::get('/parent/assessments', 'ResultController@parentAssessments')->name('parent.assessments');
    
    // Parent Student Analytics Route
    Route::get('/parent/student-analytics', 'StudentAnalyticsController@parentIndex')->name('parent.student-analytics');
    
    // Parent Groceries Routes
    Route::get('/parent/groceries', 'GroceryController@parentIndex')->name('parent.groceries.index');
    Route::post('/parent/groceries/submit', 'GroceryController@parentSubmit')->name('parent.groceries.submit');
    
    // Parent Medical Reports Routes
    Route::get('/parent/medical-reports', 'MedicalReportController@parentIndex')->name('parent.medical-reports.index');
    Route::get('/parent/medical-reports/create', 'MedicalReportController@parentCreate')->name('parent.medical-reports.create');
    Route::post('/parent/medical-reports', 'MedicalReportController@parentStore')->name('parent.medical-reports.store');
    Route::get('/parent/medical-reports/{id}', 'MedicalReportController@parentShow')->name('parent.medical-reports.show');
    
    // Parent Disciplinary Records Routes
    Route::get('/parent/disciplinary-records', 'DisciplinaryController@parentIndex')->name('parent.disciplinary.index');
    
    // Parent Payment Verification Routes
    Route::get('/parent/payment-verification', 'PaymentVerificationController@create')->name('parent.payment-verification.create');
    Route::post('/parent/payment-verification', 'PaymentVerificationController@store')->name('parent.payment-verification.store');
    
    // Parent Payment History Route
    Route::get('/parent/payment-history', 'FinanceController@parentPaymentHistory')->name('parent.payment-history');
});

Route::group(['middleware' => ['auth','role:Student']], function () {

    Route::get('/my-timetable', 'TimetableController@studentView')->name('student.timetable');
    Route::get('/studentviewresults/studentviewresults', 'AddsubjectController@studentviewsubject')->name('viewsubject.studentresults');
    Route::get('/studentattends/studentattends', 'AddsubjectController@studentattendance')->name('attendancy.studentattendance');
   Route::get('/studentresults/studentresults', 'ResultController@studentshow')->name('viewresults.studentresults');
   Route::get('Reading/{id}','AddsubjectController@showread')->name('subject.viewreading');
   Route::get('/readings/download/{id}', 'AddsubjectController@download')->name('readings.download');
   
   // Student Library - View borrowed books
   Route::get('/my-library', 'LibraryController@myLibrary')->name('student.library');

   // Student Online Exercises Routes
   Route::get('/my-exercises', 'StudentExerciseController@index')->name('student.exercises.index');
   Route::get('/my-exercises/{exercise}', 'StudentExerciseController@show')->name('student.exercises.show');
   Route::get('/my-exercises/{exercise}/attempt', 'StudentExerciseController@attempt')->name('student.exercises.attempt');
   Route::post('/my-exercises/{exercise}/save-answer', 'StudentExerciseController@saveAnswer')->name('student.exercises.save-answer');
   Route::post('/my-exercises/{exercise}/upload-file', 'StudentExerciseController@uploadFile')->name('student.exercises.upload-file');
   Route::post('/my-exercises/{exercise}/submit', 'StudentExerciseController@submit')->name('student.exercises.submit');
   Route::post('/my-exercises/{exercise}/save-exit', 'StudentExerciseController@saveAndExit')->name('student.exercises.save-exit');
   Route::get('/my-exercises/{exercise}/results', 'StudentExerciseController@results')->name('student.exercises.results');

});
