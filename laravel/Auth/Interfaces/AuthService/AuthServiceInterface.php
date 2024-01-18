<?php
namespace App\Interfaces\AuthService;
interface AuthServiceInterface
{
    public function getAuthRepository();
    public function getAuthService();
    public function matchesPattern($username) ;
}