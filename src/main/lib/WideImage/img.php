<?php
namespace WideImage;

require_once 'WideImage.php';

class img {
	static $image;
	
	static function load($source) {
		return self::$image = \WideImage::load($source);
	}

	static function unload() {
		self::$image = null;
	}
	
	static function save( $filename, $asType=null, $compression=null )
	{
		self::$image->saveToFile($filename);
	}
	 
	static function getWidth()
	{
		return self::$image->getWidth();
	}
	 
	static function getHeight()
	{
		return self::$image->getHeight();
	}
	 
	static function resizeToHeight( $height )
	{
		self::$image = self::$image->resize( null, $height );
	}
	 
	static function resizeToWidth( $width )
	{
		self::$image = self::$image->resize( $width,null );
	}
	
	static function resizeToDimension($dimension)
	{
		if ( self::$image->getWidth() >= self::$image->getHeight() )
		{
			self::$resizeToWidth( $dimension );
		}
		else
		{
			self::$resizeToHeight( $dimension );
		}
	}
	 
	static function scale( $scale )
	{
		$width = self::$getWidth() * $scale/100;
		$height = self::$getHeight() * $scale/100;
		self::$image = self::$image->resize( $width, $height );
	}
	 
	static function resize( $width, $height )
	{
		self::$image = self::$image->resize( $width, $height );
	}
	 
	static function transform( $sx, $sy, $swidth, $sheight, $dwidth, $dheight )
	{
		if ( $dwidth == null )
		{
			$dwidth = self::$getWidth();
		}
	
		if ( $dheight == null )
		{
			$dheight = self::$getHeight();
		}
	
		self::$image = self::$image->crop($sx, $sy, $swidth, $sheight)->resize($dwidth, $dheight);
	}
}