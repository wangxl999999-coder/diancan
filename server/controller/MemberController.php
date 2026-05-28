<?php
class MemberController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function postWxLogin()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'] ?? '';
        if (!$code) {
            Response::error('code不能为空');
        }
        $cfg = require __DIR__ . '/../config.php';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$cfg['wx']['appid']}&secret={$cfg['wx']['secret']}&js_code={$code}&grant_type=authorization_code";
        $res = json_decode(file_get_contents($url), true);
        if (!isset($res['openid'])) {
            Response::error('微信登录失败');
        }
        $openid = $res['openid'];
        $member = $this->db->fetch("SELECT * FROM " . $this->db->table('member') . " WHERE openid = :oid", [':oid' => $openid]);
        if (!$member) {
            $memberId = $this->db->insert('member', [
                'openid' => $openid,
                'unionid' => $res['unionid'] ?? '',
                'nickname' => $data['nickname'] ?? '微信用户',
                'avatar' => $data['avatar'] ?? '',
                'create_time' => date('Y-m-d H:i:s')
            ]);
            $member = $this->db->fetch("SELECT * FROM " . $this->db->table('member') . " WHERE id = :id", [':id' => $memberId]);
        }
        $token = Auth::generateToken([
            'type' => 'member',
            'id' => $member['id'],
            'openid' => $member['openid'],
            'exp' => time() + 86400 * 30
        ]);
        Response::success(['token' => $token, 'member' => $this->formatMember($member)]);
    }

    public function postPhoneLogin()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $phone = $data['phone'] ?? '';
        $code = $data['code'] ?? '';
        if (!$phone || !$code) {
            Response::error('手机号和验证码不能为空');
        }
        $member = $this->db->fetch("SELECT * FROM " . $this->db->table('member') . " WHERE phone = :phone", [':phone' => $phone]);
        if (!$member) {
            $memberId = $this->db->insert('member', [
                'phone' => $phone,
                'nickname' => '用户' . substr($phone, -4),
                'create_time' => date('Y-m-d H:i:s')
            ]);
            $member = $this->db->fetch("SELECT * FROM " . $this->db->table('member') . " WHERE id = :id", [':id' => $memberId]);
        }
        $token = Auth::generateToken([
            'type' => 'member',
            'id' => $member['id'],
            'openid' => $member['openid'],
            'exp' => time() + 86400 * 30
        ]);
        Response::success(['token' => $token, 'member' => $this->formatMember($member)]);
    }

    public function getInfo()
    {
        $member = Auth::getMember();
        $info = $this->db->fetch("SELECT * FROM " . $this->db->table('member') . " WHERE id = :id", [':id' => $member['id']]);
        if (!$info) {
            Response::error('用户不存在');
        }
        Response::success($this->formatMember($info));
    }

    public function putUpdate()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $allowFields = ['nickname', 'avatar', 'phone'];
        $update = [];
        foreach ($allowFields as $f) {
            if (isset($data[$f])) {
                $update[$f] = $data[$f];
            }
        }
        if ($update) {
            $this->db->update('member', $update, 'id = :id', [':id' => $member['id']]);
        }
        Response::success();
    }

    public function getPoints()
    {
        $member = Auth::getMember();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 20;
        $total = $this->db->count('points_log', 'member_id = :mid', [':mid' => $member['id']]);
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('points_log') . " WHERE member_id = :mid ORDER BY id DESC LIMIT {$offset},{$pageSize}", [':mid' => $member['id']]);
        Response::page($list, $total, $page, $pageSize);
    }

    public function getAll()
    {
        Auth::getAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 20)));
        $keyword = $_GET['keyword'] ?? '';

        $where = "1=1";
        $params = [];
        if ($keyword) {
            $where .= " AND (nickname LIKE :kw OR phone LIKE :kw2)";
            $params[':kw'] = "%{$keyword}%";
            $params[':kw2'] = "%{$keyword}%";
        }

        $total = $this->db->count('member', $where, $params);
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('member') . " WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$pageSize}", $params);
        Response::page($list, $total, $page, $pageSize);
    }

    private function formatMember($member)
    {
        return [
            'id' => $member['id'],
            'nickname' => $member['nickname'],
            'avatar' => $member['avatar'],
            'phone' => $member['phone'],
            'points' => (int)$member['points'],
            'level' => (int)$member['level']
        ];
    }
}
