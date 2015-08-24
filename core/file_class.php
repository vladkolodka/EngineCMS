<?php
class File{
    public static function uploadIMG($file, $max_size, $dir, $root = false, $source_file = false){
        $blackList = array("php", "phtml", "php3", "php4", "html", "htm");
        $whiteList = array("jpg", "jpeg", "png", "gif");

        foreach ($blackList as $disabledExtension)
            if(preg_match("/\.$disabledExtension\$/i", $file["name"])) throw new Exception("ERROR_IMAGE_TYPE");

        $type = $file["type"];
        $size = $file["size"];

        $allow = false;
        foreach ($whiteList as $allowedExtention)
            if($type == "image/" . $allowedExtention) {
                $allow = true;
                break;
            }
        if(!$allow) throw new Exception("ERROR_IMAGE_TYPE");
        if($size > $max_size) throw new Exception("ERROR_IMAGE_SIZE");

        if($source_file) $image_name = $file["name"];
        else $image_name = self::getName() . '.' . str_replace("image/", '', $type);

        $upload_dir = $dir . $image_name;
        if(!$root) $upload_dir = $_SERVER["DOCUMENT_ROOT"] . $upload_dir;

        if(!move_uploaded_file($file["tmp_name"], $upload_dir)) throw new Exception("UNKNOWN_ERROR");
        return $image_name;
    }
    public static function getName(){
        return md5(uniqid('', true));
    }
    public static function deleteFile($file_name, $root = false){
        if(!$root) $file_name = $_SERVER["DOCUMENT_ROOT"] . $file_name;

        if(file_exists($file_name)) unlink($file_name);
    }
    public static function isExists($file_name, $root){
        if(!$root) $file_name = $_SERVER["DOCUMENT_ROOT"] . $file_name;
        return file_exists($file_name);
    }
}