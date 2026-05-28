<?php
class Response
{
    public static function json($code = 0, $msg = 'success', $data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = [], $msg = 'success')
    {
        self::json(0, $msg, $data);
    }

    public static function error($msg = 'error', $code = 1, $data = [])
    {
        self::json($code, $msg, $data);
    }

    public static function page($list, $total, $page, $pageSize)
    {
        self::success([
            'list' => $list,
            'total' => (int)$total,
            'page' => (int)$page,
            'page_size' => (int)$pageSize,
            'total_pages' => ceil($total / $pageSize)
        ]);
    }
}
