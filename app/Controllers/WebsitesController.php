<?php

namespace App\Controllers;

use App\Models\WebsiteModel;

class WebsitesController extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $websiteModel = new WebsiteModel();
        $websites = $websiteModel->findAll();

        return view('websites/index', ['websites' => $websites]);
    }

    public function store()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $websiteModel = new WebsiteModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'url' => $this->request->getPost('url'),
            'username' => $this->request->getPost('username'),
            'app_password' => $this->request->getPost('app_password'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $websiteModel->insert($data);

        return redirect()->to(base_url('/websites'))->with('success', 'Site cadastrado com sucesso!');
    }

    public function delete($id)
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $websiteModel = new WebsiteModel();
        $websiteModel->delete($id);

        return redirect()->to(base_url('/websites'))->with('success', 'Site removido com sucesso!');
    }
}