<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Service\AuthenticationService;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Customer\Entities\Customer;

class AuthenticationController extends BaseController
{
    protected $authenticationService;
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function register(Request $request)
    {
        $result = $this->authenticationService->register($request->only('name', 'phone', 'email', 'password'));
        return response()->json($result);
    }

    public function verifyUserOtp(Request $request)
    {
        return $this->authenticationService->verifyOtp($request->otp);
    }

    public function authenticate(Request $request)
    {
        $result = $this->authenticationService->authenticate($request->all());
        return response()->json($result, $result['status_code']);
    }

    public function myCustomerProfile()
    {
        $result = $this->authenticationService->getUserProfile();

        return response()->json($result);
    }

    public function updateCustomerProfile(Request $request)
    {
        $result = $this->authenticationService->updateCustomerProfile($request->all());

        return response()->json($result);
    }

    public function storeAddress(Request $request)
    {
        return $this->authenticationService->storeAddress($request);
    }

    public function change_password(Request $request)
    {
        return $this->authenticationService->changePassword($request->all());
    }

//    Delivery man Api --------------------------
    public function deliveryManAuthenticate(Request $request)
    {
        $credentials = $request->only('phone', 'password');
        $result = $this->authenticationService->deliveryManAuthenticate($credentials);
        return response()->json($result);
    }

    public function deliveryManUpdateProfile(Request $request)
    {
        $data = $request->only('name', 'email', 'information', 'phone', 'gender', 'avatar');
        $result = $this->authenticationService->deliveryManUpdateProfile($data);
        return response()->json($result);
    }
}
