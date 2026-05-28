<?php
class CouponController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        $member = Auth::getMember();
        $status = $_GET['status'] ?? '0';
        $list = $this->db->fetchAll("SELECT mc.*, c.name, c.type, c.value, c.min_amount, c.start_time, c.end_time FROM " . $this->db->table('member_coupon') . " mc JOIN " . $this->db->table('coupon') . " c ON mc.coupon_id = c.id WHERE mc.member_id = :mid AND mc.status = :st ORDER BY mc.id DESC", [':mid' => $member['id'], ':st' => $status]);
        Response::success($list);
    }

    public function getAvailable()
    {
        $member = Auth::getMember();
        $amount = (float)($_GET['amount'] ?? 0);
        $list = $this->db->fetchAll("SELECT mc.*, c.name, c.type, c.value, c.min_amount, c.start_time, c.end_time FROM " . $this->db->table('member_coupon') . " mc JOIN " . $this->db->table('coupon') . " c ON mc.coupon_id = c.id WHERE mc.member_id = :mid AND mc.status = 0 AND c.min_amount <= :amount AND c.start_time <= NOW() AND c.end_time >= NOW() ORDER BY mc.id DESC", [':mid' => $member['id'], ':amount' => $amount]);
        Response::success($list);
    }

    public function postReceive()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $couponId = (int)($data['coupon_id'] ?? 0);
        $coupon = $this->db->fetch("SELECT * FROM " . $this->db->table('coupon') . " WHERE id = :id AND status = 1", [':id' => $couponId]);
        if (!$coupon) {
            Response::error('优惠券不存在');
        }
        if ($coupon['total_count'] > 0 && $coupon['used_count'] >= $coupon['total_count']) {
            Response::error('优惠券已领完');
        }
        $this->db->insert('member_coupon', [
            'coupon_id' => $couponId,
            'member_id' => $member['id'],
            'create_time' => date('Y-m-d H:i:s')
        ]);
        $this->db->execute("UPDATE " . $this->db->table('coupon') . " SET used_count = used_count + 1 WHERE id = :id", [':id' => $couponId]);
        Response::success();
    }

    public function getAll()
    {
        Auth::getAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 20;
        $total = $this->db->count('coupon', '1=1');
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('coupon') . " ORDER BY id DESC LIMIT {$offset},{$pageSize}");
        Response::page($list, $total, $page, $pageSize);
    }

    public function postCreate()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $data['create_time'] = date('Y-m-d H:i:s');
        $id = $this->db->insert('coupon', $data);
        Response::success(['id' => $id]);
    }

    public function putUpdate($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('coupon', $data, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function deleteDelete($id)
    {
        Auth::getAdmin();
        $this->db->update('coupon', ['status' => 0], 'id = :id', [':id' => $id]);
        Response::success();
    }
}
