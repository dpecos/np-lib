<?
class NP_Image {
    private $original_image = null;

    function __construct($src_image) {
        if (is_string($src_image)) {
            if (NP_endsWith(".jpg", $src_image) || NP_endsWith(".jpeg", $src_image)) {
                $this->original_image = imagecreatefromjpeg($src_image);
            } else if (NP_endswith(".png", $src_image)) {
                $this->original_image = imagecreatefrompng($src_image);
            }
        } else {
            $this->original_image = $src_image;
        }
    }

    function resizeXY($w, $h) {
        $old_w = imageSX($this->original_image);
        $old_h = imageSY($this->original_image);

        $new_image = ImageCreateTrueColor($w,$h);
        imagecopyresampled($new_image, $this->original_image, 0, 0, 0, 0, $w, $h, $old_w, $old_h); 
    
        return new NP_Image($new_image);
    }

    function resizeMaxSize($size) {
        $old_x = imageSX($this->original_image);
        $old_y = imageSY($this->original_image);

        if ($old_x >= $old_y) {
            $new_x = $size;
            $new_y = $old_y * ($size/$old_x);
        } else {
            $new_x = $old_x * ($size/$old_y);
            $new_y = $size;
        }

        return $this->resizeXY($new_x, $new_y);

    }

    function resizePercent($percent) {
    }

    function save($path) {
        $file = fopen($path, 'w');
        fclose($file);

        if (NP_endsWith(".jpg", $path) || NP_endsWith($path, ".jpeg", $path)) {
            return imagejpeg($this->original_image, $path);
        } else if (NP_endswith(".png", $path)) {
            return imagepng($this->original_image, $path);
        }
    }

    function close() {
        if ($this->original_image != null)
            imagedestroy($this->original_image);
        $this->original_image = null;
    }
}
