<?php

/**
 * @author yjiang
 * @filename upload.php
 */
class upload extends CI_Controller
{

    private $upload_path;
    private $upload_src;

    public function __construct()
    {
        parent::__construct();
        $this->upload_path = FCPATH . "tmp/";
        $this->upload_src = "/tmp/";
    }
    public function index(){//图片上传接收
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            header("HTTP/1.1 405 Method Not Allowed");exit;
        }
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //$this->image 由../autoload.php 加载 library/image.php
            $form_file_name = 'imageData';
            $file_name = time(); //生成新的图片名
            $base_path = $this->image->computeImageLocationPath();
            $save_path = $this->upload_path . $base_path;

            $file = $this->image->upload_image($form_file_name, $file_name, $save_path); //(表单文件的name, 生成的文件名, 存放基本路径)
            $img_src = $save_path . $file;
            if(!$this->input->post('thumb') === false){
                $img_thumb = $this->image->createThumbLocation($img_src, 120, null, $save_path);
            }
            echo $this->upload_src . $base_path . $file;
            exit;
        }
    }
}

?>
