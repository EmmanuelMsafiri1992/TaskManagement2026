<?php

use Admin\Http\Controllers\Api\AvatarUpload;
use Admin\Http\Controllers\Api\Charts;
use Admin\Http\Controllers\Api\ChecklistItemComplete;
use Admin\Http\Controllers\Api\ChecklistItemsController;
use Admin\Http\Controllers\Api\FavoritesController;
use Admin\Http\Controllers\Api\FileUpload;
use Admin\Http\Controllers\Api\FiltersController;
use Admin\Http\Controllers\Api\InvitationsController;
use Admin\Http\Controllers\Api\LabelsController;
use Admin\Http\Controllers\Api\LogoUpload;
use Admin\Http\Controllers\Api\Metrics;
use Admin\Http\Controllers\Api\Notifications;
use Admin\Http\Controllers\Api\NotificationsRead;
use Admin\Http\Controllers\Api\ProfileController;
use Admin\Http\Controllers\Api\ProjectArchive;
use Admin\Http\Controllers\Api\ProjectDuplicate;
use Admin\Http\Controllers\Api\ProjectExportTimeLogs;
use Admin\Http\Controllers\Api\ProjectFavorite;
use Admin\Http\Controllers\Api\ProjectListSort;
use Admin\Http\Controllers\Api\ProjectListsController;
use Admin\Http\Controllers\Api\ProjectRestore;
use Admin\Http\Controllers\Api\ProjectTimeLogs;
use Admin\Http\Controllers\Api\ProjectTotalTime;
use Admin\Http\Controllers\Api\ProjectsController;
use Admin\Http\Controllers\Api\ProjectsOptions;
use Admin\Http\Controllers\Api\RecentProjects;
use Admin\Http\Controllers\Api\RolesController;
use Admin\Http\Controllers\Api\SettingsEmailController;
use Admin\Http\Controllers\Api\SettingsGeneralController;
use Admin\Http\Controllers\Api\SettingsAdSenseController;
use Admin\Http\Controllers\Api\AdSenseOAuthController;
use Admin\Http\Controllers\Api\SubTasksController;
use Admin\Http\Controllers\Api\TaskArchive;
use Admin\Http\Controllers\Api\TaskAssign;
use Admin\Http\Controllers\Api\TaskCommentsController;
use Admin\Http\Controllers\Api\TaskComplete;
use Admin\Http\Controllers\Api\TaskDueDate;
use Admin\Http\Controllers\Api\TaskLabel;
use Admin\Http\Controllers\Api\TaskList;
use Admin\Http\Controllers\Api\TaskMove;
use Admin\Http\Controllers\Api\TaskPriority;
use Admin\Http\Controllers\Api\TaskRecurring;
use Admin\Http\Controllers\Api\TaskRestore;
use Admin\Http\Controllers\Api\TaskSort;
use Admin\Http\Controllers\Api\TasksController;
use Admin\Http\Controllers\Api\TimeLogsController;
use Admin\Http\Controllers\Api\UsersController;
use Admin\Http\Controllers\Api\AdSenseReportController;
use Admin\Http\Controllers\Api\AttendanceController;
use Admin\Http\Controllers\Api\GeneratorFuelController;
use Admin\Http\Controllers\Api\EmployeeRecordsController;
use Admin\Http\Controllers\Api\ImpersonateController;
use Admin\Http\Controllers\Api\LeavesController;
use Admin\Http\Controllers\Api\HolidaysController;
use Admin\Http\Controllers\Api\PayrollsController;
use Admin\Http\Controllers\Api\ClientsController;
use Admin\Http\Controllers\Api\QuotationsController;
use Admin\Http\Controllers\Api\ExpensesController;
use Admin\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Admin\SettingsCountriesController;
use App\Http\Controllers\Admin\UserAssignmentController;
use App\Http\Controllers\Admin\JobShareController;
use App\Http\Controllers\Admin\VideoEnhancerController;
use App\Http\Controllers\UrlShortenerController;
use Admin\Http\Controllers\Api\AuditTrailController;
use Admin\Http\Controllers\Api\ServiceProviderController;
use Admin\Http\Controllers\Api\RecordingSessionController;
use Admin\Http\Controllers\Api\LessonPlanController;
use Admin\Http\Controllers\Api\SubjectController;
use Admin\Http\Controllers\Api\PaymentController;
use Admin\Http\Controllers\Api\UserWorkingHoursController;
use Admin\Http\Controllers\Api\UserActivityController;
use Admin\Http\Controllers\Api\LeadsController;
use Illuminate\Support\Facades\Route;

Route::get('{resource}/filters', FiltersController::class);
Route::get('metrics', Metrics::class);
Route::get('charts', Charts::class);
Route::get('notifications', Notifications::class);
Route::post('notifications/read', NotificationsRead::class);
Route::get('recent-projects', RecentProjects::class);
Route::post('logo', LogoUpload::class);
Route::post('avatar', AvatarUpload::class);
Route::post('file', FileUpload::class);
Route::resource('profile', ProfileController::class)->only(['create', 'store']);
Route::resource('time-logs', TimeLogsController::class)->only(['index', 'store']);

// Generator Fuel Routes
Route::prefix('generator')->group(function () {
    Route::get('status', [GeneratorFuelController::class, 'status']);
    Route::get('logs', [GeneratorFuelController::class, 'logs']);
    Route::get('statistics', [GeneratorFuelController::class, 'statistics']);
    Route::post('add-fuel', [GeneratorFuelController::class, 'addFuel']);
    Route::post('start', [GeneratorFuelController::class, 'start']);
    Route::post('stop', [GeneratorFuelController::class, 'stop']);
    Route::put('settings', [GeneratorFuelController::class, 'updateSettings']);
});

// Attendance Routes
Route::get('attendance/status', [AttendanceController::class, 'status']);
Route::get('attendance/report', [AttendanceController::class, 'report']);
Route::get('attendance/users', [AttendanceController::class, 'users']);
Route::get('attendance/export', [AttendanceController::class, 'export']);
Route::resource('attendance', AttendanceController::class);

// Employee Records Routes
Route::get('employees/statistics', [EmployeeRecordsController::class, 'statistics']);
Route::get('employees/available-users', [EmployeeRecordsController::class, 'availableUsers']);
Route::get('employees/supervisors', [EmployeeRecordsController::class, 'supervisors']);
Route::resource('employees', EmployeeRecordsController::class);

// Leave Management Routes
Route::get('leaves/statistics', [LeavesController::class, 'statistics']);
Route::post('leaves/{id}/approve', [LeavesController::class, 'approve']);
Route::post('leaves/{id}/reject', [LeavesController::class, 'reject']);
Route::resource('leaves', LeavesController::class);

// Holiday Routes
Route::get('holidays/upcoming', [HolidaysController::class, 'upcoming']);
Route::get('holidays/calendar', [HolidaysController::class, 'calendar']);
Route::resource('holidays', HolidaysController::class);

// Payroll Routes
Route::get('payrolls/statistics', [PayrollsController::class, 'statistics']);
Route::post('payrolls/generate', [PayrollsController::class, 'generateForMonth']);
Route::post('payrolls/{id}/approve', [PayrollsController::class, 'approve']);
Route::post('payrolls/{id}/mark-as-paid', [PayrollsController::class, 'markAsPaid']);
Route::post('payrolls/{id}/send-payslip', [PayrollsController::class, 'sendPayslip']);
Route::resource('payrolls', PayrollsController::class);

// Client Routes
Route::get('clients/statistics', [ClientsController::class, 'statistics']);
Route::resource('clients', ClientsController::class);

// Quotation Routes
Route::get('quotations/statistics', [QuotationsController::class, 'statistics']);
Route::get('quotations/{id}/pdf', [QuotationsController::class, 'pdf']);
Route::post('quotations/{id}/change-status', [QuotationsController::class, 'changeStatus']);
Route::resource('quotations', QuotationsController::class);

// Expense Routes
Route::get('expenses/statistics', [ExpensesController::class, 'statistics']);
Route::get('expenses/categories', [ExpensesController::class, 'categories']);
Route::post('expenses/{id}/approve', [ExpensesController::class, 'approve']);
Route::post('expenses/{id}/reject', [ExpensesController::class, 'reject']);
Route::resource('expenses', ExpensesController::class);

// Income Routes
Route::get('income/statistics', [IncomeController::class, 'statistics']);
Route::post('income/{id}/mark-as-received', [IncomeController::class, 'markAsReceived']);
Route::resource('income', IncomeController::class);

Route::resource('favorites', FavoritesController::class)->only(['index', 'store', 'destroy']);
Route::get('projects/options', ProjectsOptions::class);
Route::resource('projects', ProjectsController::class);
Route::resource('projects.lists', ProjectListsController::class)->shallow();
Route::patch('projects/{project}/favorite', ProjectFavorite::class);
Route::post('projects/{project}/duplicate', ProjectDuplicate::class);
Route::patch('projects/{project}/archive', ProjectArchive::class);
Route::patch('projects/{project}/restore', ProjectRestore::class);
Route::patch('projects/{project}/list-sort', ProjectListSort::class);
Route::get('projects/{project}/time-logs', ProjectTimeLogs::class);
Route::get('projects/{project}/total-time', ProjectTotalTime::class);
Route::get('projects/{project}/export-time-logs', ProjectExportTimeLogs::class);

Route::resource('tasks.comments', TaskCommentsController::class)->shallow()->only(['store', 'destroy']);
Route::resource('tasks', TasksController::class);
Route::patch('tasks/{task}/sort', TaskSort::class);
Route::patch('tasks/{task}/move', TaskMove::class);
Route::patch('tasks/{task}/list', TaskList::class);
Route::patch('tasks/{task}/priority', TaskPriority::class);
Route::patch('tasks/{task}/assign', TaskAssign::class);
Route::patch('tasks/{task}/label', TaskLabel::class);
Route::patch('tasks/{task}/complete', TaskComplete::class);
Route::patch('tasks/{task}/archive', TaskArchive::class);
Route::patch('tasks/{task}/restore', TaskRestore::class);
Route::patch('tasks/{task}/due-date', TaskDueDate::class);
Route::post('tasks/{task}/recurring', TaskRecurring::class);
Route::post('sub-tasks', [SubTasksController::class, 'store']);
Route::patch('sub-tasks/{task}', [SubTasksController::class, 'update']);
Route::delete('sub-tasks/{task}', [SubTasksController::class, 'destroy']);
Route::post('checklist-item', [ChecklistItemsController::class, 'store']);
Route::patch('checklist-item/{item}', [ChecklistItemsController::class, 'update']);
Route::delete('checklist-item/{item}', [ChecklistItemsController::class, 'destroy']);
Route::patch('checklist-item/{item}/complete', ChecklistItemComplete::class);

Route::resource('labels', LabelsController::class);
Route::resource('users', UsersController::class);
Route::resource('invitations', InvitationsController::class)->except(['show', 'edit', 'update']);
Route::resource('roles', RolesController::class);

Route::resource('settings/general', SettingsGeneralController::class)->only(['create', 'store']);
Route::resource('settings/email', SettingsEmailController::class)->only(['create', 'store']);
Route::resource('settings/adsense', SettingsAdSenseController::class)->only(['create', 'store']);

// Countries & Websites Management Routes
Route::prefix('settings/countries')->group(function () {
    Route::get('/', [SettingsCountriesController::class, 'index']);
    Route::put('users/{userId}/countries', [SettingsCountriesController::class, 'updateUserCountries']);
    Route::get('companies/{countryCode}', [SettingsCountriesController::class, 'getCompaniesByCountry']);
    Route::post('users/{userId}/countries/{userCountryId}/websites', [SettingsCountriesController::class, 'assignWebsitesToCountry']);
    Route::get('users/{userId}/targets', [SettingsCountriesController::class, 'getUserTargets']);
    Route::put('users/{userId}/targets', [SettingsCountriesController::class, 'updateUserTargets']);
});

// User Assignment Management Routes
Route::prefix('settings/user-assignments')->group(function () {
    Route::get('/', [UserAssignmentController::class, 'index']);
    Route::put('users/{userId}/focus', [UserAssignmentController::class, 'updateFocus']);
    Route::get('available-users', [UserAssignmentController::class, 'getAvailableUsers']);
    Route::get('users/{userId}/assignments', [UserAssignmentController::class, 'getUserAssignments']);
    Route::post('assign', [UserAssignmentController::class, 'assignUser']);
    Route::delete('assignments/{assignmentId}', [UserAssignmentController::class, 'unassignUser']);
    Route::put('assignments/{assignmentId}/notes', [UserAssignmentController::class, 'updateNotes']);
    Route::get('statistics', [UserAssignmentController::class, 'getStatistics']);
});

// AdSense Routes
Route::prefix('adsense')->group(function () {
    // OAuth Routes
    Route::get('auth/url', [AdSenseOAuthController::class, 'getAuthUrl']);
    Route::post('disconnect', [AdSenseOAuthController::class, 'disconnect']);

    // Reports Routes
    Route::get('reports', [AdSenseReportController::class, 'index']);
    Route::get('reports/by-country', [AdSenseReportController::class, 'byCountry']);
    Route::get('reports/by-website', [AdSenseReportController::class, 'byWebsite']);
    Route::get('reports/summary', [AdSenseReportController::class, 'summary']);
    Route::get('reports/latest-date', [AdSenseReportController::class, 'latestDate']);
    Route::get('reports/daily-trend', [AdSenseReportController::class, 'dailyTrend']);
    Route::get('user-assignments', [AdSenseReportController::class, 'userAssignments']);
    Route::post('sync', [AdSenseReportController::class, 'sync']);
    Route::get('test-connection', [AdSenseReportController::class, 'testConnection']);

    // Analytics Routes
    Route::get('traffic-sources', [AdSenseReportController::class, 'trafficSources']);
    Route::get('test-analytics-connection', [AdSenseReportController::class, 'testAnalyticsConnection']);
});

// Job Sharing Routes
Route::prefix('job-shares')->group(function () {
    Route::get('/', [JobShareController::class, 'index']);
    Route::get('statistics', [JobShareController::class, 'statistics']);
    Route::get('{id}', [JobShareController::class, 'show']);
    Route::post('{id}/mark-copied', [JobShareController::class, 'markAsCopied']);
    Route::post('trigger-assignment', [JobShareController::class, 'triggerAssignment']);
});

// URL Shortener Stats Route (API)
Route::get('url-shortener/{shortCode}/stats', [UrlShortenerController::class, 'stats']);

// Impersonation Routes
Route::prefix('impersonate')->group(function () {
    Route::post('stop', [ImpersonateController::class, 'stopImpersonating']);
    Route::get('status', [ImpersonateController::class, 'status']);
    Route::post('{userId}', [ImpersonateController::class, 'impersonate']);
});

// Video Enhancer Routes
Route::prefix('video-enhancer')->group(function () {
    Route::get('/', [VideoEnhancerController::class, 'index']);
    Route::post('upload', [VideoEnhancerController::class, 'upload']);
    Route::post('{id}/estimate', [VideoEnhancerController::class, 'estimate']);
    Route::post('{id}/process', [VideoEnhancerController::class, 'process']);
    Route::get('{id}/status', [VideoEnhancerController::class, 'status']);
    Route::get('{id}/download', [VideoEnhancerController::class, 'download']);
    Route::delete('{id}', [VideoEnhancerController::class, 'destroy']);
    Route::delete('{id}/processed', [VideoEnhancerController::class, 'deleteProcessed']);
});

// Audit Trail Routes
Route::prefix('audit-trails')->group(function () {
    Route::get('/', [AuditTrailController::class, 'index']);
    Route::get('statistics', [AuditTrailController::class, 'statistics']);
    Route::get('model-types', [AuditTrailController::class, 'modelTypes']);
    Route::get('{type}/{id}', [AuditTrailController::class, 'show']);
});

// Service Providers Routes
Route::prefix('service-providers')->group(function () {
    Route::get('statistics', [ServiceProviderController::class, 'statistics']);
    Route::post('stop-impersonating', [ServiceProviderController::class, 'stopImpersonating']);
    Route::get('{serviceProvider}/recording-sessions', [ServiceProviderController::class, 'recordingSessions']);
    Route::get('{serviceProvider}/lesson-plans', [ServiceProviderController::class, 'lessonPlans']);
    Route::get('{serviceProvider}/payments', [ServiceProviderController::class, 'payments']);
    Route::get('{serviceProvider}/payment-summary', [ServiceProviderController::class, 'paymentSummary']);
    Route::get('{serviceProvider}/download-agreement', [ServiceProviderController::class, 'downloadAgreement']);
    Route::post('{serviceProvider}/activate', [ServiceProviderController::class, 'activate']);
    Route::post('{serviceProvider}/suspend', [ServiceProviderController::class, 'suspend']);
    Route::post('{serviceProvider}/sign-agreement', [ServiceProviderController::class, 'signAgreement']);
    Route::post('{serviceProvider}/impersonate', [ServiceProviderController::class, 'impersonate']);
});
Route::resource('service-providers', ServiceProviderController::class);

// Payments Routes
Route::prefix('payments')->group(function () {
    Route::get('statistics', [PaymentController::class, 'statistics']);
    Route::post('bulk', [PaymentController::class, 'bulkPayment']);
    Route::get('{payment}/receipt', [PaymentController::class, 'generateReceipt']);
    Route::post('{payment}/complete', [PaymentController::class, 'markAsCompleted']);
});
Route::resource('payments', PaymentController::class);

// Recording Sessions Routes
Route::prefix('recording-sessions')->group(function () {
    Route::get('statistics', [RecordingSessionController::class, 'statistics']);
    Route::get('pending-review', [RecordingSessionController::class, 'pendingReview']);
    Route::post('{recordingSession}/approve', [RecordingSessionController::class, 'approve']);
    Route::post('{recordingSession}/reject', [RecordingSessionController::class, 'reject']);
    Route::post('{recordingSession}/upload-video', [RecordingSessionController::class, 'uploadVideo']);
});
Route::resource('recording-sessions', RecordingSessionController::class);

// Lesson Plans Routes
Route::prefix('lesson-plans')->group(function () {
    Route::get('statistics', [LessonPlanController::class, 'statistics']);
    Route::get('pending-review', [LessonPlanController::class, 'pendingReview']);
    Route::post('{lessonPlan}/approve', [LessonPlanController::class, 'approve']);
    Route::post('{lessonPlan}/reject', [LessonPlanController::class, 'reject']);
});
Route::resource('lesson-plans', LessonPlanController::class);

// Subjects & Topics Routes
Route::prefix('subjects')->group(function () {
    Route::get('statistics', [SubjectController::class, 'statistics']);
    Route::get('by-form', [SubjectController::class, 'byForm']);
    Route::get('{subject}/topics', [SubjectController::class, 'topics']);
    Route::post('{subject}/topics', [SubjectController::class, 'storeTopic']);
    Route::put('{subject}/topics/{topic}', [SubjectController::class, 'updateTopic']);
    Route::delete('{subject}/topics/{topic}', [SubjectController::class, 'destroyTopic']);
});
Route::resource('subjects', SubjectController::class);

// Working Hours Routes
Route::get('working-hours/statistics', [UserWorkingHoursController::class, 'statistics']);
Route::get('working-hours/users', [UserWorkingHoursController::class, 'users']);
Route::get('working-hours/my-hours', [UserWorkingHoursController::class, 'myWorkingHours']);
Route::get('working-hours/history/{userId}', [UserWorkingHoursController::class, 'history']);
Route::resource('working-hours', UserWorkingHoursController::class)->parameters([
    'working-hours' => 'workingHour'
]);

// Activity Tracking Routes
Route::prefix('activity')->group(function () {
    Route::post('session/start', [UserActivityController::class, 'startSession']);
    Route::post('session/end', [UserActivityController::class, 'endSession']);
    Route::post('heartbeat', [UserActivityController::class, 'heartbeat']);
    Route::post('return', [UserActivityController::class, 'reportReturn']);
    Route::get('pending', [UserActivityController::class, 'getPendingReports']);
    Route::post('explain/{reportId}', [UserActivityController::class, 'submitExplanation']);
    Route::get('statistics', [UserActivityController::class, 'getStatistics']);
    Route::get('reports', [UserActivityController::class, 'index']);
    Route::put('reports/{id}', [UserActivityController::class, 'update']);
    Route::delete('reports/{id}', [UserActivityController::class, 'destroy']);
    Route::get('users', [UserActivityController::class, 'users']);
    Route::get('settings', [UserActivityController::class, 'getSettings']);
    Route::post('settings', [UserActivityController::class, 'saveSettings']);
});

// Lead Management Routes
Route::prefix('leads')->group(function () {
    Route::get('statistics', [LeadsController::class, 'statistics']);
    Route::get('pipeline', [LeadsController::class, 'pipeline']);
    Route::get('options', [LeadsController::class, 'options']);
    Route::get('export', [LeadsController::class, 'export']);
    Route::get('email-templates', [LeadsController::class, 'emailTemplates']);
    Route::post('email-templates', [LeadsController::class, 'storeEmailTemplate']);
    Route::put('email-templates/{id}', [LeadsController::class, 'updateEmailTemplate']);
    Route::delete('email-templates/{id}', [LeadsController::class, 'deleteEmailTemplate']);
    Route::get('{id}/activities', [LeadsController::class, 'activities']);
    Route::post('{id}/status', [LeadsController::class, 'updateStatus']);
    Route::post('{id}/assign', [LeadsController::class, 'assignUser']);
    Route::post('{id}/follow-up', [LeadsController::class, 'scheduleFollowUp']);
    Route::post('{id}/note', [LeadsController::class, 'addNote']);
    Route::post('{id}/log-call', [LeadsController::class, 'logCall']);
    Route::post('{id}/convert', [LeadsController::class, 'convertToClient']);
});
Route::resource('leads', LeadsController::class);

// ============================================
// Legume Business Management Routes
// ============================================
use Admin\Http\Controllers\Api\LegumeProductController;
use Admin\Http\Controllers\Api\SupplierController;
use Admin\Http\Controllers\Api\LegumeBudgetController;
use Admin\Http\Controllers\Api\LegumePurchaseController;
use Admin\Http\Controllers\Api\LegumeInventoryController;
use Admin\Http\Controllers\Api\LegumeCustomerController;
use Admin\Http\Controllers\Api\LegumeOrderController;
use Admin\Http\Controllers\Api\LegumeOrderPaymentController;
use Admin\Http\Controllers\Api\LegumeDeliveryTrackingController;
use Admin\Http\Controllers\Api\LegumeStockAlertController;
use Admin\Http\Controllers\Api\LegumeProfitReportController;

Route::prefix('legume')->group(function () {
    // Products
    Route::get('products/statistics', [LegumeProductController::class, 'statistics']);
    Route::get('products/dropdown', [LegumeProductController::class, 'dropdown']);
    Route::put('products/{id}/prices', [LegumeProductController::class, 'updatePrices']);
    Route::resource('products', LegumeProductController::class);

    // Suppliers
    Route::get('suppliers/statistics', [SupplierController::class, 'statistics']);
    Route::get('suppliers/dropdown', [SupplierController::class, 'dropdown']);
    Route::get('suppliers/{id}/purchase-history', [SupplierController::class, 'purchaseHistory']);
    Route::resource('suppliers', SupplierController::class);

    // Budget
    Route::get('budget/current', [LegumeBudgetController::class, 'currentBudget']);
    Route::get('budget/statistics', [LegumeBudgetController::class, 'statistics']);
    Route::resource('budget', LegumeBudgetController::class)->only(['index', 'store']);

    // Purchases
    Route::get('purchases/statistics', [LegumePurchaseController::class, 'statistics']);
    Route::post('purchases/{id}/complete', [LegumePurchaseController::class, 'complete']);
    Route::post('purchases/{id}/cancel', [LegumePurchaseController::class, 'cancel']);
    Route::get('purchases/by-supplier/{supplierId}', [LegumePurchaseController::class, 'bySupplier']);
    Route::resource('purchases', LegumePurchaseController::class);

    // Inventory
    Route::get('inventory/statistics', [LegumeInventoryController::class, 'statistics']);
    Route::get('inventory/low-stock', [LegumeInventoryController::class, 'lowStockAlerts']);
    Route::post('inventory/adjust', [LegumeInventoryController::class, 'adjust']);
    Route::get('inventory/{productId}/movements', [LegumeInventoryController::class, 'movements']);
    Route::resource('inventory', LegumeInventoryController::class)->only(['index', 'show']);

    // Customers
    Route::get('customers/statistics', [LegumeCustomerController::class, 'statistics']);
    Route::get('customers/dropdown', [LegumeCustomerController::class, 'dropdown']);
    Route::get('customers/{id}/orders', [LegumeCustomerController::class, 'orderHistory']);
    Route::resource('customers', LegumeCustomerController::class);

    // Orders
    Route::get('orders/statistics', [LegumeOrderController::class, 'statistics']);
    Route::post('orders/{id}/status', [LegumeOrderController::class, 'updateStatus']);
    Route::post('orders/{id}/items', [LegumeOrderController::class, 'addItem']);
    Route::delete('orders/{orderId}/items/{itemId}', [LegumeOrderController::class, 'removeItem']);
    Route::resource('orders', LegumeOrderController::class);

    // Payments
    Route::get('payments/statistics', [LegumeOrderPaymentController::class, 'statistics']);
    Route::get('payments/order/{orderId}', [LegumeOrderPaymentController::class, 'forOrder']);
    Route::post('payments/verify-mobile', [LegumeOrderPaymentController::class, 'verifyMobileMoney']);
    Route::post('payments/{id}/complete', [LegumeOrderPaymentController::class, 'markCompleted']);
    Route::post('payments/{id}/reverse', [LegumeOrderPaymentController::class, 'reverse']);
    Route::resource('payments', LegumeOrderPaymentController::class)->except(['update']);

    // Deliveries
    Route::get('deliveries/statistics', [LegumeDeliveryTrackingController::class, 'statistics']);
    Route::get('deliveries/order/{orderId}', [LegumeDeliveryTrackingController::class, 'forOrder']);
    Route::post('deliveries/{id}/status', [LegumeDeliveryTrackingController::class, 'updateStatus']);
    Route::resource('deliveries', LegumeDeliveryTrackingController::class);

    // Stock Alerts
    Route::get('alerts', [LegumeStockAlertController::class, 'index']);
    Route::get('alerts/statistics', [LegumeStockAlertController::class, 'statistics']);
    Route::post('alerts/{id}/acknowledge', [LegumeStockAlertController::class, 'acknowledge']);
    Route::post('alerts/acknowledge-multiple', [LegumeStockAlertController::class, 'acknowledgeMultiple']);
    Route::post('alerts/acknowledge-all', [LegumeStockAlertController::class, 'acknowledgeAll']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('dashboard', [LegumeProfitReportController::class, 'dashboard']);
        Route::get('daily', [LegumeProfitReportController::class, 'dailyReport']);
        Route::get('weekly', [LegumeProfitReportController::class, 'weeklyReport']);
        Route::get('monthly', [LegumeProfitReportController::class, 'monthlyReport']);
        Route::get('by-product', [LegumeProfitReportController::class, 'profitByProduct']);
        Route::get('custom-period', [LegumeProfitReportController::class, 'customPeriod']);
    });
});
