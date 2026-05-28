<?php
class CategoryController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('category') . " WHERE status = 1 ORDER BY sort ASC, id ASC");
        Response::success($list);
    }

    public function getAll()
    {
        Auth::getAdmin();
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('category') . " ORDER BY sort ASC, id ASC");
        Response::success($list);
    }

    public function postCreate()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $data['create_time'] = date('Y-m-d H:i:s');
        $id = $this->db->insert('category', $data);
        Response::success(['id' => $id]);
    }

    public function putUpdate($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('category', $data, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function deleteDelete($id)
    {
        Auth::getAdmin();
        $this->db->update('category', ['status' => 0], 'id = :id', [':id' => $id]);
        Response::success();
    }
}
