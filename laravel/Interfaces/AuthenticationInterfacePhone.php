<?php

namespace App\Interfaces;


interface AuthenticationInterfacePhone
{
    public function model();

    public function login($request,$service);
    
    public function verifyCode($phone);

    public function register($request);
    
    public function sendVerification($request);

    public function resendCode($phone, $token);


}
