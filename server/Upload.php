<?php
class Upload
{
    public static function save($file, $subDir = 'dish')
    {
        $cfg = require __DIR__ . '/config.php';
        $uploadCfg = $cfg['upload'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('上传失败');
        }
        if ($file['size'] > $uploadCfg['max_size']) {
            Response::error('文件大小超过限制');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $uploadCfg['allow_ext'])) {
            Response::error('不支持的文件格式');
        }
        $dir = $uploadCfg['path'] . $subDir . '/' . date('Ymd') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid() . '.' . $ext;
        $path = $dir . $filename;
        move_uploaded_file($file['tmp_name'], $path);
        return '/uploads/' . $subDir . '/' . date('Ymd') . '/' . $filename;
    }
}
