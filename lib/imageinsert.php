<?php
defined('ABSPATH') or die();
class Imagescontent {
    private $images;
    private $maximg;

    public function __construct() {
        $this->images = array(); // Danh sách hình ảnh
        $this->setmaxImages(3);
    }

    // Thiết lập danh sách hình ảnh
    public function setmaxImages($maximg) {
        $this->maximg = $maximg;
    }

    public function insertImages($html,$images) {
        $this->images = $images;
        // Tìm tất cả các thẻ <h> trong chuỗi HTML
        preg_match_all('/<h[1-6]>.+?<\/h[1-6]>|<p>.+?<\/p>/', $html, $matches);

        $headers = $matches[0];
        $imageIndex = 0;

        foreach ($headers as $header) {
            if ($imageIndex < $this->maximg) {
                // Đây là một thẻ <h>, hãy chèn hình ảnh vào đây
                $imageToInsert = "\n".'<p><img src="' . $this->images[$imageIndex] . '"></p>';
                
                 $html = substr_replace($html, $imageToInsert, strpos($html, $header) + strlen($header), 0);
               
                
            } 
            $imageIndex++;
        }

        return $html;
    }
}
