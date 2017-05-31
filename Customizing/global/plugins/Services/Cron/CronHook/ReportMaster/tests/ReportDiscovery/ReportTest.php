<?php


use CaT\Plugins\ReportMaster\ReportDiscovery as RD;

require_once __DIR__.'/MenuItemTest.php';

class ReportTest extends MenuItemTest
{


	/**
	 * get proper link param sample array for test
	 */
	protected function lps()
	{
		return ['type' => 'typ', 'ref_id' => 123];
	}

	/**
	 * init and return menu item representation.
	 */
	protected function invoke($title, $link_params, $icon_location)
	{
		return new RD\Report($title, $link_params, $icon_location);
	}
}
