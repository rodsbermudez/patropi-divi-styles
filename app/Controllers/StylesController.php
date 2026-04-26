<?php

namespace App\Controllers;

use App\Models\StyleModel;

class StylesController extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $styleModel = new StyleModel();
        $styles = $styleModel->findAll();

        return view('styles/index', ['styles' => $styles]);
    }
}