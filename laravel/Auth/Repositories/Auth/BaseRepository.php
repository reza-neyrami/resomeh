<?php

namespace App\Repositories\Auth;
use Illuminate\Support\Facades\Cache;




class BaseRepository  
{

    public function verification_code()
    {
        return mt_rand(100000, 999999);

    }

    public function cacheVerification($username, $token, $phoneNumber)
    {
        Cache::put($username, ['random' => $token, 'username' => $phoneNumber], now()->addMinutes(2));
    }
    
    public function getVerificationStatus($username)
    {

        return Cache::get($username);
    }

}
