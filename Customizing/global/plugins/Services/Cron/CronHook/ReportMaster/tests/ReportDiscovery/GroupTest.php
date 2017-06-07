<?php


use CaT\Plugins\ReportMaster\ReportDiscovery as RD;

require_once __DIR__.'/MenuItemTest.php';

class GroupTest extends MenuItemTest
{


	/**
	 * get proper link param sample array for test
	 */
	protected function lps()
	{
		return ['type' => 'typ'];
	}

	/**
	 * init and return menu item representation.
	 */
	protected function invoke($title, $link_params, $icon_location)
	{
		return new RD\Group($title, $link_params, $icon_location);
	}
}
