<?php

use ILIAS\TMS\Timezone;

class TimezoneCheckerTest {
	public function test_SummerTimezone() {
		$db = $this->getMockBuilder(Timezone\TimezoneDB::class)
			->setMethods(["readFor"])
			->disableOriginalConstructor()
			->getMock();

		$db
			->expects($this->once())
			->method("readFor")
			->will(array("start_summer" => "25.03.2018", "start_winter" => "28.10.2018"));

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$this->assertTrue($checker->isSummerTimeZone("25.03.2018"));
		$this->assertFalse($checker->isWinterTimeZone("25.03.2018"));
	}

	public function test_WinterTimezone() {
		$db = $this->getMockBuilder(Timezone\TimezoneDB::class)
			->setMethods(["readFor"])
			->disableOriginalConstructor()
			->getMock();

		$db
			->expects($this->once())
			->method("readFor")
			->will(array("start_summer" => "25.03.2018", "start_winter" => "28.10.2018"));

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$this->assertTrue($checker->isWinterTimeZone("28.10.2018"));
		$this->assertFalse($checker->isSummerTimeZone("28.10.2018"));
	}
}