<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            'role' => 'sometimes|in:super_admin,admin,branch_manager,cashier,inventory_clerk,default',
            'branch_id' => [
                'nullable',
                'exists:branches,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role !== 'super_admin' && $request->role !== 'default' && empty($value)) {
                        $fail('The branch id field is required for this role.');
                    }
                }
            ],
            'business_id' => [
                'nullable',
                'exists:businesses,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role !== 'super_admin' && $request->role !== 'default' && empty($value)) {
                        $fail('The business id field is required for this role.');
                    }
                }
            ]
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        
        // If no role is specified, set it to default by default
        $input['role'] = $input['role'] ?? 'default';
        
        // If role is super_admin or default, don't require business_id and branch_id
        if ($input['role'] === 'super_admin' || $input['role'] === 'default') {
            unset($input['business_id']);
            unset($input['branch_id']);
        }

        $user = User::create($input);
        $success['user'] = $user;

        return $this->sendResponse($success, 'User Registered Successfully.');
    }

    /**
     * Register a new admin (Super Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function registerAdmin(Request $request) {
        // Check if the authenticated user is a super admin
        if (!Auth::user() || Auth::user()->role !== 'super_admin') {
            return $this->sendError('Unauthorized.', ['error' => 'Only super admin can create admin accounts']);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            'business_id' => 'required|exists:businesses,id'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['role'] = 'admin';
        
        $user = User::create($input);
        $success['user'] = $user;

        return $this->sendResponse($success, 'Admin Registered Successfully.');
    }

    /**
     * Login user and create token
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials']);
            }
        } catch (\Exception $e) {
            return $this->sendError('Could not create token.', ['error' => $e->getMessage()]);
        }

        $user = Auth::user();
        $success['token'] = $token;
        $success['user'] = $user;

        return $this->sendResponse($success, 'User login successfully.');
    }

    /**
     * Logout user (Revoke the token)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request) {
        try {
            JWTAuth::invalidate($request->token);
            return $this->sendResponse([], 'User logged out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Could not logout.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get authenticated user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request) {
        return $this->sendResponse($request->user(), 'User profile retrieved successfully.');
    }

    /**
     * Refresh a token
     * 
     * @return \Illuminate\Http\Response
     */
    public function refresh() {
        try {
            $token = JWTAuth::refresh();
            return $this->sendResponse(['token' => $token], 'Token refreshed successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Could not refresh token.', ['error' => $e->getMessage()]);
        }
    }

    protected function respondWithToken($token) {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }
}
