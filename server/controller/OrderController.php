<?php
class OrderController
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
        $tableId = (int)($data['table_id'] ?? 0);
        $tableNo = $data['table_no'] ?? '';
        $orderType = (int)($data['order_type'] ?? 1);
        $remark = $data['remark'] ?? '';
        $items = $data['items'] ?? [];
        $couponId = (int)($data['coupon_id'] ?? 0);
        $contactName = $data['contact_name'] ?? '';
        $contactPhone = $data['contact_phone'] ?? '';
        $address = $data['address'] ?? '';
        $deliveryFee = (float)($data['delivery_fee'] ?? 0);

        if (empty($items)) {
            Response::error('请选择菜品');
        }

        $this->db->beginTransaction();
        try {
            $orderId = 0;
            $isMerged = 0;

            if ($orderType == 1 && $tableId) {
                $table = $this->db->fetch("SELECT * FROM " . $this->db->table('table') . " WHERE id = :id", [':id' => $tableId]);
                if (!$table) {
                    throw new Exception('桌位不存在');
                }
                if ($table['current_order_id']) {
                    $existOrder = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :oid AND status IN (0,1,2)", [':oid' => $table['current_order_id']]);
                    if ($existOrder) {
                        $orderId = $existOrder['id'];
                        $isMerged = 1;
                    }
                }
                if (!$orderId) {
                    $this->db->update('table', ['status' => 1], 'id = :id', [':id' => $tableId]);
                }
            }

            $totalAmount = 0;
            $itemData = [];
            foreach ($items as $item) {
                $dish = $this->db->fetch("SELECT * FROM " . $this->db->table('dish') . " WHERE id = :id", [':id' => $item['dish_id']]);
                if (!$dish) continue;
                $specPrice = 0;
                $addonPrice = 0;
                if (!empty($item['addon_info'])) {
                    $addons = is_string($item['addon_info']) ? json_decode($item['addon_info'], true) : $item['addon_info'];
                    if (is_array($addons)) {
                        foreach ($addons as $a) {
                            $addonPrice += (float)($a['price'] ?? 0);
                        }
                    }
                }
                if (!empty($item['spec_info'])) {
                    $specs = is_string($item['spec_info']) ? json_decode($item['spec_info'], true) : $item['spec_info'];
                    if (is_array($specs)) {
                        foreach ($specs as $s) {
                            $specPrice += (float)($s['price'] ?? 0);
                        }
                    }
                }
                $unitPrice = $dish['price'] + $specPrice + $addonPrice;
                $subtotal = $unitPrice * $item['quantity'];
                $totalAmount += $subtotal;
                $itemData[] = [
                    'dish_id' => $dish['id'],
                    'dish_name' => $dish['name'],
                    'dish_image' => $dish['image'],
                    'price' => $dish['price'],
                    'quantity' => $item['quantity'],
                    'spec_info' => is_string($item['spec_info'] ?? '') ? $item['spec_info'] : json_encode($item['spec_info'] ?? [], JSON_UNESCAPED_UNICODE),
                    'addon_info' => is_string($item['addon_info'] ?? '') ? $item['addon_info'] : json_encode($item['addon_info'] ?? [], JSON_UNESCAPED_UNICODE),
                    'addon_price' => $addonPrice,
                    'remark' => $item['remark'] ?? ''
                ];
            }

            if (empty($itemData)) {
                throw new Exception('有效菜品为空');
            }

            $discountAmount = 0;
            if ($couponId) {
                $coupon = $this->db->fetch("SELECT mc.*, c.* FROM " . $this->db->table('member_coupon') . " mc JOIN " . $this->db->table('coupon') . " c ON mc.coupon_id = c.id WHERE mc.id = :mid AND mc.member_id = :uid AND mc.status = 0", [':mid' => $couponId, ':uid' => $member['id']]);
                if ($coupon) {
                    if ($coupon['type'] == 1) {
                        $discountAmount = (float)$coupon['value'];
                    } elseif ($coupon['type'] == 2) {
                        $discountAmount = $totalAmount * (1 - (float)$coupon['value']);
                    } elseif ($coupon['type'] == 3) {
                        $discountAmount = (float)$coupon['value'];
                    }
                    if ($discountAmount > $totalAmount) {
                        $discountAmount = $totalAmount;
                    }
                }
            }

            $payAmount = $totalAmount - $discountAmount + $deliveryFee;

            if ($orderId) {
                $this->db->update('order', [
                    'total_amount' => $existOrder['total_amount'] + $totalAmount,
                    'pay_amount' => $existOrder['pay_amount'] + $payAmount,
                    'is_merged' => 1,
                    'update_time' => date('Y-m-d H:i:s')
                ], 'id = :id', [':id' => $orderId]);

                $subId = $this->db->insert('order_sub', [
                    'order_id' => $orderId,
                    'member_id' => $member['id'],
                    'sub_amount' => $payAmount,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            } else {
                $orderNo = 'DC' . date('YmdHis') . mt_rand(1000, 9999);
                $orderId = $this->db->insert('order', [
                    'order_no' => $orderNo,
                    'table_id' => $tableId,
                    'table_no' => $tableNo,
                    'order_type' => $orderType,
                    'status' => 0,
                    'pay_type' => 0,
                    'pay_mode' => 0,
                    'total_amount' => $totalAmount,
                    'pay_amount' => $payAmount,
                    'discount_amount' => $discountAmount,
                    'remark' => $remark,
                    'member_id' => $member['id'],
                    'is_merged' => 0,
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'address' => $address,
                    'delivery_fee' => $deliveryFee,
                    'kitchen_status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);

                if ($tableId && $orderType == 1) {
                    $this->db->update('table', ['current_order_id' => $orderId, 'status' => 1], 'id = :id', [':id' => $tableId]);
                }

                $subId = 0;
            }

            foreach ($itemData as $item) {
                $this->db->insert('order_item', array_merge($item, [
                    'order_id' => $orderId,
                    'sub_order_id' => $subId,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]));
            }

            if ($couponId && isset($coupon)) {
                $this->db->update('member_coupon', ['status' => 1, 'use_time' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $couponId]);
            }

            $pointsRate = 1;
            $points = (int)($payAmount * $pointsRate);
            if ($points > 0) {
                $this->db->execute("UPDATE " . $this->db->table('member') . " SET points = points + :p WHERE id = :id", [':p' => $points, ':id' => $member['id']]);
                $this->db->insert('points_log', [
                    'member_id' => $member['id'],
                    'order_id' => $orderId,
                    'points' => $points,
                    'type' => 1,
                    'remark' => '消费获得积分',
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
            Response::success(['order_id' => $orderId, 'order_no' => $this->db->fetch("SELECT order_no FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $orderId])['order_no'], 'total_amount' => $totalAmount, 'pay_amount' => $payAmount, 'discount_amount' => $discountAmount, 'points' => $points]);
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function getList()
    {
        $member = Auth::getMember();
        $status = $_GET['status'] ?? '';
        $orderType = $_GET['order_type'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, (int)($_GET['page_size'] ?? 10)));

        $where = "member_id = :mid";
        $params = [':mid' => $member['id']];
        if ($status !== '') {
            $where .= " AND status = :st";
            $params[':st'] = $status;
        }
        if ($orderType !== '') {
            $where .= " AND order_type = :ot";
            $params[':ot'] = $orderType;
        }

        $total = $this->db->count('order', $where, $params);
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('order') . " WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$pageSize}", $params);

        foreach ($list as &$order) {
            $order['items'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('order_item') . " WHERE order_id = :oid", [':oid' => $order['id']]);
        }
        Response::page($list, $total, $page, $pageSize);
    }

    public function getDetail($id)
    {
        $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $id]);
        if (!$order) {
            Response::error('订单不存在');
        }
        $order['items'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('order_item') . " WHERE order_id = :oid", [':oid' => $id]);
        $order['sub_orders'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('order_sub') . " WHERE order_id = :oid", [':oid' => $id]);
        $order['payments'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('payment') . " WHERE order_id = :oid", [':oid' => $id]);
        Response::success($order);
    }

    public function getAll()
    {
        Auth::getAdmin();
        $status = $_GET['status'] ?? '';
        $orderType = $_GET['order_type'] ?? '';
        $keyword = $_GET['keyword'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 20)));

        $where = "1=1";
        $params = [];
        if ($status !== '') {
            $where .= " AND o.status = :st";
            $params[':st'] = $status;
        }
        if ($orderType !== '') {
            $where .= " AND o.order_type = :ot";
            $params[':ot'] = $orderType;
        }
        if ($keyword) {
            $where .= " AND (o.order_no LIKE :kw OR o.contact_name LIKE :kw2)";
            $params[':kw'] = "%{$keyword}%";
            $params[':kw2'] = "%{$keyword}%";
        }

        $total = $this->db->count('order o', $where, $params);
        $offset = ($page - 1) * $pageSize;
        $list = $this->db->fetchAll("SELECT o.*, m.nickname as member_name FROM " . $this->db->table('order') . " o LEFT JOIN " . $this->db->table('member') . " m ON o.member_id = m.id WHERE {$where} ORDER BY o.id DESC LIMIT {$offset},{$pageSize}", $params);

        foreach ($list as &$order) {
            $order['items'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('order_item') . " WHERE order_id = :oid", [':oid' => $order['id']]);
        }
        Response::page($list, $total, $page, $pageSize);
    }

    public function putStatus($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $status = (int)($data['status'] ?? 0);
        $update = ['status' => $status];

        if ($status == 1) {
            $update['pay_time'] = date('Y-m-d H:i:s');
        }
        if ($status == 4) {
            $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $id]);
            if ($order && $order['table_id']) {
                $this->db->update('table', ['status' => 0, 'current_order_id' => 0], 'id = :id', [':id' => $order['table_id']]);
            }
        }
        if ($status == 3) {
            $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $id]);
            if ($order && $order['table_id']) {
                $this->db->update('table', ['status' => 0, 'current_order_id' => 0], 'id = :id', [':id' => $order['table_id']]);
            }
        }

        $this->db->update('order', $update, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function putKitchen($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('order', ['kitchen_status' => $data['kitchen_status'] ?? 0], 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function postPay()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($data['order_id'] ?? 0);
        $payType = (int)($data['pay_type'] ?? 1);
        $payMode = (int)($data['pay_mode'] ?? 0);

        $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $orderId]);
        if (!$order) {
            Response::error('订单不存在');
        }
        if ($order['status'] != 0) {
            Response::error('订单状态异常');
        }

        if ($payType == 2) {
            $this->db->update('order', ['pay_type' => 2, 'pay_mode' => $payMode, 'status' => 1], 'id = :id', [':id' => $orderId]);
            $this->db->insert('payment', [
                'order_id' => $orderId,
                'member_id' => $member['id'],
                'pay_amount' => $order['pay_amount'],
                'pay_type' => 2,
                'status' => 1,
                'create_time' => date('Y-m-d H:i:s')
            ]);
            Response::success(['pay_type' => 2]);
        }

        $wxPay = new WxPay();
        $result = $wxPay->createOrder($order['order_no'], $order['pay_amount'], '智慧点餐-订单支付', $member['openid'] ?? '');
        if ($result['code'] !== 0) {
            Response::error($result['msg'] ?? '支付创建失败');
        }

        $this->db->update('order', ['pay_type' => 1, 'pay_mode' => $payMode], 'id = :id', [':id' => $orderId]);
        Response::success($result['data']);
    }

    public function postNotify()
    {
        $xml = file_get_contents('php://input');
        $wxPay = new WxPay();
        $data = $wxPay->verifyNotify($xml);
        if (!$data) {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code></xml>';
            exit;
        }
        $orderNo = $data['out_trade_no'];
        $transactionId = $data['transaction_id'];
        $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE order_no = :no", [':no' => $orderNo]);
        if ($order && $order['status'] == 0) {
            $this->db->update('order', ['status' => 1, 'pay_type' => 1, 'pay_time' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $order['id']]);
            $this->db->insert('payment', [
                'order_id' => $order['id'],
                'member_id' => $order['member_id'],
                'transaction_id' => $transactionId,
                'pay_amount' => $order['pay_amount'],
                'pay_type' => 1,
                'status' => 1,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
    }

    public function postAaPay()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($data['order_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);

        $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id", [':id' => $orderId]);
        if (!$order) {
            Response::error('订单不存在');
        }

        $subOrder = $this->db->fetch("SELECT * FROM " . $this->db->table('order_sub') . " WHERE order_id = :oid AND member_id = :mid AND pay_status = 0", [':oid' => $orderId, ':mid' => $member['id']]);
        if (!$subOrder) {
            $subOrder = ['sub_amount' => $amount];
        }

        $wxPay = new WxPay();
        $result = $wxPay->createOrder($order['order_no'] . '_AA_' . $member['id'], $subOrder['sub_amount'], '智慧点餐-AA付款', $member['openid'] ?? '');
        if ($result['code'] !== 0) {
            Response::error($result['msg'] ?? '支付创建失败');
        }
        Response::success($result['data']);
    }

    public function postConfirm()
    {
        $member = Auth::getMember();
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($data['order_id'] ?? 0);
        $order = $this->db->fetch("SELECT * FROM " . $this->db->table('order') . " WHERE id = :id AND member_id = :mid", [':id' => $orderId, ':mid' => $member['id']]);
        if (!$order) {
            Response::error('订单不存在');
        }
        if ($order['order_type'] == 2) {
            $this->db->update('order', ['status' => 3], 'id = :id', [':id' => $orderId]);
        }
        Response::success();
    }
}
