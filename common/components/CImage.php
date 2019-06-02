<?php
namespace common\components;

/**  
 * 功能：php生成缩略图片的类  
 * */ 
class CImage{  
    /**
     * 生成缩略图
     * @param $srcFile (string)  参数说明：源文件路径
     * @param $size int 缩略图的大小，默认值150
     * @param $is_square bool 是否正方形，默认值false
     * @param $quality int 为可选项，范围从 0（最差质量，文件更小）到 100（最佳质量，文件最大）。默认为 IJG 默认的质量值（大约 75）。
     */
    public function create_thumbnails($srcFile, $dstFile, $size = 150, $is_square = false, $quality = 75){
        if(file_exists($srcFile)){
            //返回含有4个单元的数组，0-宽，1-高，2-图像类型，3-宽高的文本描述。 
            $data = getimagesize($srcFile);
            //将文件载入到资源变量im中 
            switch ($data[2]){  //1-GIF，2-JPG，3-PNG  
                case 1:
                    $im = imagecreatefromgif($srcFile);
                    break;
                case 2:
                    $im = imagecreatefromjpeg($srcFile);
                    break;
                case 3:
                    $im = imagecreatefrompng($srcFile);
                    break;
            }
            if(!$im){
                return false;
            }
            //设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息（与单一透明色相反）
            imagesavealpha($im, true);
            //
            $srcW = imagesx($im);
            $srcH = imagesy($im);
            $srcX = $srcY = 0;
            if($is_square == true){
                if($srcH >= $srcW){
                    $srcX = 0;
                    $srcY = floor(($srcH - $srcW) / 2);
                    $srcH = $srcW;
                }else {
                    $srcY = 0;
                    $srcX = floor(($srcW - $srcH) / 2);
                    $srcW = $srcH;
                }
                $fdstH = $fdstW = $size;
            } else {
                if ($srcW < $size && $srcH < $size) {
                    return false;
                }
                if ($srcH >= $srcW) {
                    $fdstH = $size;
                    $fdstW = $fdstH * $srcW / $srcH;
                } else {
                    $fdstW = $size;
                    $fdstH = $fdstW * $srcH / $srcW;
                }
            }
            $ni = imagecreatetruecolor($fdstW, $fdstH);
            //关闭 alpha 渲染并设置 alpha 标志
            imagealphablending($ni, false);
            imagesavealpha($ni, true);
            //重采样拷贝部分图像并调整大小
            imagecopyresampled($ni, $im, 0, 0, $srcX, $srcY, $fdstW, $fdstH, $srcW, $srcH);
            switch ($data[2]){
                case 1:
                    imagegif($ni,$dstFile);
                    break;
                case 2:
                    imagejpeg($ni,$dstFile,$quality);
                    break;
                case 3:
                    imagepng($ni,$dstFile);
                    break;
            }
            imagedestroy($im);
            imagedestroy($ni);
            
            return true;
        }
        return false;
    }

}



?>
