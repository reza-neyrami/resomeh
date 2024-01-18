<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\BaseController;
use App\Http\Requests\Admin\UploadImageRequst;
use App\Http\Requests\Auth\AuthUserRegisterRequest;
use App\Http\Requests\Auth\LoginPhoneRequest;
use App\Http\Requests\Auth\UploadImageRequstHome;
use App\Http\Requests\Auth\UploadVideoRequst;
use App\Http\Requests\Auth\VerfriCodeActivationRequest;
use App\Services\AuthService\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class AuthController extends BaseController
{

    protected $url;
    protected $receptor;
    protected $token;
    protected $template;

    protected $authServices;

    public function __construct(AuthService $authServices) {
        $this->authServices = $authServices;
    }


    public function loginPhone(LoginPhoneRequest $request)
    {
        $username = $request->username;
        $authRepo = $this->authServices->getAuthRepository($username);
        $service = $this->authServices->getAuthService($username);
        $response = $authRepo->login($username, $service);
        return response()->json($response, 200);
    }

    public function registeration(AuthUserRegisterRequest $request)
    {
        $username = $request->username;
        $authRepo = $this->authServices->getAuthRepository($username);
        $response = $authRepo->register($request);
        if (isset($response['user'])) {
            return response([
                'message' => $response['message'],
                'auth' => $response['user'],
                'access_token' => $response['user']->createToken("Register")->accessToken,
                'login' => true,
            ]);
        }
        return response($response);
    }

    public function verifyCode(VerfriCodeActivationRequest $request)
    {
        $username = $request->username;
        $authRepo = $this->authServices->getAuthRepository($username);
        $response = $authRepo->verifyCode($request);
        if (isset($response['user'])) {
            return response([
                'auth' => $response['user'],
                'message' => $response['message'],
                'access_token' => $response['user']->createToken("Login token")->accessToken,
                'checkout' => $response['checkout'],
                'username' => $username,
                'codephone' => $response['codephone']
            ]);
        }
        return response($response);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response(['message' => 'logout is SuccessFully'], 200);
    }

    public function uploadImagesLogin(UploadImageRequstHome $request)
    {

        Log::info('Public path: ' . public_path());
        try {
            $bannerName = date('Y-M/d') . '/' . '_Banner';

            $banner = $request->file('banner');

            $url = Storage::disk('banners')->put($bannerName, $banner);
            return response(
                [
                    'message' => 'اپلود بنر با موفقیت انجام شد',
                    'banner' => '/api/banners/' . $url,
                ],
                200
            );
        } catch (\Exception $e) {
            Log::info(response($e->getMessage()));
            return response(['message' => 'خطایی رخ داده است' . '-' . $e->getMessage()], 500);
        }

    }


}
