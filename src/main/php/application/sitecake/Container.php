<?php
namespace sitecake;

class Container {
	public $name;
	public $global;
	public $styles;
	
	static function create() {
		return new Container;
	}
}