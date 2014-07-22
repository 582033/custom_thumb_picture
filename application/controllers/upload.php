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
                $size = $this->input->post('size');
                $quality = $this->input->post('q');
                $size = $size ? $size : 180;
                $quality = $quality ? $quality : 80;
                $img_thumb = $this->image->createThumbLocation($img_src, $size, null, $save_path, $quality);
            }
            echo $this->upload_src . $base_path . $file;
            exit;
        }
    }

    public function create($a, $b, $c, $d){
        $img_path = "{$a}/{$b}/{$c}/";
        $img_name = $d;

        if(!$a | !$b | !$c | !$d) exit('params error:img path');

        $source_name = end(explode("_", $img_name));
        $size = reset(explode("_", $img_name));
        $source_path = FCPATH . $img_path;
        $source_img = $source_path . end(explode("_", $img_name));

        if(preg_match('/^(\d+|\w+)_crop-\d+x\d+x\d+x\d+_\d+.*$/', $d)){//支持裁切图缩略
            $crop_size = preg_replace('/^(\d+|\w+)_crop-(\d+x\d+x\d+x\d+)_\d+.*$/', '\2', $d);
            $source_img = $source_path . "crop-" . $crop_size . "_" . $source_name;
            $source_name = $source_name . "@" . $crop_size;
        }

        if(!preg_match("/^(\d+.*\d+|\w+)$/", $size)) exit('params error:img size');

        switch($size){
            case 'thumb':
                $r_size = 180;
                break;
            default:
                $r_size = $size;
        }

        $img_thumb = $this->image->createThumbLocation($source_img, $r_size, $img_name, $source_path);
        $img_src = "/" . $img_path . $source_name . "!" . $size;
        self::r301($img_src);
    }

    public function crop($a, $b, $c, $d){
        $img_path = "{$a}/{$b}/{$c}/";
        $img_name = $d;
        if(!$a | !$b | !$c | !$d) exit('params error:img path');
        $source_path = FCPATH . $img_path;

        //支持缩略图裁剪
        $resolve = explode("_", $img_name);
        if(count($resolve) <= 2){
            $source_name = end(explode("_", $img_name));
        }
        else{
            $tmp_source = array_pop($resolve);
            $tmp_size = array_pop($resolve);
            $source_name = $tmp_size . "_" . $tmp_source;
        }

        $source_img = $source_path . $source_name;
        $params = end(explode("-", reset(explode("_", $img_name))));

        if(!preg_match("/^\d+x\d+x\d+x\d+$/", $params)) exit('params error:img params');

        list($x, $y, $width, $hight) = explode("x", $params);

        $img_thumb = $this->image->crop_image($source_img, $x, $y, $width, $hight, $img_name, $source_path);
        $img_src = "/" . $img_path . $source_name . "@" . $params;
        self::r301($img_src);
    }

    private function r301($new_url){
        $http_protocol = $_SERVER['SERVER_PROTOCOL'];   //http协议版本
        $http_host = "http://" . $_SERVER['HTTP_HOST'];

        //如果是其他协议，则默认为HTTP/1.0
        if ( 'HTTP/1.1' != $http_protocol && 'HTTP/1.0' != $http_protocol ){
            $http_protocol = 'HTTP/1.0';
        }

        //响应301状态码
        header("$http_protocol 301 Moved Permanently");

        //指定重定向的URL
        header("Location:{$http_host}{$new_url}");
    }
}
/*
 * 自动生成缩略图的nginx配置
 */

/*
server{
    listen 80;
    listen 5001;
    server_name pic.weixin.com 192.19.0.10:5001;
    index index.html index.htm index.php;
    root /var/www/html/weixin/pic/;
    location ~ .*\.(jpg|png|gif)@(\d+x\d+x\d+x\d+)!(\w+|\d+)$ {
        rewrite ^/(tmp\/.*)/(\d+).(jpg|png|gif)@(\d+x\d+x\d+x\d+)!(\w+|\d+)$ /$1/$5_crop-$4_$2.$3;
        if ( !-f $request_filename ) {
            rewrite ^/(tmp\/.*)/(\d+_crop-\d+x\d+x\d+x\d+_\d+).(jpg|png|gif)$ /upload/create/$1/$2.$3 redirect;
        }
        if ( -f $request_filename ) {•
            expires 30d;
        }
    }
    location ~ .*\.(jpg|png|gif)!(\w+|\d+)$ {
        rewrite ^/(tmp\/.*)/(\d+.*\d+).(jpg|png|gif)!(\d+|\w+)$ /$1/$4_$2.$3;
        if ( !-f $request_filename ) {•
            rewrite ^/(tmp\/.*)/(\d+.*\d+).(jpg|png|gif)$ /upload/create/$1/$2.$3 redirect;
        }
        if ( -f $request_filename ) {•
            expires 30d;
        }
    }
    location ~ .*\.(jpg|png|gif)@(\d+x\d+x\d+x\d+)$ {
        rewrite ^/(tmp\/.*)/(\d+|.*_\d+).(jpg|png|gif)@(\d+x\d+x\d+x\d+)$ /$1/crop-$4_$2.$3;
        if ( !-f $request_filename ) {•
            rewrite ^/(tmp\/.*)/crop-(\d+.*|.*\d+).(jpg|png|gif)$ /upload/crop/$1/crop-$2.$3 redirect;
        }
        if ( -f $request_filename ) {•
            expires 30d;
        }
    }

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php?$1 last;
            break;
        }
        rewrite ^/(?!index\.php|robots\.txt|tmp)(.*)$ /index.php/$1 last;
    }
    location ~ .*\.(php|php5)?$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_param        SCRIPT_FILENAME        $document_root$fastcgi_script_name;
        fastcgi_param        PATH_INFO                $fastcgi_path_info;
        fastcgi_param        PATH_TRANSLATED        $document_root$fastcgi_path_info;
        include        fastcgi_params;
    }
}
*/
?>
