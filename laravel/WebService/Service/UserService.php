<?php

namespace App\Services;


use App\Exceptions\UserAlreadyRegisteredException;
use App\Http\Requests\Auth\RegisterNewUserRequest;
use App\Http\Requests\Auth\RegisterVerifyUserRequest;
use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangeEmailSubmitRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\FollowingUserRequest;
use App\Http\Requests\User\FollowUserRequest;
use App\Http\Requests\User\UnfollowUserRequest;
use App\Http\Requests\User\UnregisterUserRequest;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    const CHANGE_EMAIL_CACHE_KEY = 'change.email.for.user.';

    public static function registerNewUser(RegisterNewUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $field = $request->getFieldName();
            $value = $request->getFieldValue();

            // اگر کاربر از قبل ثبت نام کرده باشد باید روال ثبت نام را قطع کنیم
            if ($user = User::where($field, $value)->first()) {
                // اگر کاربر من ازقبل ثبت نام خودش رو کامل کرده باشه باید بهش خطا بدم
                if ($user->verified_at) {
                    throw new UserAlreadyRegisteredException('شما قبلا ثبت نام کرده اید');
                }

                return response(['message' => 'کد فعالسازی قبلا برای شما ارسال شده'], 200);
            }

            $code = random_verification_code();
            $user = User::create([
                $field => $value,
                'verify_code' => $code,
            ]);

            //TODO: ارسال ایمیل یا پیامک به کاربر
            Log::info('SEND-REGISTER-CODE-MESSAGE-TO-USER', ['code' => $code]);

            DB::commit();
            return response(['message' => 'کاربر ثبت موقت شد'], 200);
        } catch (Exception $exception) {
            Db::rollBack();

            if ($exception instanceof UserAlreadyRegisteredException) {
                throw $exception;
            }

            Log::error($exception);
            return response(['message' => 'خطایی رخ داده است']);
        }
    }

    public static function registerNewUserVerify(RegisterVerifyUserRequest $request)
    {
        $field = $request->has('email') ? 'email' : 'mobile';
        $code = $request->code;

        $user = User::where([
            $field => $request->input($field),
            'verify_code' => $code,
        ])->first();

        if (empty($user)) {
            throw new ModelNotFoundException('کاربری با اطلاعات مورد نظر یافت نشد');
        }

        $user->verify_code = null;
        $user->verified_at = now();
        $user->save();

        return response($user, 200);
    }

    public static function resendVerificationCodeToUser(ResendVerificationCodeRequest $request)
    {
        $field = $request->getFieldName();
        $value = $request->getFieldValue();

        $user = User::where($field, $value)->whereNull('verified_at')->first();

        if (!empty($user)) {
            $dateDiff = now()->diffInMinutes($user->updated_at);

            // اگر زمان مورد نظر از ارسال کد قبلی گذشته بود مجددا کد جدید ایجاد و ارسال میکنیم
            if ($dateDiff > config('auth.resend_verification_code_time_diff', 60)) {
                $user->verify_code = random_verification_code();
                $user->save();
            }

            //TODO: ارسال ایمیل یا پیامک به کاربر
            Log::info('RESEND-REGISTER-CODE-MESSAGE-TO-USER', ['code' => $user->verify_code]);

            return response([
                'message' => 'کد مجدداً برای شما ارسال گردید'
            ], 200);
        }

        throw new ModelNotFoundException('کاربری با این مشخصات یافت نشد یا قبلا فعالسازی شده است');
    }

    public static function changeEmail(ChangeEmailRequest $request)
    {
        try {
            $email = $request->email;
            $userId = auth()->id();
            $code = random_verification_code();
            $expireDate = now()->addMinutes(config('auth.change_email_cache_expiration', 1440));
            Cache::put(self::CHANGE_EMAIL_CACHE_KEY . $userId, compact('email', 'code'), $expireDate);

            //TODO: ارسال ایمیل به کاربر برای تغییر ایمیل
            Log::info('SEND-CHANGE-EMAIL-CODE', compact('code'));

            return response([
                'message' => 'ایمیلی به شما ارسال شد لطفا صندوق دریافتی خود را بررسی نمایید'
            ], 200);
        } catch (Exception $e) {
            Log::error($e);
            return response([
                'message' => 'خطایی رخ داده است و سرور قادر به ارسال کد فعالسازی نمیباشد'
            ], 500);
        }
    }

    public static function changeEmailSubmit(ChangeEmailSubmitRequest $request)
    {
        $userId = auth()->id();
        $cacheKey = self::CHANGE_EMAIL_CACHE_KEY . $userId;
        $cache = Cache::get($cacheKey);

        if (empty($cache) || $cache['code'] != $request->code) {
            return response([
                'message' => 'درخواست نامعتبر'
            ], 400);
        }

        $user = auth()->user();
        $user->email = $cache['email'];
        $user->save();
        Cache::forget($cacheKey);

        return response([
            'message' => 'ایمیل با موفقیت تغییر یافت'
        ], 200);
    }

    public static function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth()->user();

            if (!Hash::check($request->old_password, $user->password)) {
                return response(['message' => 'گذر واژه وارد شده مطابقت ندارد'], 400);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return response(['message' => 'تغییر گذرواژه با موفقیت انجام شد'], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => 'خطایی به وجود آمده است'], 500);
        }
    }

    public static function follow(FollowUserRequest $request)
    {
        $user = $request->user();
        $user->follow($request->channel->user);
        return response(['message' => 'با موفقیت انجام شد'], 200);
    }

    public static function unfollow(UnfollowUserRequest $request)
    {
        $user = $request->user();
        $user->unfollow($request->channel->user);
        return response(['message' => 'با موفقیت انجام شد'], 200);
    }

    public static function followings(FollowingUserRequest $request)
    {
        return $request->user()
            ->followings()
            ->paginate();
    }

    public static function followers(FollowingUserRequest $request)
    {
        return $request->user()
            ->followers()
            ->paginate();
    }

    public static function unregister(UnregisterUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->user()->delete();
            DB::commit();
            return response(['message' => 'کاربر با موفقیت غیر فعال شد، برای فعالسازی کافیست یک بار در سیستم لاگین کنید'], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'عملیات مورد نظر مقدور نمیباشد، دوباره سعی کنید'], 500);
        }
    }
}
