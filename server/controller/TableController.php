<?php
class TableController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        Auth::getAdmin();
        $list = $this->db->fetchAll("SELECT t.*, o.order_no as current_order_no FROM " . $this->db->table('table') . " t LEFT JOIN " . $this->db->table('order') . " o ON t.current_order_id = o.id ORDER BY t.id ASC");
        Response::success($list);
    }

    public function getScan()
    {
        $tableNo = $_GET['table_no'] ?? '';
        if (!$tableNo) {
            Response::error('桌号不能为空');
        }
        $table = $this->db->fetch("SELECT * FROM " . $this->db->table('table') . " WHERE table_no = :tn", [':tn' => $tableNo]);
        if (!$table) {
            Response::error('桌位不存在');
        }
        $order = null;
        if ($table['current_order_id']) {
            $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :oid", [':oid' => $table['current_order_id']]);
        }
        Response::success([
            'table' => [
                'id' => $table['id'],
                'table_no' => $table['table_no'],
                'area' => $table['area'],
                'seats' => $table['seats'],
                'status' => $table['status']
            ],
            'order' => $order
        ]);
    }

    public function postCreate()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $data['create_time'] = date('Y-m-d H:i:s');
        $id = $this->db->insert('table', $data);
        Response::success(['id' => $id]);
    }

    public function putUpdate($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('table', $data, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function deleteDelete($id)
    {
        Auth::getAdmin();
        $this->db->delete('table', 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function putOpen($id)
    {
        Auth::getAdmin();
        $this->db->update('table', ['status' => 1], 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function putClose($id)
    {
        Auth::getAdmin();
        $this->db->update('table', ['status' => 0, 'current_order_id' => 0], 'id = :id', [':id' => $id]);
        Response::success();
    }
}
