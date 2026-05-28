<?php
class UploadController
{
    public function postImage()
    {
        Auth::getAdmin();
        if (empty($_FILES['file'])) {
            Response::error('请选择文件');
        }
        $subDir = $_GET['type'] ?? 'dish';
        $path = Upload::save($_FILES['file'], $subDir);
        Response::success(['url' => $path]);
    }
}
