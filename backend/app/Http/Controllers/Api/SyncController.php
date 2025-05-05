<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Staff;
use App\Models\StockChange;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    protected $models = [
        'sales' => Sale::class,
        'sale_items' => SaleItem::class,
        'staff' => Staff::class,
        'stock_changes' => StockChange::class,
        'stock_requests' => StockRequest::class,
        'stock_request_items' => StockRequestItem::class,
        'users' => User::class,
        'warehouses' => Warehouse::class,
        'refunds' => Refund::class,
        'refund_items' => RefundItem::class,
        'inventory' => Inventory::class,
        'payments' => Payment::class,
        'branches' => Branch::class,
        'businesses' => Business::class,
        'categories' => Category::class,
        'customers' => Customer::class,
    ];

    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'last_sync_at' => 'nullable|date',
            'models' => 'required|array',
            'models.*' => 'required|string|in:' . implode(',', array_keys($this->models)),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deviceId = $request->device_id;
        $lastSyncAt = $request->last_sync_at;
        $modelsToSync = $request->models;

        DB::beginTransaction();
        try {
            $syncResults = [];

            foreach ($modelsToSync as $modelName) {
                $modelClass = $this->models[$modelName];
                $incomingData = $request->input($modelName, []);
                
                // Handle incoming data
                foreach ($incomingData as $data) {
                    $model = $modelClass::updateOrCreate(
                        ['id' => $data['id']],
                        array_merge($data, ['device_id' => $deviceId])
                    );
                }

                // Get data that needs to be synced back to the device
                $query = $modelClass::query();
                if ($lastSyncAt) {
                    $query->where('updated_at', '>', $lastSyncAt);
                }
                $query->orWhere('device_id', $deviceId);

                $syncResults[$modelName] = $query->get();
            }

            DB::commit();

            return response()->json([
                'message' => 'Sync completed successfully',
                'data' => $syncResults,
                'sync_timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    public function getPendingSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'models' => 'required|array',
            'models.*' => 'required|string|in:' . implode(',', array_keys($this->models)),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pendingData = [];
        foreach ($request->models as $modelName) {
            $modelClass = $this->models[$modelName];
            $pendingData[$modelName] = $modelClass::where('sync_status', 'pending')
                ->orWhere('device_id', $request->device_id)
                ->get();
        }

        return response()->json([
            'data' => $pendingData,
            'sync_timestamp' => now(),
        ]);
    }

    public function markAsSynced(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'models' => 'required|array',
            'models.*.name' => 'required|string|in:' . implode(',', array_keys($this->models)),
            'models.*.ids' => 'required|array',
            'models.*.ids.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->models as $modelData) {
            $modelClass = $this->models[$modelData['name']];
            $modelClass::whereIn('id', $modelData['ids'])
                ->update([
                    'sync_status' => 'synced',
                    'last_sync_at' => now(),
                ]);
        }

        return response()->json([
            'message' => 'Records marked as synced successfully',
            'sync_timestamp' => now(),
        ]);
    }
}
