<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Business;
use App\Services\SuperAdmin\BusinessService;
use App\Http\Resources\SuperAdmin\BusinessResource;
use App\Http\Requests\SuperAdmin\BusinessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Controller for managing businesses by super admin
 */
class BusinessController extends BaseController
{
    /**
     * @var BusinessService
     */
    protected BusinessService $businessService;

    /**
     * Constructor
     *
     * @param BusinessService $businessService
     */
    public function __construct(BusinessService $businessService)
    {
        $this->businessService = $businessService;
    }

    
    /**
     * Display a listing of businesses
     *
     * @param Request $request
     * @return ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        $businesses = Business::query()
            ->withCount(['branches', 'users'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return BusinessResource::collection($businesses);
    }

    /**
     * Store a newly created business
     *
     * @param BusinessRequest $request
     * @return JsonResponse
     */
    public function store(BusinessRequest $request): JsonResponse
    {
        $business = $this->businessService->createBusiness($request->validated());
        return $this->sendSuccess(
            new BusinessResource($business),
            'Business created successfully'
        );
    }

    /**
     * Display the specified business
     *
     * @param Business $business
     * @return JsonResponse
     */
    public function show(Business $business): JsonResponse
    {
        $business->loadCount(['branches', 'users']);
        return $this->sendSuccess(
            new BusinessResource($business),
            'Business retrieved successfully'
        );
    }

    /**
     * Update the specified business
     *
     * @param BusinessRequest $request
     * @param Business $business
     * @return JsonResponse
     */
    public function update(BusinessRequest $request, Business $business): JsonResponse
    {
        $business = $this->businessService->updateBusiness($business, $request->validated());
        return $this->sendSuccess(
            new BusinessResource($business),
            'Business updated successfully'
        );
    }

    /**
     * Upload business logo
     *
     * @param Request $request
     * @param Business $business
     * @return JsonResponse
     */
    public function uploadLogo(Request $request, Business $business): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $this->businessService->uploadLogo($business, $request->file('logo'));
        $business->update(['logo' => $path]);

        return $this->sendSuccess(
            new BusinessResource($business),
            'Business logo uploaded successfully'
        );
    }

    /**
     * Toggle business active status
     *
     * @param Business $business
     * @return JsonResponse
     */
    public function toggleStatus(Business $business): JsonResponse
    {
        $business->update(['is_active' => !$business->is_active]);
        return $this->sendSuccess(
            new BusinessResource($business),
            'Business status updated successfully'
        );
    }

    /**
     * Take over business operations
     *
     * @param Business $business
     * @return JsonResponse
     */
    public function takeover(Business $business): JsonResponse
    {
        // Logic for taking over business operations
        // This could include logging the takeover, notifying business admin, etc.
        return $this->sendSuccess(
            new BusinessResource($business),
            'Business takeover initiated successfully'
        );
    }
} 