<?php

header('Content-Type: image');

//$im=imageCreateFromAny($_GET['file_name']);
//$file_name=$_GET['file_name'];
//$im=imageCreateFromAny($file_name);
//imagejpeg($im,null,2);
//die;
$im=createThumbnail($_GET['file_name'],200,200);
imagejpeg($im,null,50);
imagedestroy($im);

die;

function createThumbnail($filepath, $thumbnail_width, $thumbnail_height, $background=false) {
    list($original_width, $original_height, $original_type) = getimagesize($filepath);
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);

    if ($original_type === 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    } else if ($original_type === 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    } else if ($original_type === 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    } else {
        return false;
    }

    $old_image = $imgcreatefrom($filepath);
    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background

    // figuring out the color for the background
    if(is_array($background) && count($background) === 3) {
      list($red, $green, $blue) = $background;
      $color = imagecolorallocate($new_image, $red, $green, $blue);
      imagefill($new_image, 0, 0, $color);
    // apply transparent background only if is a png image
    } else if($background === 'transparent' && $original_type === 3) {
      imagesavealpha($new_image, TRUE);
      $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
      imagefill($new_image, 0, 0, $color);
    }

    imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
	return $new_image;

}

function imageCreateFromAny($filepath) { 
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
    $allowedTypes = array( 
        1,  // [] gif 
        2,  // [] jpg 
        3,  // [] png 
        6   // [] bmp 
    ); 
    if (!in_array($type, $allowedTypes)) { 
        return false; 
    } 
    switch ($type) { 
        case 1 : 
            $im = imageCreateFromGif($filepath); 
        break; 
        case 2 : 
            $im = imageCreateFromJpeg($filepath); 
        break; 
        case 3 : 
            $im = imageCreateFromPng($filepath); 
        break; 
        case 6 : 
            $im = imageCreateFromBmp($filepath); 
        break; 
    } 
	return $im;  
} 
?>
