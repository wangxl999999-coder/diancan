<?php
class DishController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        $categoryId = $_GET['category_id'] ?? 0;
        $keyword = $_GET['keyword'] ?? '';
        $priceMin = $_GET['price_min'] ?? 0;
        $priceMax = $_GET['price_max'] ?? 0;
        $tag = $_GET['tag'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, (int)($_GET['page_size'] ?? 20)));

        $where = "status = 1";
        $params = [];
        if ($categoryId) {
            $where .= " AND category_id = :cid";
            $params[':cid'] = $categoryId;
        }
        if ($keyword) {
            $where .= " AND name LIKE :kw";
            $params[':kw'] = "%{$keyword}%";
        }
        if ($priceMin > 0) {
            $where .= " AND price >= :pmin";
            $params[':pmin'] = $priceMin;
        }
        if ($priceMax > 0) {
            $where .= " AND price <= :pmax";
            $params[':pmax'] = $priceMax;
        }
        if ($tag) {
            $where .= " AND FIND_IN_SET(:tag, tags)";
            $params[':tag'] = $tag;
        }

        $total = $this->db->count('dish', $where, $params);
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT * FROM " . $this->db->table('dish') . " WHERE {$where} ORDER BY sort ASC, id DESC LIMIT {$offset},{$pageSize}";
        $list = $this->db->fetchAll($sql, $params);

        foreach ($list as &$item) {
            $item['tags'] = $item['tags'] ? explode(',', $item['tags']) : [];
        }
        Response::page($list, $total, $page, $pageSize);
    }

    public function getDetail($id)
    {
        $dish = $this->db->fetch("SELECT * FROM " . $this->db->table('dish') . " WHERE id = :id", [':id' => $id]);
        if (!$dish) {
            Response::error('菜品不存在');
        }
        $dish['tags'] = $dish['tags'] ? explode(',', $dish['tags']) : [];
        $dish['specs'] = $this->getSpecs($id);
        $dish['addons'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('dish_addon') . " WHERE (dish_id = :id OR dish_id = 0) AND status = 1 ORDER BY sort ASC", [':id' => $id]);
        Response::success($dish);
    }

    private function getSpecs($dishId)
    {
        $groups = $this->db->fetchAll("SELECT * FROM " . $this->db->table('dish_spec_group') . " WHERE dish_id = :id ORDER BY sort ASC", [':id' => $dishId]);
        foreach ($groups as &$group) {
            $group['options'] = $this->db->fetchAll("SELECT * FROM " . $this->db->table('dish_spec_option') . " WHERE group_id = :gid ORDER BY sort ASC", [':gid' => $group['id']]);
        }
        return $groups;
    }

    public function getAll()
    {
        Auth::getAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 20)));
        $keyword = $_GET['keyword'] ?? '';
        $categoryId = $_GET['category_id'] ?? 0;

        $where = "1=1";
        $params = [];
        if ($keyword) {
            $where .= " AND d.name LIKE :kw";
            $params[':kw'] = "%{$keyword}%";
        }
        if ($categoryId) {
            $where .= " AND d.category_id = :cid";
            $params[':cid'] = $categoryId;
        }

        $total = $this->db->count('dish d', $where, $params);
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT d.*, c.name as category_name FROM " . $this->db->table('dish') . " d LEFT JOIN " . $this->db->table('category') . " c ON d.category_id = c.id WHERE {$where} ORDER BY d.sort ASC, d.id DESC LIMIT {$offset},{$pageSize}";
        $list = $this->db->fetchAll($sql, $params);
        Response::page($list, $total, $page, $pageSize);
    }

    public function postCreate()
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $specs = $data['specs'] ?? [];
        $addons = $data['addons'] ?? [];
        unset($data['specs'], $data['addons']);

        if (is_array($data['tags'])) {
            $data['tags'] = implode(',', $data['tags']);
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $dishId = $this->db->insert('dish', $data);

        $this->saveSpecs($dishId, $specs);
        $this->saveAddons($dishId, $addons);

        Response::success(['id' => $dishId]);
    }

    public function putUpdate($id)
    {
        Auth::getAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $specs = $data['specs'] ?? [];
        $addons = $data['addons'] ?? [];
        unset($data['specs'], $data['addons']);

        if (is_array($data['tags'])) {
            $data['tags'] = implode(',', $data['tags']);
        }
        $this->db->update('dish', $data, 'id = :id', [':id' => $id]);
        $this->saveSpecs($id, $specs);
        $this->saveAddons($id, $addons);
        Response::success();
    }

    private function saveSpecs($dishId, $specs)
    {
        $this->db->delete('dish_spec_option', 'group_id IN (SELECT id FROM ' . $this->db->table('dish_spec_group') . ' WHERE dish_id = :did)', [':did' => $dishId]);
        $this->db->delete('dish_spec_group', 'dish_id = :did', [':did' => $dishId]);

        foreach ($specs as $group) {
            $groupId = $this->db->insert('dish_spec_group', [
                'dish_id' => $dishId,
                'name' => $group['name'],
                'is_required' => $group['is_required'] ?? 0,
                'sort' => $group['sort'] ?? 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
            if (!empty($group['options'])) {
                foreach ($group['options'] as $opt) {
                    $this->db->insert('dish_spec_option', [
                        'group_id' => $groupId,
                        'name' => $opt['name'],
                        'price' => $opt['price'] ?? 0,
                        'sort' => $opt['sort'] ?? 0,
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    private function saveAddons($dishId, $addons)
    {
        $this->db->delete('dish_addon', 'dish_id = :did AND dish_id != 0', [':did' => $dishId]);
        foreach ($addons as $addon) {
            $this->db->insert('dish_addon', [
                'dish_id' => $dishId,
                'name' => $addon['name'],
                'price' => $addon['price'] ?? 0,
                'sort' => $addon['sort'] ?? 0,
                'status' => $addon['status'] ?? 1,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function deleteDelete($id)
    {
        Auth::getAdmin();
        $this->db->update('dish', ['status' => 0], 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function postUpdateSales()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $this->db->execute("UPDATE " . $this->db->table('dish') . " SET monthly_sales = monthly_sales + :qty WHERE id = :id", [':qty' => $item['quantity'], ':id' => $item['dish_id']]);
        }
        Response::success();
    }
}
