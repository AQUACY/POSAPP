<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Staff;
use App\Services\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    protected $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'admin',
            'status' => 'pending',
        ]);

        // Generate and send OTP
        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp, 'otp_expires_at' => Carbon::now()->addMinutes(10)]);

        try {
            $this->messagingService->sendOtp($user->phone, $otp);
        } catch (\Exception $e) {
            // Log the error but don't fail the registration
            Log::error('Failed to send OTP: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. Please verify your account with the OTP sent to your phone.',
            'user_id' => $user->id
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if ($user->otp !== $request->otp || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $user->update([
            'status' => 'verified',
            'otp' => null,
            'otp_expires_at' => null
        ]);

        return response()->json(['message' => 'Account verified successfully']);
    }

    public function setupBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'address' => 'required|string',
            'whatsapp_contact' => 'required|string',
            'business_logo' => 'required|image|max:2048',
            'email' => 'required|string|email|max:255|unique:businesses',
            'tax_id' => 'required|string',
            // 'receipt_settings' => 'required|array',
            // 'report_settings' => 'required|array',
            // 'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        
        if ($user->status !== 'verified') {
            return response()->json(['message' => 'Please verify your account first'], 403);
        }

        // Handle logo upload
        $logoPath = $request->file('business_logo')->store('business-logos', 'public');

        $business = Business::create([
            'user_id' => $user->id,
            'name' => $request->business_name,
            'type' => $request->business_type,
            'address' => $request->address,
            'whatsapp_contact' => $request->whatsapp_contact,
            'logo_path' => $logoPath,
            'email' => $request->email,
            'tax_id' => $request->tax_id,
            // 'receipt_settings' => $request->receipt_settings,
            // 'report_settings' => $request->report_settings,
            // 'settings' => $request->settings,
            'status' => 'active',
            'subscription_end_date' => Carbon::now()->addDays(30), // Default 30-day trial
        ]);

        $user->update([
            'business_id' => $business->id
        ]); 

        return response()->json([
            'message' => 'Business setup completed successfully',
            'business' => $business
        ]);
    }

    public function setupBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|max:255|unique:branches',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $business = $user->business;

        if (!$business) {
            return response()->json(['message' => 'Please setup your business first'], 403);
        }

        $branch = Branch::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'address' => $request->address,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Branch setup completed successfully',
            'branch' => $branch
        ]);
    }

    public function createStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'role' => 'required|in:cashier,branch_manager,inventory_clerk',
            'branch_id' => 'required|exists:branches,id',
            'whatsapp_contact' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $business = $user->business;

        // Generate temporary password
        $tempPassword = Str::random(8);

        $staffUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => 'active',
            'business_id' => $business->id,
            'branch_id' => $request->branch_id,
        ]);

        $staff = Staff::create([
            'user_id' => $staffUser->id,
            'business_id' => $business->id,
            'branch_id' => $request->branch_id,
            'whatsapp_contact' => $request->whatsapp_contact,
            'status' => 'active'
        ]);

        // Send credentials via WhatsApp
        try {
            $message = "Welcome to {$business->name}!\n\n";
            $message .= "Your account has been created with the following credentials:\n";
            $message .= "Email: {$request->email}\n";
            $message .= "Temporary Password: {$tempPassword}\n\n";
            $message .= "Please login and change your password immediately.\n";
            $message .= "Role: " . ucfirst($request->role) . "\n\n";
            $message .= "Best regards,\n{$business->name} Team";

            $this->messagingService->sendWhatsApp(
                $request->whatsapp_contact,
                $message
            );
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp credentials: ' . $e->getMessage());
            // Don't fail the staff creation if WhatsApp message fails
        }

        return response()->json([
            'message' => 'Staff account created successfully',
            'staff' => $staff,
            'temporary_password' => $tempPassword
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        // Check if user is already verified
        if ($user->status === 'verified') {
            return response()->json(['message' => 'User is already verified'], 400);
        }

        // Check if last OTP was sent less than 1 minute ago
        if ($user->otp_expires_at && Carbon::now()->diffInSeconds($user->otp_expires_at) > 540) {
            return response()->json(['message' => 'Please wait before requesting a new OTP'], 429);
        }

        // Generate new OTP
        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        try {
            $this->messagingService->sendOtp($user->phone, $otp);
            return response()->json([
                'message' => 'New OTP has been sent to your phone',
                'expires_in' => 600 // 10 minutes in seconds
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    // public function updateUserBusiness(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'business_id' => 'required|exists:businesses,id'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $user = auth()->user();
        
    //     // Update user with business ID
    //     $user->update([
    //         'business_id' => $request->business_id
    //     ]);

    //     return response()->json([
    //         'message' => 'User business updated successfully',
    //         'user' => $user
    //     ]);
    // }
} 