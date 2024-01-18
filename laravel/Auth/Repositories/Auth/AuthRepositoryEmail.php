<?php

namespace App\Repositories\Auth;

use DB;
use Log;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Interfaces\AuthenticationInterfaceMail;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ProfileRepositoryInterface;


class AuthRepositoryEmail extends BaseRepository implements AuthenticationInterfaceMail
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

    public function login($username, $mailService)
    {
        try {
            $code = $this->verification_code();
            $mailService->sendVerificationCode($username, $code);
            $this->cacheVerification($username, $code, $username);
            return $this->respondWithMessage('کد به ایمیل شما ارسال شد', true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->respondWithError('خطایی رخ داده است.');
        }
    }


    public function verifyCode($request)
    {
        $emailVerificationStatus = $this->getVerificationStatus($request->username);

        if ($emailVerificationStatus['random'] != $request->code) {
            return $this->respondWithInvalidCode();
        }

        $email = $emailVerificationStatus['username'];
        $user = $this->getUserEmail($email);

        if ($user) {
            return $this->respondWithUser($user, 'شما با موفقیت لاگین شدید', 'login', $email);
        }

        return $this->respondWithRedirectToAuthentication($email);
    }

    public function register($request)
    {

        DB::beginTransaction();
        try {
            $user = $this->getUserEmail($request->username);
            if ($user) {
                return $this->respondWithUser($user, 'ثبت نام شما با موفقیت ثبت شد.');
            }

            $user = $this->userRepository->store([
                'name' => $request->firstName . '-' . $request->lastName,
                'active' => true,
                'email' => $request->username,
                'email_verified_at' => Carbon::now(),
            ]);
            if ($user) {
                $profile = $this->getUserProfileEmail($user->email);
                if ($profile) {
                    $repository = $this->profileRepository->update($profile->id, [
                        'fullname' => $request->firstName . '-' . $request->lastName,
                        'codeposti' => $request->codeposti,
                        'address' => $request->address,
                        'email' => $request->username,
                        'birthday' => $request->birthday,
                        'city' => $request->city,
                        'image' => $request->banner,
                        'user_id' => $user->id
                    ]);
                } else {

                    $repository = $this->profileRepository->store([
                        'fullname' => $request->firstName . '-' . $request->lastName,
                        'codeposti' => $request->codeposti,
                        'address' => $request->address,
                        'email' => $request->username,
                        'birthday' => $request->birthday,
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

    private function respondWithUser($user, $message, $checkout = null, $email = null)
    {
        return [
            'user' => $user,
            'message' => $message,
            'checkout' => $checkout,
            'username' => $email,
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

    private function respondWithRedirectToAuthentication($email)
    {
        return [
            'message' => 'انتقال به بخش احراز هویت',
            'username' => $email,
            'checkout' => true,
            'codeephone' => false,
            'code' => false
        ];
    }

    protected function getUserEmail($email)
    {
        return $this->model()->where('email', $email)->first();
    }

    protected function getUserProfileEmail($email)
    {
        return $this->profileRepository->model()->where('email', $email)->first();
    }


    public function sendVerification($request)
    {

    }

    public function resendCode($email, $token)
    {

    }


}
