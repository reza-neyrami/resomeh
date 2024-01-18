<?php

namespace App\Repositories\Auth;

use App\Interfaces\AuthenticationInterfacePhone;
use DB;
use Log;
use Cache;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ProfileRepositoryInterface;
use Illuminate\Http\Resources\Json\JsonResource;


class AuthRepositoryPhone extends BaseRepository implements AuthenticationInterfacePhone
{

    protected $userRepository;
    protected $profileRepository;


    private $model;

    public function __construct(

        UserRepositoryInterface    $userRepository,
        ProfileRepositoryInterface $profileRepository,
        User                       $user
    )
    {
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
        $this->model = $user;
    }


    public function model()
    {
        return $this->model;
    }

    public function login($username, $kavenegarService)
    {
        try {
            $phoneNumber = '0098' . substr($username, -10, 10);
            $token = $this->verification_code();
            Log::info($token);
            $status = $kavenegarService->sendVerificationCode($phoneNumber, $token, 'verify');
            $status = collect($status)->first(); // گرفتن اولین استاتوس

            if (in_array($status->status, [4, 5, 10])) {
                // پیامک با موفقیت ارسال شد

                $this->cacheVerification($username, $token, $phoneNumber);
                return $this->respondWithMessage('کد به موبایل شما ارسال شد', true);
            } else {
                Log::error("خطا در ارسال پیامک: {$status->status}");
                return $this->respondWithError('خطایی رخ داده است.' . "{$status->status}");
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->respondWithError('خطایی رخ داده است.');
        }
    }


    public function verifyCode($request)
    {
        $phoneVerificationStatus = $this->getVerificationStatus($request->username);

        if ($phoneVerificationStatus['random'] != $request->code) {
            return $this->respondWithInvalidCode();
        }

        $formattedPhone = '0098' . '' . Str::substr($phoneVerificationStatus['username'], -10, 10);
        $user = $this->getUserPhone($formattedPhone);

        if ($user) {
            return $this->respondWithUser($user, 'شما با موفقیت لاگین شدید', 'login', $formattedPhone);
        }

        return $this->respondWithRedirectToAuthentication($formattedPhone);
    }


    public function register($request)
    {

        DB::beginTransaction();
        try {
            $user = $this->getUserPhone($request->username);
            if ($user) {
                return $this->respondWithUser($user, 'ثبت نام شما با موفقیت ثبت شد.');
            }

            $user = $this->userRepository->store([
                'name' => $request->firstName . '-' . $request->lastName,
                'active' => true,
                'mobile' => $request->username,
                'code_assigment' => $request->code_assigment,
                'email_verified_at' => Carbon::now(),
            ]);
            if ($user) {
                $profile = $this->getUserProfile($user->phone);
                if ($profile) {
                    $repository = $this->profileRepository->update($profile->id, [
                        'fullname' => $request->firstName . '-' . $request->lastName,
                        'codeposti' => $request->codeposti,
                        'address' => $request->address,
                        'birthday' => $request->birthday,
                        'phone' => $request->username,
                        'city' => $request->city,
                        'image' => $request->banner,
                        'user_id' => $user->id
                    ]);
                } else {

                    $repository = $this->profileRepository->store([
                        'fullname' => $request->firstName . '-' . $request->lastName,
                        'codeposti' => $request->codeposti,
                        'address' => $request->address,
                        'birthday' => $request->birthday,
                        'phone' => $request->username,
                        'city' => $request->city,
                        'image' => $request->banner,
                        'user_id' => $user->id
                    ]);
                }
                DB::commit();
                return $this->respondWithUser($user, 'ثبت نام شما با موفقیت ثبت شد.');
            }
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e->getMessage());
            return $this->respondWithError('ثبت نام با مشکل رو به رو شده.', $e->getMessage());
        }
    }

    private function respondWithMessage($message, $activecode)
    {
        return ['message' => $message, 'activecode' => $activecode];
    }

    private function respondWithError($message, $error = null)
    {
        return ['activecode' => false, 'message' => $message, 'error' => $error];
    }

    private function respondWithUser($user, $message, $checkout = null, $phone = null)
    {
        return [
            'user' => $user,
            'message' => $message,
            'checkout' => $checkout,
            'username' => $phone,
            'codephone' => false
        ];
    }

    private function respondWithInvalidCode()
    {
        return [
            'message' => 'کد وارد شده اشتباه می‌باشد',
            'codephone' => true,
            'code' => false,
            'checkout' => false,
        ];
    }

    private function respondWithRedirectToAuthentication($phone)
    {
        return [
            'message' => 'انتقال به بخش احراز هویت',
            'username' => $phone,
            'checkout' => true,
            'codephone' => false,
            'code' => false
        ];
    }

    protected function getUserPhone($phone)
    {
        return $this->model()->where('mobile', $phone)->first();
    }

    protected function getUserProfile($phone)
    {
        $mobile = substr($phone, -10,10);
        return $this->profileRepository->model()->where('phone', 'like', '%' . $mobile)->first();
    }


    public function sendVerification($request)
    {

    }

    public function resendCode($phone, $token)
    {

    }


}
