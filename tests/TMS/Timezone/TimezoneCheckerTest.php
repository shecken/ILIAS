<?php

use ILIAS\TMS\Timezone;

class TimezoneCheckerTest {
	public function test_SummerTime() {
		$db = $this->getMockBuilder(Timezone\TimezoneDB::class)
			->setMethods(["readFor"])
			->disableOriginalConstructor()
			->getMock();

		$start_summer = new DateTime(strtotime("25.03.2018"));
		$start_winter = new DateTime(strtotime("28.10.2018"));

		$db
			->expects($this->exactly(6))
			->method("readFor")
			->will(array("start_summer" => $start_summer, "start_winter" => $start_winter));

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$checktime = new DateTime(strtotime("25.03.2018"));
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));

		$checktime = new DateTime(strtotime("27.10.2018"));
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));

		$checktime = new DateTime(strtotime("19.06.2018"));
		$this->assertTrue($checker->isSummerTime($checktime));
		$this->assertFalse(!$checker->isSummerTime($checktime));
	}

	public function test_WinterTime() {
		$db = $this->getMockBuilder(Timezone\TimezoneDB::class)
			->setMethods(["readFor"])
			->disableOriginalConstructor()
			->getMock();

		$start_summer = new DateTime(strtotime("25.03.2018"));
		$start_winter = new DateTime(strtotime("28.10.2018"));

		$db
			->expects($this->exactly(6))
			->method("readFor")
			->will(array("start_summer" => $start_summer, "start_winter" => $start_winter));

		$checker = new Timezone\TimezoneCheckerImpl($db);
		$checktime = new DateTime(strtotime("28.10.2018"));
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));

		$checktime = new DateTime(strtotime("31.12.2018"));
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));

		$checktime = new DateTime(strtotime("03.10.2018"));
		$this->assertTrue(!$checker->isSummerTime($checktime));
		$this->assertFalse($checker->isSummerTime($checktime));
	}
}