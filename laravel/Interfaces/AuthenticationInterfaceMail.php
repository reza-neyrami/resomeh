<?php

namespace App\Interfaces;


interface AuthenticationInterfaceMail
{
    public function model();

    public function login($request,$service);
    
    public function verifyCode($phone);

    public function register($request);
    
    public function sendVerification($request);

    public function resendCode($phone, $token);


}
