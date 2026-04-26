<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WebsiteModel;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return view('auth/login');
        }

        $userModel = new UserModel();
        $websiteModel = new WebsiteModel();

        $data = [
            'styles' => [], // Will be loaded from styles table
            'websites' => $websiteModel->findAll(),
            'users' => $userModel->findAll(),
        ];

        return view('dashboard', $data);
    }
}