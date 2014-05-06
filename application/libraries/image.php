<?php
/**
 * Imagelib
 * 上传图片及截图辅助类
 * @author yjiang
 */

require_once APPPATH . 'third_party/phpthumb/ThumbLib.inc.php';
require_once APPPATH . 'third_party/linuxUpload/uploads.php';

class Image{
	static $thumb_path;
	static $thumb_uri;
	private $file_images;
	public function __construct(){	//{{{
		$this->obj = & get_instance();
		self::$thumb_path = FCPATH . 'tmp/';
		self::$thumb_uri = '/tmp/';
	}	//}}}
	private function _createFolder($path){	//创建不存在的目录{{{
		if (!file_exists($path)){
			self::_createFolder(dirname($path));
			mkdir($path, 0755);
		}
	}	//}}}
	public function upload_image($imagename, $filename, $upload_path) {	//{{{
	/**
	 * upload_image
	 * 上传图片
	 * @param mixed $filename,生成图片的名称
	 * @param mixed $ouput_file,输出的路径
	 * @access public
	 * @return void
	 */
		self::_createFolder($upload_path);
		$config['upload_path'] = $upload_path;		//图片目录
		$config['allowed_types'] = 'gif|jpg|png';	//允许上传图片类型
		$config['max_size'] = '1024';				//大最图片尺寸
		$config['file_name'] = $filename;			//生成图片名称
		$config['overwrite'] = true;				//强制覆盖已有图片
		$this->obj->load->library('upload', $config);	//加载upload类
		if(!$this->obj->upload->do_upload($imagename)){ 	//对应表单中input框的name
			$error_string = $this->obj->upload->display_errors();
			echo $error_string;
			exit;
		}else{
			$data = $this->obj->upload->data();
			return $data['file_name'];				//成功
		}
	}	//}}}
	public function crop_image($source_image, $x_axis, $y_axis, $width, $height, $view_width, $new_image = null){		//{{{
	/**
	 *  $source_image 要裁切图片的物理地址
	 *  $x_axis $y_axis	裁切坐标
	 *  $width $height 裁切的宽高
	 *  $view_width  页面上图片的宽度,用于取得在源图上裁切的比例
	 *  $new_image   裁切后的图片名称,默认覆盖原图
	 */
		list($real_width, $real_height) = getimagesize($source_image);
		//print_r(getimagesize($source_image));
		$ratio = $real_width / $view_width;
		if($new_image) {
			$config['new_image'] = $new_image;
		}
		else{
			$new_image = array_pop(split('/', $source_image));
		}
		$config['image_library'] = 'gd2';
		$config['source_image'] = $source_image;
		$config['maintain_ratio'] = FALSE;//保证设置的长宽有效
		$config['x_axis'] = $x_axis * $ratio;
		$config['y_axis'] = $y_axis * $ratio;
		$config['width'] = $width * $ratio;
		$config['height'] = $height * $ratio;
		$this->obj->load->library('image_lib', $config);
		if(!$this->obj->image_lib->crop()) {
			return $this->obj->image_lib->display_errors();
		}else{
			return $new_image;//裁剪完毕
		}
	}		//}}}
	private function _listFiles($dir, $uri){	//列出文件夹下所有文件{{{
		if(!is_dir($dir)) return;
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$second_dir = preg_replace('/\/+/', '/', $dir . '/' . $file);
					$second_uri = preg_replace('/\/+/', '/', $uri . '/' . $file);
					if(is_dir($second_dir)){
						self::_listFiles($second_dir, $second_uri);
					}
					else{
						$this->file_images[] = array(
								'img_uri' => $second_uri,
								);
					}
				}
			}
			closedir($handle);
		}
	}	//}}}
	public function imageTraversal($dir, $uri, $num=null){	//读取文件夹下所有图片{{{
	/**
	 *  @$dir 图片所在路径
	 *  @$uri 返回数据时,图片的uri
	 *  @$num 返回图片的数量
	 */
		$cache_key = md5('traverse_dir_' . $dir);
		$imgs = $this->obj->cache->get($cache_key);
		if(!$imgs){
			$this->file_images = array();
			self::_listFiles($dir, $uri);
			$this->obj->cache->save($cache_key, $this->file_images, 3600);
			$imgs = $this->file_images;
		}
		if($num){
			$rand_keys = array_rand($imgs, $num);
			$rand_imgs = array();
			foreach($rand_keys as $k => $v){
				$rand_imgs[$k] = $imgs[$v] ;
			}
			$imgs = $rand_imgs;
		}
		return $imgs;
	}	//}}}
	public function createThumbLocation($src, $size, $rename=null, $repath=null){	//创建本地缩略图{{{

		$file_info = pathinfo($src);				//分解文件信息
		$extension = $file_info['extension'];		//获取文件扩展名

		$uniq_key = 'thumb_' . $file_info['filename'];		        //生成缓存key
		$img_name = $rename ? $rename : $uniq_key . "." . $extension;	//生成缩略图名r

		$save_path = $repath ? $repath : self::$thumb_path;
        self::_createFolder($save_path);
		$img_file = $save_path . $img_name;

        try{
            $thumb = PhpThumbFactory::create($src);
        }
        catch (Exception $e){
            echo $e->getMessage();
            exit;
        }
        if(!empty($size)) {
            if(strpos($size, 'x')){
                list($width, $height) = explode('x', $size);
                $thumb->resize($width, $height);
            }
            else{
                $thumb->resize($size);
            }
        }
        else {
            $thumb->resizePercent(100);
        }
        $thumb->save($img_file);
		return $img_file;
	}	//}}}
	/**
	 * 计算生成图片存放目录
	 */
	function computeImageLocationPath() {	//{{{
		$seperator = '/';

        $firstDigit = substr(md5(mt_rand(1000000000, 9999999999) % 10), 6, 3);
        $secondDigit = substr(md5(mt_rand(1000000000, 9999999999) % 8), 8, 2);

		$path = $firstDigit . $seperator . $secondDigit . $seperator;
        return $path;
	}	//}}}
	/**
	 * 生成新的图片名
	 * Enter description here ...
	 * @param unknown_type $orig_name
	 */
	function generateRandomFileName($orig_name){	//{{{
		$tmp_rand = mt_rand(1000000000, 9999999999);
		$pos = strrpos($orig_name, ".");
		if($pos===false){
			return $tmp_rand.".jpg";
		}
		$extension = substr($orig_name, $pos);
		return $tmp_rand.$extension;
	}	//}}}
	public function linxUploadImage($imagename, $upload_path) {	//{{{
	/**
	 * linxUploadImage
	 * 上传图片
	 * @param mixed $filename,生成图片的名称
	 * @param mixed $ouput_file,输出的路径
	 * @access public
	 * @return void
	 */
		self::_createFolder($upload_path);
		$uploads = new uploads($_FILES[$imagename], $upload_path);
		$photo_infos = $uploads->get_file_infos();
		return $photo_infos['name'];
	}	//}}}
}
