<?php

namespace App\Http\Controllers\Api\Service;

use App\Http\Controllers\BaseController;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\Account\Entities\ChartOfAccount;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Customer\Entities\Customer;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthenticationService extends BaseController
{
    use UploadAble;

    public function register(array $data)
    {
        $validator = $this->validateRegistrationData($data);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $existUserCheck = Customer::where('phone', $data['phone'])->exists();
        if ($existUserCheck) {
            return ['success' => false, 'message' => 'Customer already registered'];
        }

        $otp = rand(1000, 9999);
        $customer = Customer::create([
            'warehouse_id' => 1,
            'name' => $data['name'],
            'username' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'otp' => $otp,
            'status' => 2,
        ]);
        $coa_max_code = ChartOfAccount::where('level', 4)->where('code', 'like', '1020201%')->max('code');
        $code = $coa_max_code ? ($coa_max_code + 1) : $this->coa_head_code('customer_receivable');
        $head_name = $customer->id . '-' . $customer->name;
        $customer_coa_data = $this->customer_coa($code, $head_name, $customer->id);
        ChartOfAccount::create($customer_coa_data);

        $this->sendOTP($data['phone'], $otp);
        return ['success' => true, 'message' => 'OTP sent for verification!'];
    }

    // Method to send OTP via SMS
    private function sendOTP($phone, $otp)
    {
        $url = "https://sms.brainwavebd.com/api/sms/send";
        $data = [
            "apiKey" => "A000050ebd75369-fec2-4d67-86ce-dc3f3d7e101f",
            "contactNumbers" => $phone,
            "senderId" => "8809612441286",
            "textBody" => "Your OTP: $otp"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_exec($ch);
    }

    public function verifyOtp($otp)
    {
        $user = Customer::where('otp', $otp)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], Response::HTTP_BAD_REQUEST);
        }

        $loginResponse = $this->login($user);

        if ($loginResponse->getData()->success) {
            $user->update(['otp' => '', 'status' => 1]);
            // Send Notification
//            $this->subscribeUserToOneSignal($user->id);
        }

        return $loginResponse;
    }

    public function authenticate(array $credentials)
    {
        $validator = Validator::make($credentials, [
            'phone' => 'required',
            'password' => 'required|string|min:6|max:50',
            'push_token' => 'nullable|string',
            'device_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages(),
                'errors' => $validator->errors(),
                'status_code' => 400
            ];
        }

        try {
            if (!$token = Auth::guard('customer')->attempt($validator->validated())) {
                return [
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                    'status_code' => 401
                ];
            } else {
                $customer = Auth::guard('customer')->user();
                $customerData = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'image_path' => CUSTOMER_AVATAR_PATH,
                    'image' => $customer->image
                ];

                if ($customer->status == 1) {
                    // Subscribe the user to OneSignal
//                    $this->subscribeUserToOneSignal($customer->id);

                    return [
                        'success' => true,
                        'message' => 'Successfully logged in.',
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => 60 * 24 * 7,
                        'status_code' => 200,
                        'user' => $customerData
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Your credentials do not match our records.',
                        'status_code' => 400
                    ];
                }
            }
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Failed to login, please try again.',
                'errors' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    public function getUserProfile()
    {
        $user = Auth::guard('customer')->user()->load([
            'addresses' => function ($query) {
                $query->select('id', 'customer_id', 'label', 'district', 'city', 'thana', 'area', 'information');
            },
            'customerPoint:id,customer_id,available_point,min_use_point,conversion_rate'
        ]);

        $userArray = $user->only(['id', 'name', 'phone', 'email', 'information', 'image', 'image_path', 'created_at']);
        $userArray['customer_point'] = $user->customerPoint;
        $userArray['addresses'] = $user->addresses;

        return ['data' => $userArray];
    }

    public function updateCustomerProfile(array $data)
    {
        if (!$customer = Auth::guard('customer')->user()) {
            return ['success' => false, 'message' => 'Customer not found.'];
        }

        if (!empty($data['avatar'])) {
            $image = $this->upload_base64_image($data['avatar'], CUSTOMER_AVATAR_PATH);
            if ($customer->image) {
                $this->delete_file($customer->image, CUSTOMER_AVATAR_PATH);
            }
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'information' => $data['information'] ?? null,
            'image' => $image ?? $customer->image
        ];

        Customer::where('id', $customer->id)->update($updateData);

        $new_head_name = $customer->id . '-' . $data['name'];
        $customer_coa = ChartOfAccount::where(['customer_id' => $customer->id])->first();
        if ($customer_coa) {
            $customer_coa->update(['name' => $new_head_name]);
        }

        return ['success' => true, 'message' => 'Profile updated successfully!'];
    }

    public function storeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'label' => 'required|string|unique:customer_addresses,label,' . ($request->update_id ?? 'NULL') . ',id,customer_id,' . $request->customer_id,
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'thana' => 'nullable|string',
            'area' => 'nullable|string',
            'information' => 'nullable',
            'update_id' => 'nullable|exists:customer_addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation fails', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $customer = Auth::guard('customer')->user();

        if ($request->update_id) {
            $address = $customer->addresses()->find($request->update_id);

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found!',
                ], Response::HTTP_NOT_FOUND);
            }

            // Update the address
            $address->update($request->only(['label', 'district', 'city', 'thana', 'area', 'information']));

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully!',
            ], Response::HTTP_OK);
        }
        // If creating a new address
        $customer->addresses()->create($request->only(['label', 'district', 'city', 'thana', 'area', 'information']));

        return response()->json(['success' => true, 'message' => 'Address stored successfully!'], Response::HTTP_CREATED);
    }

    public function changePassword($requestData)
    {
        $validator = Validator::make($requestData, [
            'current_password' => 'required',
            'password' => 'required|min:6|max:100',
        ]);

        if ($validator->fails()) {
            return ['message' => 'Validation fails', 'errors' => $validator->errors()];
        }

        $customer = Auth::guard('customer')->user();

        if (!Hash::check($requestData['current_password'], $customer->password)) {
            $output = ['success' => false, 'message' => 'Current password does not match!'];
        } else {
            $customer->password = $requestData['password'];
            if ($customer->save()) {
                $output = ['success' => true, 'message' => 'Password changed successfully'];

                Auth::guard('customer')->logout();
            } else {
                $output = ['success' => false, 'message' => 'Failed to change password. Try Again!'];
            }
        }

        return response()->json($output);
    }

    //    Delivery man Api --------------------------
    public function deliveryManAuthenticate(array $credentials)
    {
        // Validate credentials
        $validator = Validator::make($credentials, [
            'phone' => 'required|string',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        try {
            // Attempt to generate JWT token
            if (!$token = JWTAuth::attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Invalid login credentials.'
                ];
            }
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Could not create token.'
            ];
        }

        // Token created successfully, fetch user data
        $user = Auth::user();

        if ($user->status != 1) {
            return [
                'success' => false,
                'message' => 'Your credentials do not match our records.'
            ];
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'image_path' => MATERIAL_IMAGE_PATH,
            'avatar' => $user->avatar
        ];

        return [
            'success' => true,
            'message' => 'Successfully logged in.',
            'token' => $token,
            'user' => $userData
        ];
    }

    public function deliveryManUpdateProfile(array $data)
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        if (!empty($data['avatar'])) {
            $data['avatar'] = $this->upload_base64_image($data['avatar'], MATERIAL_IMAGE_PATH);
        } else {
            $data['avatar'] = $user->avatar;
        }

        $user->update($data);

        return [
            'success' => true,
            'message' => 'Profile updated successfully!'
        ];
    }


    //One Signal Helper methods
//    private function subscribeUserToOneSignal($userId)
//    {
//        try {
//            $client = new OneSignalClient(config('onesignal.app_id'), config('onesignal.api_key'), null);
//            $response = $client->createPlayer([
//                'app_id' => config('onesignal.app_id'),
//                'device_type' => 5, // Use 0 for web or unknown device type
//                'external_user_id' => (string) $userId,
//            ]);
//
//            // Decode the response body to log it
//            $responseBody = json_decode($response->getBody()->getContents(), true);
//
//            info('User successfully subscribed to OneSignal.', ['response' => $responseBody]);
//        } catch (Exception $e) {
//            info('Failed to subscribe user to OneSignal.', [
//                'error_message' => $e->getMessage(),
//                'user_id' => $userId,
//            ]);
//        }
//    }

    private function validateRegistrationData(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string',
            'phone' => 'required|string|unique:customers',
            'password' => 'required|string|min:6|max:50',
        ]);
    }

    private function validationErrorResponse($validator)
    {
        return [
            'success' => false,
            'message' => $validator->messages()->first(),
            'status_code' => 400
        ];
    }

    private function login($user)
    {
        try {
            $token = Auth::guard('customer')->login($user);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login failed. Please try again.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $customerData = [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'image_path' => CUSTOMER_AVATAR_PATH,
                'image' => $user->image,
                'created_at' => $user->created_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in.',
                'token' => $token,
                'token_type' => 'bearer',
                'status_code' => 200,
                'user' => $customerData
            ], Response::HTTP_OK);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log in, please try again.',
                'errors' => $e->getMessage(),
                'status_code' => 500
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function customer_coa(string $code, string $head_name, int $customer_id)
    {
        return [
            'code' => $code,
            'name' => $head_name,
            'parent_name' => 'Customer Receivable',
            'level' => 4,
            'type' => 'A',
            'transaction' => 1,
            'general_ledger' => 2,
            'customer_id' => $customer_id,
            'supplier_id' => null,
            'budget' => 2,
            'depreciation' => 2,
            'depreciation_rate' => '0',
            'status' => 1,
            'created_by' => optional(Auth::user())->name ?? ''
        ];
    }
}
