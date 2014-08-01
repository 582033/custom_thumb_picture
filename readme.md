### 缩略图server
* 默认图片上传路径 `localhost/upload`,
* 配合nginx的urlrewrite,实现上传后的图片缩略图不存在时,自动生成不同尺寸的缩略图

### 支持的缩略方式
* http://foo.com/bar.jpg!30 #30x30,如果原图不存在会根据`bar.jpg`自动生成
* http://foo.com/bar.jpg@0x0x30x30 #{x轴}x{y轴}x{width}x{height} 对`bar.jpg`进行裁切
* http://foo.com/bar.jpg!30@0x0x30x30 在缩略图的基础上进行裁切
### nginx virtual.conf
    ```server{
            listen 80; 
            listen 5001;
            server_name pic.weixin.com 192.19.0.10:5001;                                                                                                                                                                  
            index index.html index.htm index.php;
            root /var/www/html/weixin/pic/;

            location ~ .*\.(jpg|png|gif)@(\d+x\d+x\d+x\d+)!(\w+|\d+)$ {
                rewrite ^/(tmp\/.*)/(\d+).(jpg|png|gif)@(\d+x\d+x\d+x\d+)!(\w+|\d+)$ /$1/$5_crop-$4_$2.$3;
                if ( !-f $request_filename ) { 
                    rewrite ^/(tmp\/.*)/(\d+_crop-\d+x\d+x\d+x\d+_\d+|\w+_crop-\d+x\d+x\d+x\d+_\d+).(jpg|png|gif)$ /upload/create/$1/$2.$3 redirect;
                }   
                if ( -f $request_filename ) { 
                    expires 30d;
                }   
            }   
            location ~ .*\.(jpg|png|gif)!(\w+|\d+)$ {
                rewrite ^/(tmp\/.*)/(\d+.*\d+).(jpg|png|gif)!(\d+|\w+)$ /$1/$4_$2.$3;
                if ( !-f $request_filename ) { 
                    rewrite ^/(tmp\/.*)/(\d+.*\d+).(jpg|png|gif)$ /upload/create/$1/$2.$3 redirect;
                }   
                if ( -f $request_filename ) { 
                    expires 30d;
                }   
            }   
            location ~ .*\.(jpg|png|gif)@(\d+x\d+x\d+x\d+)$ {
                rewrite ^/(tmp\/.*)/(\d+|.*_\d+).(jpg|png|gif)@(\d+x\d+x\d+x\d+)$ /$1/crop-$4_$2.$3;
                if ( !-f $request_filename ) { 
                    rewrite ^/(tmp\/.*)/crop-(\d+.*|.*\d+).(jpg|png|gif)$ /upload/crop/$1/crop-$2.$3 redirect;
                }   
                if ( -f $request_filename ) { 
                    expires 30d;
                }   
            }  

            loccation / {
                    if (!-e $request_filename) {
                        rewrite ^/(.*)$ /index.php?$1 last;
                        break;
                    }•••
                    rewrite ^/(?!index\.php|robots\.txt|tmp)(.*)$ /index.php/$1 last;
                }•••
                location ~ .*\.(php|php5)?$ {
                    fastcgi_pass 127.0.0.1:9000;
                    fastcgi_index index.php;
                    fastcgi_split_path_info ^(.+\.php)(.*)$;
                    fastcgi_param        SCRIPT_FILENAME        $document_root$fastcgi_script_name;
                    fastcgi_param        PATH_INFO                $fastcgi_path_info;
                    fastcgi_param        PATH_TRANSLATED        $document_root$fastcgi_path_info;
                    include        fastcgi_params;
                }
    }```
