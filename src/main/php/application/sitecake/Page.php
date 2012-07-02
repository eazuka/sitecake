<?php
namespace sitecake;

class Page {

	/**
	 * Singnals if this is an external page (out of the CMS control).
	 * @var boolean
	 */
	public $external;
	
	/**
	 * The page's URI.
	 * @var string
	 */
	public $uri;
	
	/**
	 * The page's order within the navigaiton menu. A negative value means
	 * that page is not included in the navigation menu.
	 * @var int
	 */
	public $navMenuIndex;
	
	/**
	 * This value is rendered as the a-tag's title attribute in the navigation
	 * menu.
	 * @var string
	 */
	public $navTitle;
	
	/**
	 * The text label that appears in the navigation menu.
	 * @var string
	 */
	public $navDisplay;
	
	/**
	 * The page's title, alsto the HTML title tag.
	 * @var string
	 */
	public $title;
	
	/**
	 * The page's description text, the content of the description meta-tag.
	 * @var string
	 */
	public $description;
	
	/**
	 * The page's keywords that go into the keywords meta-tag.
	 * @var string
	 */
	public $keywords;
	
	static function create() {
		$page = new Page();
		$page->id = uniqid('', true);
		return $page;
	}
}