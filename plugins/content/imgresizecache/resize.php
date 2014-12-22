<?php
/**
 * @copyright   Copyright (C) 2013 S2 Software di Stefano Storti. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//jimport('joomla.plugin.plugin');
//jimport('joomla.html.parameter');
jimport('joomla.filesystem.folder');

class ImgResizeCache
{
	protected $imagick_process;
	protected $imagick_path_to_convert;
	protected $cache_folder;
	
	public function __construct($params = array())
	{
		// Load plugin config
		$plugin = JPluginHelper::getPlugin('content', 'imgresizecache');
		$pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry(@$plugin->params);
		$this->imagick_process = isset($params['imagick_process']) ? $params['imagick_process'] : $pluginParams->get('imagick_process', 'class');
		$this->imagick_path_to_convert = isset($params['imagick_path_to_convert']) ? $params['imagick_path_to_convert'] : $pluginParams->get('imagick_path_to_convert', 'convert');
		$this->cache_folder = isset($params['cache_folder']) ? $params['cache_folder'] : $pluginParams->get('cache_folder', 'images/cache');
		
		// Make cache folder if not exists (used by resize function)
		if (!file_exists($this->cache_folder))
		{
			//mkdir($this->cache_folder, 0777);
			JFolder::create($this->cache_folder, 0777);
		}
		if (!file_exists($this->cache_folder.'/remote'))
		{
			//mkdir($this->cache_folder.'/remote');
			JFolder::create($this->cache_folder.'/remote', 0777);
		}
	}
	
	public function resize($imagePath, $opts)
	{
		if (!$opts)
			return $imagePath;
		
		if (!$this->_checkImage($imagePath))
			return $imagePath;
		
		return $this->_resize($imagePath, $opts);
	}
	
	/**
	 * Avoid errors if image corrupted
	 * @param string $image_path
	 * @return boolean
	 */
	protected function _checkImage($imagePath)
	{
		try
		{
			@$size = getimagesize($imagePath);
			if (!$size)
				return FALSE;
			return TRUE;
		} catch (Exception $e) {
			return FALSE;
		}
	
	}
	
	// https://github.com/wes/phpimageresize
	/**
	 * function by Wes Edling .. http://joedesigns.com
	 * feel free to use this in any project, i just ask for a credit in the source code.
	 * a link back to my site would be nice too.
	 *
	 *
	 * Changes:
	 * 2012/01/30 - David Goodwin - call escapeshellarg on parameters going into the shell
	 * 2012/07/12 - Whizzkid - Added support for encoded image urls and images on ssl secured servers [https://]
	 */
	
	/**
	 * SECURITY:
	 * It's a bad idea to allow user supplied data to become the path for the image you wish to retrieve, as this allows them
	 * to download nearly anything to your server. If you must do this, it's strongly advised that you put a .htaccess file
	 * in the cache directory containing something like the following :
	 * <code>php_flag engine off</code>
	 * to at least stop arbitrary code execution. You can deal with any copyright infringement issues yourself :)
	 */
	
	/**
	 * @param string $imagePath - either a local absolute/relative path, or a remote URL (e.g. http://...flickr.com/.../ ). See SECURITY note above.
	 * @param array $opts  (w(pixels), h(pixels), crop(boolean), scale(boolean), thumbnail(boolean), maxOnly(boolean), canvas-color(#abcabc), output-filename(string), cache_http_minutes(int))
	 * @return new URL for resized image.
	 */
	protected function _resize($imagePath,$opts=null){
		$imagePath = urldecode($imagePath);
		# start configuration
		$cacheFolder = $this->cache_folder.'/'; # path to your cache folder, must be writeable by web server	// s2s: use $this->cache_folder
		$remoteFolder = $cacheFolder.'remote/'; # path to the folder you wish to download remote images into
	
		$defaults = array('crop' => false, 'scale' => false, 'thumbnail' => false, 'maxOnly' => false,
				'canvas-color' => 'transparent', 'output-filename' => false,
				'cacheFolder' => $cacheFolder, 'remoteFolder' => $remoteFolder, 'quality' => 90, 'cache_http_minutes' => 20);
		
		/*s2s - start - 'class' mode extra params */
		$defaults['bestfit'] = true;
		$defaults['fill'] = true;
		/*s2s - end */
		
		$opts = array_merge($defaults, $opts);
		
		/*s2s: maxOnly FIX - start*/
		if ($opts['maxOnly']) {
			$imagesize = getimagesize($imagePath);
			if (isset($opts['w'])) if ($opts['w'] > $imagesize[0]) $opts['w'] = $imagesize[0];
			if (isset($opts['h'])) if ($opts['h'] > $imagesize[1]) $opts['h'] = $imagesize[1];
			$opts['maxOnly'] = false;
		}
		if ($opts['crop'] && $this->imagick_process == 'exec') {	// fix crop: in some cases doesn't work (in exec mode)
			$imagesize = getimagesize($imagePath);	// 0 => width, 1 => height
			if ($imagesize[0] > $imagesize[1] && $imagesize[0]/$imagesize[1] < $opts['w']/$opts['h'] ||
				$imagesize[0] < $imagesize[1] && $imagesize[0]/$imagesize[1] > $opts['w']/$opts['h'])
			{
				$opts['crop'] = FALSE;
				$opts['resize'] = TRUE;
			}
		}
		/*s2s - end*/
		
		$cacheFolder = $opts['cacheFolder'];
		$remoteFolder = $opts['remoteFolder'];
	
		//$path_to_convert = 'convert'; # this could be something like /usr/bin/convert or /opt/local/share/bin/convert 	//s2s ORIGINALE
		$path_to_convert = $this->imagick_path_to_convert;	// s2s imagick convert path from config
	
		## you shouldn't need to configure anything else beyond this point
	
		$purl = parse_url($imagePath);
		$finfo = pathinfo($imagePath);
		$ext = $finfo['extension'];
	
		# check for remote image..
		if(isset($purl['scheme']) && ($purl['scheme'] == 'http' || $purl['scheme'] == 'https')):
		# grab the image, and cache it so we have something to work with..
		list($filename) = explode('?',$finfo['basename']);
		$local_filepath = $remoteFolder.$filename;
		$download_image = true;
		if(file_exists($local_filepath)):
		if(filemtime($local_filepath) < strtotime('+'.$opts['cache_http_minutes'].' minutes')):
		$download_image = false;
		endif;
		endif;
		if($download_image == true):
		$img = file_get_contents($imagePath);
		file_put_contents($local_filepath,$img);
		endif;
		$imagePath = $local_filepath;
		endif;
	
		if(file_exists($imagePath) == false):
		$imagePath = $_SERVER['DOCUMENT_ROOT'].$imagePath;
		if(file_exists($imagePath) == false):
		return 'image not found';
		endif;
		endif;
	
		if(isset($opts['w'])): $w = $opts['w']; endif;
		if(isset($opts['h'])): $h = $opts['h']; endif;
	
		$filename = md5_file($imagePath);
	
		// If the user has requested an explicit output-filename, do not use the cache directory.
		if(false !== $opts['output-filename']) :
			$newPath = $opts['output-filename'];
		else:
			if(!empty($w) and !empty($h)):
				$newPath = $cacheFolder.$filename.'_w'.$w.'_h'.$h.(isset($opts['crop']) && $opts['crop'] == true ? "_cp" : "").(isset($opts['scale']) && $opts['scale'] == true ? "_sc" : "").'.'.$ext;
			elseif(!empty($w)):
				$newPath = $cacheFolder.$filename.'_w'.$w.'.'.$ext;
			elseif(!empty($h)):
				$newPath = $cacheFolder.$filename.'_h'.$h.'.'.$ext;
			else:
				return false;
			endif;
		endif;
	
		$create = true;
	
		if(file_exists($newPath) == true):
			$create = false;
			// do not check time: if md5_file() it's the same, file content it's the same
			/*$origFileTime = date("YmdHis",filemtime($imagePath));
			$newFileTime = date("YmdHis",filemtime($newPath));
			if($newFileTime < $origFileTime): # Not using $opts['expire-time'] ??
				$create = true;
			endif;*/
		endif;
	
		if($create == true):
	
		if ($this->imagick_process == 'exec')	// s2s
		{
			// ORIGINAL FROM SHELL
			if(!empty($w) and !empty($h)):
	
			list($width,$height) = getimagesize($imagePath);
			$resize = $w;
	
			if($width > $height):
			$resize = $w;
			if(true == $opts['crop']):
			$resize = "x".$h;
			endif;
			else:
			$resize = "x".$h;
			if(true == $opts['crop']):
			$resize = $w;
			endif;
			endif;
	
			if(true == $opts['scale']):
			$cmd = $path_to_convert ." ". escapeshellarg($imagePath) ." -resize ". escapeshellarg($resize) .
			" -quality ". escapeshellarg($opts['quality']) . " " . escapeshellarg($newPath);
			else:
			$cmd = $path_to_convert." ". escapeshellarg($imagePath) ." -resize ". escapeshellarg($resize) .
			" -size ". escapeshellarg($w ."x". $h) .
			" xc:". escapeshellarg($opts['canvas-color']) .
			" +swap -gravity center -composite -quality ". escapeshellarg($opts['quality'])." ".escapeshellarg($newPath);
			endif;
	
			else:	// s2s fix w empty
			$cmd = $path_to_convert." " . escapeshellarg($imagePath) .
			" -thumbnail ". (!empty($w) ? $w:'') . 'x' . (!empty($h) ? $h:'') ."".
			(isset($opts['maxOnly']) && $opts['maxOnly'] == true ? "\>" : "") .
			" -quality ". escapeshellarg($opts['quality']) ." ". escapeshellarg($newPath);
			endif;
	
			$c = exec($cmd, $output, $return_code);
			if($return_code != 0) {
				error_log("Tried to execute : $cmd, return code: $return_code, output: " . print_r($output, true));
				return false;
			}
	
		}
		elseif ($this->imagick_process == 'class' && class_exists('Imagick') && extension_loaded('imagick'))	// s2s
		{
			// s2s Use Imagick class (for hosting with exec() disabled)
			if (empty($w)) $w = 0;
			if (empty($h)) $h = 0;
			
			$imagick = new Imagick(realpath($imagePath));
			$imagick->setImageCompressionQuality($opts['quality']);
			
			if (!empty($opts['canvas-color']))
				$imagick->setimagebackgroundcolor($opts['canvas-color']);
			
			if($opts['scale'] == true)
			{
				if ($w > 0 && $h > 0)
					$imagick->scaleImage($w, $h, $opts['bestfit']);
				else
					$imagick->scaleImage($w, $h);
			}
			elseif($opts['crop'] == true)
			{
				$imagick->cropThumbnailImage($w, $h);
			}
			else
			{
				if ($w > 0 && $h > 0)
					$imagick->thumbnailImage($w, $h, $opts['bestfit'], $opts['fill']);
				else
					$imagick->thumbnailImage($w, $h);
			}
			
			$imagick->writeImage(realpath($cacheFolder).'/'.str_replace($cacheFolder, '', $newPath));
			// s2s end
		}
		elseif ($this->imagick_process == 'jimage' && class_exists('JImage') && extension_loaded('gd'))	// s2s Joomla
		{
			// s2s Use Joomla JImage class (GD)
			if (empty($w)) $w = 0;
			if (empty($h)) $h = 0;
			
			// Keep proportions if w or h is not defined
			list($width, $height) = getimagesize($imagePath);
			if (!$w) $w = ($h / $height) * $width;
			if (!$h) $h = ($w / $width) * $height;
			
			// http://stackoverflow.com/questions/10842734/how-resize-image-in-joomla
			try {
				$image = new JImage($imagePath);
			} catch (Exception $e) {
				return $imagePath;	// "Attempting to load an image of unsupported type: image/x-ms-bmp"
			}
			if ($opts['crop'] == true)
			{
				$rw = $w;
				$rh = $h;
				if ($width/$height < $rw/$rh) {
					$rw = $w;
					$rh = ($rw / $width) * $height;
				}
				else {
					$rh = $h;
					$rw = ($rh / $height) * $width;
				}
				$resizedImage = $image->resize($rw, $rh)->crop($w, $h);
			}
			else
			{
				$resizedImage = $image->resize($w, $h);
			}
			
			$properties = JImage::getImageFileProperties($imagePath);
			// fix compression level must be 0 through 9 (in case of png)
			$quality = $opts['quality'];
			if ($properties->type == IMAGETYPE_PNG)
			{
				$quality = round(9 - $quality * 9/100);	// 100 quality = 0 compression, 0 quality = 9 compression
			}
			
			$resizedImage->toFile($newPath, $properties->type, array('quality' => $quality));
		}
		endif;
	
		# return cache file path
		return str_replace($_SERVER['DOCUMENT_ROOT'],'',$newPath);
	
	}
}
