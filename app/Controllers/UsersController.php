<?php

namespace App\Controllers;

use App\Models\UserModel;

class UsersController extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $users = $userModel->findAll();

        return view('users/index', ['users' => $users]);
    }

    public function store()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $userModel->insert($data);

        return redirect()->to(base_url('/users'))->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function update($id)
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to(base_url('/users'))->with('error', 'Usuário não encontrado');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);

        return redirect()->to(base_url('/users'))->with('success', 'Usuário atualizado com sucesso!');
    }

    public function delete($id)
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $userModel = new UserModel();
        $userModel->delete($id);

        return redirect()->to(base_url('/users'))->with('success', 'Usuário removido com sucesso!');
    }
}