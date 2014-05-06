<?php
/**
 * 加载选项文件
 */
function get_option($option_name, $dir="options"){
    $array_file = array();
    $array_option_name = explode("_",$option_name);
    $option_name_first = array_shift($array_option_name);
    $option_name_end = join("_",$array_option_name);

    $dir = APPPATH . "config/" . $dir . "/";

    if($option_name_end != ""){
        $array_file[] = $dir . $option_name_first . "/" . $option_name_end . ".inc.php";
    }
    $array_file[] = $dir . $option_name_first . "/" . $option_name . ".inc.php";
    $array_file[] = $dir . $option_name . ".inc.php";
    echo APPPATH;

    foreach($array_file as $index => $file){
        if(is_file($file) == true){
            include($file);
            return $config;
        }
    }
}
