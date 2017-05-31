<?php

use CaT\Plugins\ReportMaster\ReportDiscovery as RD;

// TODO: This makes the Unit Test fail on my system (rklees)
class MenuItemCollectionTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	public function setUp()
	{

		if (!defined('IL_PHPUNIT_TEST')) {
			include_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
			\ilUnitUtil::performInitialisation();
		}
	}

	public function test_init()
	{
		return new RD\MenuItemCollection();
	}

	/**
	 * @depends test_init
	 */
	public function test_add_item($mic)
	{
		return $mic->withMenuItem($this->getMenuItem('and_a_one'));
	}

	/**
	 * @depends test_add_item
	 */
	public function test_add_more_items($mic)
	{
		return $mic
				->withMenuItem($this->getMenuItem('and_a_two'))
				->withMenuItem($this->getMenuItem('and_a_three'));
	}

	/**
	 * @depends test_add_more_items
	 */
	public function test_iterate($mic)
	{
		$items = [];
		foreach ($mic as $key => $value) {
			$items[$value->title()] = $value;
		}
		$this->assertCount(3, $items);
		$this->assertArrayHasKey('and_a_one', $items);
		$this->assertArrayHasKey('and_a_two', $items);
		$this->assertArrayHasKey('and_a_three', $items);
		$items = [];
		foreach ($mic as $key => $value) {
			$items[$value->title()] = $value;
		}
		$this->assertCount(3, $items);
		$this->assertArrayHasKey('and_a_one', $items);
		$this->assertArrayHasKey('and_a_two', $items);
		$this->assertArrayHasKey('and_a_three', $items);
	}

	protected function getMenuItem($title)
	{
		return new RD\Report($title, ['ref_id' => 1, 'type' => 'type'], 'a');
	}
}
