<?php

namespace App\Controllers;

use App\Models\UserModel;
use Config\Services;

class AuthController extends BaseController
{
    public function login()
    {
        if ($this->request->getMethod() === 'POST') {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            if ($user && password_verify($password, $user['password'])) {
                session()->set('user_id', $user['id']);
                session()->set('user_name', $user['name']);
                session()->set('user_email', $user['email']);

                return redirect()->to(base_url('/dashboard'))->withHeaders();
            }

            return view('auth/login', ['error' => 'Email ou senha inválidos']);
        }

        return view('auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'))->withHeaders();
    }
}