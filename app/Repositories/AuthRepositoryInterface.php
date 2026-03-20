<?php

namespace App\Repositories;

interface AuthRepositoryInterface
{
    public function register(array $data);
    public function login(string $email);
}
