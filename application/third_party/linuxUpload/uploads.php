<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Uploads {
	private $files;
	private $save_path;
	private $file_names;
	private $file_sizes;
	private $file_widths;
	private $file_heights;
	private $file_types;
	private $file_infos;
	public function __construct($files, $save_path){
		$this->files = $files;
		$this->save_path = $save_path;
		$this->unique_name = $this->get_unique_name();
		$this->fileAttr2array();
		$this->move_files();
	}
	public function uploads(){	//	{{{
		echo "<pre>";
		print_r($this->files);
	}	//	}}}
	private function fileAttr2array(){	//	{{{
		$files = $this->files;
		$unique_name = $this->unique_name;
		$fileInfos = array();
		$fileInfos['name'] = $unique_name;
		$fileInfos['size'] = self::get_file_attr('size');
		$fileInfos['type'] = self::get_file_attr('type');
		$fileInfos['width'] = self::get_file_attr('width');
		$fileInfos['height'] = self::get_file_attr('height');
		$this->file_infos = $fileInfos;
	}	//	}}}
	private function get_file_attr($attr_name){	//	{{{
		$files = $this->files;
		if($attr_name == 'width'){
			$pic_attr = (getimagesize($files['tmp_name']));
			return $pic_attr[0];
		}
		elseif($attr_name == 'height'){
			$pic_attr = (getimagesize($files['tmp_name']));
			return $pic_attr[1];
		}
		else{
			return $files[$attr_name];
		}
	}	//	}}}
	private function move_files(){	//	{{{
		$files = $this->files;
		$unique_name = $this->unique_name;
		$file_infos = $this->file_infos;
		if(is_dir($this->save_path)){
			exec('mv ' . $files['tmp_name'] . ' ' .$this->save_path . "/" . $unique_name);		
		}
		else {
			exec('mkdir -p ' . $this->save_path);
			self::move_files();
		}
	}	//	}}}
	private function get_unique_name(){	// 生成对应文件数量的唯一ID作为文件名	{{{
		$files = $this->files;
		$unique_name = array();
		list(, $type) = explode('.', $files['name']);
		$unique_id = md5(time() . mt_rand(1,1000000));
		$unique_name = strtolower($unique_id.'.'.$type);
		return $unique_name;
	}	//	}}}
	public function get_file_infos(){	//	{{{
		return $this->file_infos;
	}	//	}}}
}
