<?php

use CaT\Plugins\ReportMaster\ReportDiscovery as RD;

// TODO: This makes the Unit Test fail on my system (rklees)
abstract class MenuItemTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	const TITLE = 'title';
	const ICON_LOC = 'icon_loc';

	/**
	 * get proper link param sample array for test
	 */
	abstract protected function lps();

	/**
	 * init and return menu item representation.
	 */
	abstract protected function invoke($title, $link_params, $icon_location);


	public function test_init()
	{
		return $this->invoke(self::TITLE, $this->lps(), self::ICON_LOC);
	}

	public function setUp()
	{

		if (!defined('IL_PHPUNIT_TEST')) {
			include_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
			\ilUnitUtil::performInitialisation();
		}
		assert_options(ASSERT_CALLBACK, function () {
			throw new Exception('assertion failed');
		});
	}

	public function tearDown()
	{
		assert_options(ASSERT_CALLBACK);
	}

	/**
	 * @depends test_init
	 */
	public function test_properties($mi)
	{
		$this->assertEquals($mi->title(), self::TITLE);
		$this->assertEquals($mi->iconLocation(), self::ICON_LOC);
		$this->assertEquals($mi->linkParameter(), $this->lps());
	}

	/**
	 * @depends test_init
	 * @expectedException Exception
	 */
	public function test_false_title()
	{
		$this->invoke(new DateTime, $this->lps(), self::ICON_LOC);
	}

	/**
	 * @depends test_init
	 * @expectedException Exception
	 */
	public function test_false_lps()
	{
		$lps = $this->lps();
		array_shift($lps);
		$this->invoke(self::TITLE, $lps, 'a');
	}

	/**
	 * @depends test_init
	 * @expectedException Exception
	 */
	public function test_false_icon_pos()
	{
		$this->invoke(self::TITLE, $this->lps(), new DateTime);
	}
}
