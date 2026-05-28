<?php
class SettingController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        $group = $_GET['group'] ?? '';
        $where = "1=1";
        $params = [];
        if ($group) {
            $where .= " AND `group` = :g";
            $params[':g'] = $group;
        }
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('setting') . " WHERE {$where}", $params);
        $result = [];
        foreach ($list as $item) {
            $result[$item['key']] = $item['value'];
        }
        Response::success($result);
    }

    public function postSave()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $group = $data['group'] ?? 'basic';
        $settings = $data['settings'] ?? [];
        foreach ($settings as $key => $value) {
            $exist = $this->db->fetch("SELECT id FROM " . $this->db->table('setting') . " WHERE `group` = :g AND `key` = :k", [':g' => $group, ':k' => $key]);
            if ($exist) {
                $this->db->update('setting', ['value' => $value], 'id = :id', [':id' => $exist['id']]);
            } else {
                $this->db->insert('setting', [
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
        }
        Response::success();
    }
}
