<?php
class StatsController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getDashboard()
    {
        Auth::getAdmin();
        $today = date('Y-m-d');
        $todayStart = $today . ' 00:00:00';
        $todayEnd = $today . ' 23:59:59';

        $todayOrders = $this->db->fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(pay_amount),0) as amount FROM " . $this->db->table('order') . " WHERE create_time BETWEEN :s AND :e AND status != 4", [':s' => $todayStart, ':e' => $todayEnd]);
        $totalOrders = $this->db->fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(pay_amount),0) as amount FROM " . $this->db->table('order') . " WHERE status != 4");
        $todayMembers = $this->db->count('member', 'create_time BETWEEN :s AND :e', [':s' => $todayStart, ':e' => $todayEnd]);
        $totalMembers = $this->db->count('member', '1=1');
        $pendingOrders = $this->db->count('order', 'status IN (0,1,2)');
        $tablesInUse = $this->db->count('table', 'status = 1');

        $recentOrders = $this->db->fetchAll("SELECT o.*, m.nickname as member_name FROM " . $this->db->table('order') . " o LEFT JOIN " . $this->db->table('member') . " m ON o.member_id = m.id ORDER BY o.id DESC LIMIT 10");
        $hotDishes = $this->db->fetchAll("SELECT name, monthly_sales FROM " . $this->db->table('dish') . " WHERE status = 1 ORDER BY monthly_sales DESC LIMIT 10");

        Response::success([
            'today_orders' => (int)$todayOrders['cnt'],
            'today_amount' => (float)$todayOrders['amount'],
            'total_orders' => (int)$totalOrders['cnt'],
            'total_amount' => (float)$totalOrders['amount'],
            'today_members' => $todayMembers,
            'total_members' => $totalMembers,
            'pending_orders' => $pendingOrders,
            'tables_in_use' => $tablesInUse,
            'recent_orders' => $recentOrders,
            'hot_dishes' => $hotDishes
        ]);
    }
}
