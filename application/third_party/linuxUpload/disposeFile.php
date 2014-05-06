<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * disposeFile 
 * 文件处理
 * @author yjiang 
 */
class disposeFile {
	protected $file;
	public function __construct($file){	//{{{
		$this->file = $file;
	}	//}}}
	public function getFileInfo(){	//获取上传的文件信息{{{
	/**
	 * getFileInfo 
	 * 
	 * @param mixed $file 
	 * @access public
	 * @return void
	 */
		$file = $this->file;
		if($file['error'] == '1') {
			echo 'upload error - ' . json_encode($file) ;
			exit;
		}
		//todo 对文件大小进行判断
		$extension = preg_match('/\./', $file['name']) ? array_pop(explode('.', $file['name'])) : '';
		$title = preg_replace("/\.{$extension}$/", '', $file['name']);
		//todo 对文件类型进行判断
		$md5 = exec("md5sum {$file['tmp_name']} | sed 's/\s.*.$//'");
		$obj = array(
				'title' => $title,
				'extension' => strtolower($extension),
				'size' => $file['size'],
				'md5' => $md5,
				);
		return $obj;
	}	//}}}
	protected function _createFolder($path){  //创建不存在的目录{{{        
		$path = preg_replace('/\/+/', '/', $path);
		if (!file_exists($path)){
			self::_createFolder(dirname($path));
			try {
				mkdir($path, 0755);
			}
			catch (Exception $e){
				echo $e->getMessage();
				exit;
			}
		}
	}   //}}}
	private function _check_path($path){	//检查路径是否正确{{{
		if(!preg_match('/.*\/$/', $path)){
			$path .= '/';
		}
		$path = preg_replace('/\/+/', '/', $path);
		self::_createFolder($path);
		return $path;
	}	//}}}
	public function os_mv($source, $target, $rename=null){	//执行系统mv命令{{{
		$target = self::_check_path($target);
		$target = $rename ? $target . $rename : $target;	//对文件进行重命名操作
		exec("mv {$source} {$target}");
		return $target;
	}	//}}}
	public function move_to($path, $rename=null){	//转移上传的文件{{{
		$path = self::_check_path($path);
		$path = $rename ? $path . $rename : $path;	//重命名
		$source = $this->file['tmp_name'];
		exec("mv {$source} {$path}");
		return $path;
	}	//}}}
}
