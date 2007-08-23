<?php

/* Config */
define('TC_FULLDIR', dirname(__FILE__) . '/');
define('TC_PASSWORD', 'changeme');
define('TC_THUMBWIDTH', 200);
define('TC_THUMBHEIGHT', 200);
define('TC_REPLYTHUMBWIDTH', 125);
define('TC_REPLYTHUMBHEIGHT', 125);
define('TC_CATTHUMBWIDTH', 50);
define('TC_CATTHUMBHEIGHT', 50);

if ($_POST['password'] != TC_PASSWORD) die('bad password');
if (!isset($_POST['type'])) die('failure');

if ($_POST['type'] == 'thumbnail' || $_POST['type'] == 'direct') {
	if (isset($_POST['file']) && isset($_POST['targetname'])) {
		if (is_file($_POST['targetname'])) die('file already exists');
		if (!@copy($_POST['file'], $_POST['targetname'])) die('unable to copy');
		
		if ($_POST['type'] == 'thumbnail') {
			if ($_POST['targetthumb'] == '' || $_POST['targetthumb_c'] == '') {
				@unlink($_POST['targetname']);
				die('failure');
			}
			$thumbloc = $_POST['targetthumb'];
			$thumbloc_c = $_POST['targetthumb_c'];
			
			$imageDim = getimagesize($_POST['targetname']);
			$imgw = $imageDim[0];
			$imgh = $imageDim[1];
			
			if (($_POST['isreply'] == '0' && ($imgw > TC_THUMBWIDTH || $imgh > TC_THUMBHEIGHT)) || ($_POST['isreply'] == '1' && ($imgw > TC_REPLYTHUMBWIDTH || $imgh > TC_REPLYTHUMBHEIGHT))) {
				if ($_POST['isreply'] == '0') {
					if (!@createThumbnail($_POST['targetname'], $thumbloc, TC_THUMBWIDTH, TC_THUMBHEIGHT)) {
						@unlink($_POST['targetname']);
						die('unable to thumbnail');
					}
				} else {
					if (!@createThumbnail($_POST['targetname'], $thumbloc, TC_REPLYTHUMBWIDTH, TC_REPLYTHUMBHEIGHT)) {
						@unlink($_POST['targetname']);
						die('unable to thumbnail');
					}
				}
			} else {
				if (!@createThumbnail($_POST['targetname'], $thumbloc, $imgw, $imgh)) {
					@unlink($_POST['targetname']);
					die('unable to thumbnail');
				}
			}
			if (!@createThumbnail($_POST['targetname'], $thumbloc_c, TC_CATTHUMBWIDTH, TC_CATTHUMBHEIGHT)) {
				@unlink($_POST['targetname']);
				die('unable to thumbnail');
			}
			
			$imageDim_thumb = getimagesize($thumbloc);
			$imgw_thumb = $imageDim_thumb[0];
			$imgh_thumb = $imageDim_thumb[1];
			
			die(serialize(array('imgw_thumb' => $imgw_thumb, 'imgh_thumb' => $imgh_thumb)));
		}
		
		die('success');
	}
} elseif ($_POST['type'] == 'delete') {
	unlink('src/' . $_POST['filename'] . '.' . $_POST['filetype']);
	unlink('thumb/' . $_POST['filename'] . 's.' . $_POST['filetype']);
	unlink('thumb/' . $_POST['filename'] . 'c.' . $_POST['filetype']);
	
	die("\n" . 'finished');
}

/**
 * @ignore
 */ 
function createThumbnail($name, $filename, $new_w, $new_h) {
	$system=explode(".", $filename);
	$system = array_reverse($system);
	if (preg_match("/jpg|jpeg/", $system[0])) {
		$src_img=imagecreatefromjpeg($name);
	} else if (preg_match("/png/", $system[0])) {
		$src_img=imagecreatefrompng($name);
	} else if (preg_match("/gif/", $system[0])) {
		$src_img=imagecreatefromgif($name);
	} else {
		return false;
	}
	
	if (!$src_img) {
		echo '<br>Unable to open the uploaded image for thumbnailing.  Maybe its a different filetype, and has the wrong extension?';
		return false;
	}
	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	if ($old_x > $old_y) {
		$percent = $new_w / $old_x;
	} else {
		$percent = $new_h / $old_y;
	}
	$thumb_w = round($old_x * $percent);
	$thumb_h = round($old_y * $percent);
	
	$dst_img=ImageCreateTrueColor($thumb_w, $thumb_h);
	fastimagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
	
	if (preg_match("/png/", $system[0])) {
		if (!imagepng($dst_img, $filename)) {
			echo 'unable to imagepng.';
			return false;
		}
	} else if (preg_match("/jpg|jpeg/", $system[0])) {
		if (!imagejpeg($dst_img, $filename, 70)) {
			echo 'unable to imagejpg.';
			return false;
		}
	} else if (preg_match("/gif/", $system[0])) {
		if (!imagegif($dst_img, $filename)) { 
			echo 'unable to imagegif.';
			return false;
		}
	}
	
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
	
	return true;
}

/* Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable. */
/**
 * @ignore
 */ 
function fastimagecopyresampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
	/*
	Optional "quality" parameter (defaults is 3).  Fractional values are allowed, for example 1.5.
	1 = Up to 600 times faster.  Poor results, just uses imagecopyresized but removes black edges.
	2 = Up to 95 times faster.  Images may appear too sharp, some people may prefer it.
	3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled.
	4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
	5 = No speedup.  Just uses imagecopyresampled, highest quality but no advantage over imagecopyresampled.
	*/
	
	if (empty($src_image) || empty($dst_image)) { return false; }

	if ($quality <= 1) {
		$temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
		imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
		imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy ($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
		
		imagecopyresized ($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
		
		imagecopyresampled ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		
		imagedestroy ($temp);
		
	} else {
		imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

	
	return true;
}

/**
 * @ignore
 */ 
function mime_content_type_custom($f) {
   return trim(exec('file -bi ' . escapeshellarg ($f )));
}

?>