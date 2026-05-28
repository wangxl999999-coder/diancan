<?php
class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function postLogin()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if (!$username || !$password) {
            Response::error('用户名和密码不能为空');
        }
        $admin = $this->db->fetch("SELECT * FROM " . $this->db->table('admin') . " WHERE username = :u", [':u' => $username]);
        if (!$admin || $admin['password'] !== md5($password)) {
            Response::error('用户名或密码错误');
        }
        if ($admin['status'] != 1) {
            Response::error('账号已禁用');
        }
        $this->db->update('admin', ['last_login_time' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $admin['id']]);
        $token = Auth::generateToken([
            'type' => 'admin',
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => $admin['role'],
            'exp' => time() + 86400
        ]);
        Response::success(['token' => $token, 'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'realname' => $admin['realname'],
            'role' => $admin['role']
        ]]);
    }

    public function getList()
    {
        Auth::getAdmin();
        $list = $this->db->fetchAll("SELECT id,username,realname,role,status,last_login_time,create_time FROM " . $this->db->table('admin') . " ORDER BY id DESC");
        Response::success($list);
    }

    public function postCreate()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $data['password'] = md5($data['password'] ?? '123456');
        $data['create_time'] = date('Y-m-d H:i:s');
        $id = $this->db->insert('admin', $data);
        Response::success(['id' => $id]);
    }

    public function putUpdate($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['password']) && $data['password']) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }
        $this->db->update('admin', $data, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function deleteDelete($id)
    {
        Auth::getAdmin();
        $this->db->delete('admin', 'id = :id', [':id' => $id]);
        Response::success();
    }
}
