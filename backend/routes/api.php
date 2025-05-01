<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Branch\BranchManagerController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\SuperAdmin\BusinessController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\InventoryManager\InventoryManagerController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\NotificationController;



// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // Super Admin routes
    Route::middleware([RoleMiddleware::class . ':super_admin'])->group(function () {
        Route::post('/auth/register-admin', [AuthController::class, 'registerAdmin']);
        
        // Business Management
        Route::apiResource('super-admin/businesses', BusinessController::class);
        Route::get('super-admin/businesses/{business}', [BusinessController::class, 'show']);
        Route::put('super-admin/businesses/{business}', [BusinessController::class, 'update']);
        Route::delete('super-admin/businesses/{business}', [BusinessController::class, 'destroy']);
        Route::post('super-admin/businesses/{business}/logo', [BusinessController::class, 'uploadLogo']);
        Route::patch('super-admin/businesses/{business}/toggle-status', [BusinessController::class, 'toggleStatus']);
        Route::post('super-admin/businesses/{business}/takeover', [BusinessController::class, 'takeover']);

        // Branch Management
        Route::apiResource('super-admin/branches', BranchController::class);
        Route::get('super-admin/branches/{businessId}', [BranchController::class, 'index']);
        Route::get('super-admin/branches/{businessId}/{branchId}', [BranchController::class, 'show']);
        Route::put('super-admin/branches/{businessId}/{branchId}', [BranchController::class, 'update']);
        Route::delete('super-admin/branches/{businessId}/{branchId}', [BranchController::class, 'destroy']);
        Route::patch('super-admin/branches/{businessId}/{branchId}/toggle-status', [BranchController::class, 'toggleStatus']);

        // Global Reports & Analytics
        Route::get('super-admin/dashboard', [DashboardController::class, 'index']);
        Route::get('super-admin/reports/sales', [ReportController::class, 'sales']);
        Route::get('super-admin/reports/revenue', [ReportController::class, 'revenue']);
        Route::get('super-admin/reports/businesses', [ReportController::class, 'businesses']);

        // Report Routes
        Route::prefix('super-admin/reports')->group(function () {
            Route::get('sales', [ReportController::class, 'getSalesReport']);
            Route::get('/revenue', [ReportController::class, 'getRevenueReport']);
            Route::get('/business', [ReportController::class, 'getBusinessReport']);
        });

        // User Management
        Route::apiResource('super-admin/users', UserController::class);
        Route::get('super-admin/users/{id}', [UserController::class, 'show']);
        Route::post('super-admin/users', [UserController::class, 'store']);
        Route::put('super-admin/users/{id}', [UserController::class, 'update']);
        Route::delete('super-admin/users/{id}', [UserController::class, 'destroy']);

        // Refund Management
        Route::get('super-admin/refunds', [RefundController::class, 'listRefunds']);
        Route::get('super-admin/refunds/{id}', [RefundController::class, 'getRefund']);
        Route::post('super-admin/refunds', [RefundController::class, 'createRefund']);
        Route::put('super-admin/refunds/{id}', [RefundController::class, 'updateRefund']);
        Route::delete('super-admin/refunds/{id}', [AdminController::class, 'deleteRefund']);
        Route::post('super-admin/refunds', [RefundController::class, 'store']);
        Route::post('super-admin/refunds/{refund}/approve', [RefundController::class, 'approve']);
    });

    // Admin routes
    Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
        // Business Management
        Route::post('/businesses', [AdminController::class, 'createBusiness']);
        Route::put('/businesses/{id}', [AdminController::class, 'updateBusiness']);
        Route::get('/businesses/{id}', [AdminController::class, 'getBusiness']);
        Route::get('/businesses', [AdminController::class, 'listBusinesses']);

        // Branch Management
        Route::post('/branches', [AdminController::class, 'createBranch']);
        Route::put('/branches/{id}', [AdminController::class, 'updateBranch']);
        Route::get('/branches/{id}', [AdminController::class, 'getBranch']);
        Route::get('/business/{businessId}/branches', [AdminController::class, 'listBranches']);

        // Inventory Management
        Route::post('/inventory', [AdminController::class, 'createInventory']);
        Route::put('/admin/inventory/{id}', [AdminController::class, 'updateInventory']);
        Route::get('/inventory/{id}', [AdminController::class, 'getInventory']);
        Route::get('/business/{businessId}/branch/{branchId}/inventory', [AdminController::class, 'listInventoryofBranch']);
        Route::get('/business/{businessId}/inventory', [AdminController::class, 'listInventoryofBusiness']);
        Route::delete('/inventory/{id}', [AdminController::class, 'deleteInventory']);
        Route::put('/inventory/stock/{id}', [AdminController::class, 'updateStock']);

        // Warehouse Management
        Route::post('/warehouses', [AdminController::class, 'createWarehouse']);
        Route::put('/warehouses/{id}', [AdminController::class, 'updateWarehouse']);
        Route::get('/warehouses/{id}', [AdminController::class, 'getWarehouse']);
        Route::get('/warehouses', [AdminController::class, 'listWarehouses']);
        Route::delete('/warehouses/{id}', [AdminController::class, 'deleteWarehouse']);

        // Category Management
        Route::post('/categories', [AdminController::class, 'createCategory']);
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory']);
        Route::get('/categories/{id}', [AdminController::class, 'getCategory']);
        Route::get('/business/{businessId}/categories', [AdminController::class, 'listCategoriesofBusiness']);
        Route::get('/business/{businessId}/branch/{branchId}/categories', [AdminController::class, 'listCategoriesofBranch']);

        // Staff Management
        Route::post('/staff', [AdminController::class, 'createStaff']);
        Route::put('/staff/{id}', [AdminController::class, 'updateStaff']);
        Route::get('/staff/{id}', [AdminController::class, 'getStaff']);
        Route::get('/business/{businessId}/branch/{branchId}/staff', [AdminController::class, 'listStaffofBranch']);
        Route::get('/business/{businessId}/staff', [AdminController::class, 'listStaffofBusiness']);



        // Sales Management
        Route::get('/sales/{id}', [AdminController::class, 'getSale']);
        Route::get('/sales', [AdminController::class, 'listSales']);

        // Refund Management
        Route::get('admin/refunds', [RefundController::class, 'listRefunds']);
        Route::get('admin/refunds/{id}', [RefundController::class, 'getRefund']);
        Route::post('admin/refunds', [RefundController::class, 'createRefund']);
        Route::put('admin/refunds/{id}', [RefundController::class, 'updateRefund']);
        Route::delete('admin/refunds/{id}', [RefundController::class, 'deleteRefund']);
        Route::post('admin/refunds', [RefundController::class, 'store']);
        Route::post('admin/refunds/{refund}/approve', [RefundController::class, 'approve']);
        
        // Reports
        Route::get('/businesses/{id}/performance', [AdminController::class, 'getBusinessPerformance']);
        Route::get('/branches/{id}/performance', [AdminController::class, 'getBranchPerformance']);

        // Stock management routes
        Route::post('/inventory/{id}/stock', [AdminController::class, 'addStock']);
        Route::get('/inventory/{id}/stock-history', [AdminController::class, 'getStockHistory']);
    });

    // Branch Manager routes
    Route::middleware([RoleMiddleware::class . ':branch_manager'])->group(function () {
        Route::prefix('branch/{businessId}/{branchId}/branch_manager')->group(function () {
            Route::get('/dashboard', [BranchManagerController::class, 'dashboard']);
            Route::get('/reports', [BranchManagerController::class, 'reports']);
              // Branch Management
            Route::get('/branch', [BranchManagerController::class, 'getBranchDetails']);
            Route::put('/branch', [BranchManagerController::class, 'updateBranchDetails']);
    
            // Inventory Management
            Route::get('/inventory', [BranchManagerController::class, 'getInventory']);
            Route::post('/inventory', [BranchManagerController::class, 'createInventoryItem']);
            Route::put('/inventory/{id}', [BranchManagerController::class, 'updateInventory']);
            Route::delete('/inventory/{id}', [BranchManagerController::class, 'deleteInventoryItem']);

            // Stock Request Management
            Route::get('/stock-requests', [BranchManagerController::class, 'getStockRequests']);
            Route::post('/stock-requests', [BranchManagerController::class, 'createStockRequest']);
            Route::get('/stock-requests/{id}', [BranchManagerController::class, 'getStockRequestDetails']);
            Route::post('/stock-requests/{id}/cancel', [BranchManagerController::class, 'cancelStockRequest']);
    
            // Sales Management
            Route::get('/sales', [BranchManagerController::class, 'getSales']);
            Route::get('/sales/summary', [BranchManagerController::class, 'getSalesSummary']);
    
            // Customer Management
            Route::get('/customers', [BranchManagerController::class, 'getCustomers']);
    
            // Staff Management
            Route::get('/staff', [BranchManagerController::class, 'getStaff']);
            Route::post('/staff', [BranchManagerController::class, 'createCashier']);
            Route::put('/staff/{id}', [BranchManagerController::class, 'updateCashier']);
        });

    });

     // Inventory Clerk routes
     Route::middleware([RoleMiddleware::class . ':inventory_clerk'])->group(function () {
        Route::prefix('branch/{branch}')->group(function () {
            Route::get('/inventory', [InventoryController::class, 'index']);
            Route::post('/inventory', [InventoryController::class, 'store']);
        });

         // Inventory Management
         Route::get('/inventory', [InventoryManagerController::class, 'getInventory']);
         Route::get('/inventory/{id}', [InventoryManagerController::class, 'getInventoryItem']);
         Route::post('/inventory', [InventoryManagerController::class, 'createInventory']);
         Route::put('/inventory/{id}', [InventoryManagerController::class, 'updateInventory']);
         Route::put('/inventory/{id}/quantity', [InventoryManagerController::class, 'updateQuantity']);
 
         // Inventory Reports
         Route::get('/inventory/low-stock', [InventoryManagerController::class, 'getLowStockItems']);
         Route::get('/inventory/summary', [InventoryManagerController::class, 'getInventorySummary']);
 
         // Categories
         Route::get('/categories', [InventoryManagerController::class, 'getCategories']);
    });
    
    // Cashier routes
    Route::middleware([RoleMiddleware::class . ':cashier'])->group(function () {
        Route::prefix('branch/{businessId}/{branchId}')->group(function () {
            Route::get('/dashboard', [CashierController::class, 'dashboard']);
            Route::get('/sales', [CashierController::class, 'sales']);
            Route::get('/customers', [CashierController::class, 'customers']);
            Route::post('/customers', [CashierController::class, 'createCustomer']);    
            Route::get('/products', [CashierController::class, 'products']);
            Route::get('/categories', [CashierController::class, 'categories']);
            Route::post('/sales', [SaleController::class, 'store']);
            Route::get('/sales', [SaleController::class, 'index']);
            Route::get('/sales/{id}', [SaleController::class, 'show']);
            Route::post('/sales/{id}/payment', [SaleController::class, 'processPayment']);

            // Refund routes
            Route::get('cashier/refunds', [RefundController::class, 'index']);
            Route::post('cashier/refunds', [RefundController::class, 'store']);
        });
    });

    // Notification routes
    Route::middleware('auth:api')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    });

});

