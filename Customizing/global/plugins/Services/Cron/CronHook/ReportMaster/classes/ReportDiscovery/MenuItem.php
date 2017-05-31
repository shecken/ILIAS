<?php

namespace CaT\Plugins\ReportMaster\ReportDiscovery;

abstract class MenuItem
{

	protected $title;
	protected $link_params;
	protected $icon_location;

	final public function __construct($title, array $link_params, $icon_location)
	{
		assert('is_string($title)');
		assert('is_string($icon_location)');
		assert('$this->checkLinkParams($link_params)');
		$this->title = $title;
		$this->link_params = $link_params;
		$this->icon_location = $icon_location;
	}

	/**
	 * Title of menu entry. This is what will be seen.
	 *
	 * @return string
	 */
	final public function title()
	{
		return $this->title;
	}

	/**
	 * A link will belong to any entry. Any get param should be defined here.
	 */
	final public function linkParameter()
	{
		return $this->link_params;
	}

	private function checkLinkParams(array $link_params)
	{
		foreach ($this->obligatoryLinkParameters() as $value) {
			if (!array_key_exists($value, $link_params)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Defines the obligatory link parameters. Will be used for checks
	 * at object instantiation.
	 *
	 * @return	string[]
	 */
	abstract protected function obligatoryLinkParameters();

	/**
	 * Defines icon location for a menu item at rendering.
	 *
	 * @return	string
	 */
	final public function iconLocation()
	{
		return $this->icon_location;
	}
}
