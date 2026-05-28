<?php
class ReviewController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function postCreate()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($data['order_id'] ?? 0);
        $rating = (int)($data['rating'] ?? 5);
        $content = $data['content'] ?? '';
        $images = $data['images'] ?? '';

        if (!$orderId) {
            Response::error('订单ID不能为空');
        }
        $exist = $this->db->fetch("SELECT id FROM " . $this->db->table('review') . " WHERE order_id = :oid AND member_id = :mid", [':oid' => $orderId, ':mid' => $member['id']]);
        if ($exist) {
            Response::error('已评价');
        }

        if (is_array($images)) {
            $images = implode(',', $images);
        }

        $this->db->insert('review', [
            'order_id' => $orderId,
            'member_id' => $member['id'],
            'rating' => $rating,
            'content' => $content,
            'images' => $images,
            'create_time' => date('Y-m-d H:i:s')
        ]);
        Response::success();
    }

    public function getList()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 20;
        $total = $this->db->count('review', 'status = 1');
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT r.*, m.nickname, m.avatar FROM " . $this->db->table('review') . " r JOIN " . $this->db->table('member') . " m ON r.member_id = m.id WHERE r.status = 1 ORDER BY r.id DESC LIMIT {$offset},{$pageSize}");
        foreach ($list as &$item) {
            $item['images'] = $item['images'] ? explode(',', $item['images']) : [];
        }
        Response::page($list, $total, $page, $pageSize);
    }

    public function getAll()
    {
        Auth::getAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 20;
        $total = $this->db->count('review', '1=1');
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT r.*, m.nickname, m.avatar FROM " . $this->db->table('review') . " r JOIN " . $this->db->table('member') . " m ON r.member_id = m.id ORDER BY r.id DESC LIMIT {$offset},{$pageSize}");
        foreach ($list as &$item) {
            $item['images'] = $item['images'] ? explode(',', $item['images']) : [];
        }
        Response::page($list, $total, $page, $pageSize);
    }

    public function postReply()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        $reply = $data['reply'] ?? '';
        $this->db->update('review', ['reply' => $reply, 'reply_time' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function putStatus($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('review', ['status' => $data['status'] ?? 0], 'id = :id', [':id' => $id]);
        Response::success();
    }
}
