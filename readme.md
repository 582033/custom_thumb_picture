### 缩略图server
* 默认图片上传路径 `localhost/upload`,
* 配合nginx的urlrewrite,实现上传后的图片缩略图不存在时,自动生成不同尺寸的缩略图
### nginx virtual.conf

    ```server{
        listen 80;
        server_name 42.62.47.222;
        index index.html index.htm index.php;
        root /var/www/weixin/pic/;
        location ~ .*\.(jpg|png|gif)?(!\w+|\s|!\d+)$ {
            rewrite ^/(tmp\/.*)/(\d+).(jpg|png|gif)!(\d+|\w+)$ /$1/$4_$2.$3;
            if ( !-f $request_filename ) { 
                rewrite ^/(tmp\/.*)/(.*).(jpg|png|gif)$ /upload/create/$1/$2.$3 redirect;
            }   
        expires 30d;    
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
        error_log /var/log/nginx/weixin_pic_error.log;
    }```
