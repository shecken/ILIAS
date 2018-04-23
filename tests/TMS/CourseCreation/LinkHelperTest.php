<?php

use ILIAS\TMS\CourseCreation\LinkHelper;
use ILIAS\TMS\CourseCreation\Request;
use PHPUnit\Framework\TestCase;

class LinkHelperMock {
	use  LinkHelper;

	protected function getCtrl() {
	}

	protected function getLng() {
	}

	protected function getUser() {
	}

	protected function sendInfo() {
	}

	public function _maybeShowRequestInfo() {
		return $this->maybeShowRequestInfo();
	}

	public function _getUsersDueRequests($user, $plugin = null) {
		return $this->getUsersDueRequests($user, $plugin);
	}

	public function _getTrainingTitleByRequest(\ILIAS\TMS\CourseCreation\Request $request) {
		return $this->getTrainingTitleByRequest($request);
	}
}


/**
 * @group needsInstalledILIAS
 */
class LinkHelperTest extends TestCase {
	public static function setUpBeforeClass() {
		require_once("./Services/User/classes/class.ilObjUser.php");
		require_once("./Services/Language/classes/class.ilLanguage.php");

		if(file_exists(
				"./Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/classes/class.ilCourseCreationPlugin.php"
			)
		) {
			require_once("./Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/classes/class.ilCourseCreationPlugin.php");
		}
	}

	public function test__noOpenRequests() {
		$usr = $this->getMockBuilder(ilObjUser::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(array("getUsersDueRequests", "getUser", "getCourseCreationPlugin", "sendInfo"))
			->getMock();

		$link_helper->expects($this->never())
			->method("sendInfo");

		$link_helper->expects($this->once())
			->method("getUsersDueRequests")
			->will($this->returnValue(array()));

		$link_helper->expects($this->once())
			->method("getCourseCreationPlugin")
			->will($this->returnValue(null));

		$link_helper->expects($this->once())
			->method("getUser")
			->will($this->returnValue($usr));

		$this->assertNull($link_helper->_maybeShowRequestInfo());
	}

	public function test_openRequests() {
		$txt_message = "This is the user info";

		$lng = $this->getMockBuilder(ilLanguage::class)
			->setMethods(array("txt"))
			->disableOriginalConstructor()
			->getMock();

		$lng->expects($this->once())
			->method("txt")
			->will($this->returnValue($txt_message));

		$usr = $this->getMockBuilder(ilObjUser::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(
				array(
					"getUsersDueRequests"
					, "getUser"
					, "getCourseCreationPlugin"
					, "sendInfo"
					, "getTrainingTitleByRequest"
					, "getLng"
				)
			)
			->getMock();

		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->disableOriginalConstructor()
			->getMock();

		$link_helper->expects($this->once())
			->method("getUsersDueRequests")
			->will($this->returnValue(array($request)));

		$link_helper->expects($this->once())
			->method("getCourseCreationPlugin")
			->will($this->returnValue(null));

		$link_helper->expects($this->once())
			->method("getTrainingTitleByRequest");

		$link_helper->expects($this->once())
			->method("sendInfo")
			->with($this->equalTo($txt_message));

		$link_helper->expects($this->once())
			->method("getLng")
			->will($this->returnValue($lng));

		$link_helper->expects($this->once())
			->method("getUser")
			->will($this->returnValue($usr));

		$this->assertTrue($link_helper->_maybeShowRequestInfo());
	}

	public function test_openRequestsWithPlugin() {
		if(class_exists("ilCourseCreationPlugin")) {

			$xccr_plugin = $this->getMockBuilder(ilCourseCreationPlugin::class)
				->setMethods(array("getActions"))
				->disableOriginalConstructor()
				->getMock();

			$txt_message = "This is the user info";
			$lng = $this->getMockBuilder(ilLanguage::class)
				->setMethods(array("txt"))
				->disableOriginalConstructor()
				->getMock();

			$lng->expects($this->once())
				->method("txt")
				->will($this->returnValue($txt_message));

			$usr = $this->getMockBuilder(ilObjUser::class)
				->disableOriginalConstructor()
				->getMock();

			$link_helper = $this->getMockBuilder(LinkHelperMock::class)
				->setMethods(
					array(
						"getUsersDueRequests"
						, "getUser"
						, "getCourseCreationPlugin"
						, "sendInfo"
						, "getTrainingTitleByRequest"
						, "getLng"
					)
				)
				->getMock();

			$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
				->disableOriginalConstructor()
				->getMock();

			$link_helper->expects($this->once())
				->method("getUsersDueRequests")
				->will($this->returnValue(array($request)));

			$link_helper->expects($this->once())
				->method("getCourseCreationPlugin")
				->will($this->returnValue($xccr_plugin));

			$link_helper->expects($this->once())
				->method("getTrainingTitleByRequest");

			$link_helper->expects($this->once())
				->method("sendInfo")
				->with($this->equalTo($txt_message));

			$link_helper->expects($this->once())
				->method("getLng")
				->will($this->returnValue($lng));

			$link_helper->expects($this->once())
				->method("getUser")
				->will($this->returnValue($usr));

			$this->assertTrue($link_helper->_maybeShowRequestInfo());
		}
	}

	public function test_noUsersDueRequestsBecauseOfNoPlugin() {
		$usr = $this->getMockBuilder(ilObjUser::class)
			->setMethods(array("getId"))
			->disableOriginalConstructor()
			->getMock();

		$usr->expects($this->once())
			->method("getId")
			->will($this->returnValue(2));

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(array("getCachedRequests", "setCachedRequests"))
			->getMock();

		$link_helper->expects($this->once())
			->method("getCachedRequests")
			->will($this->returnValue(null));

		$this->assertEquals(array(), $link_helper->_getUsersDueRequests($usr));
	}

	public function test_usersDueRequestsCached() {
		$usr = $this->getMockBuilder(ilObjUser::class)
			->setMethods(array("getId"))
			->disableOriginalConstructor()
			->getMock();

		$usr->expects($this->once())
			->method("getId")
			->will($this->returnValue(2));

		$link_helper = $this->getMockBuilder(LinkHelperMock::class)
			->setMethods(array("getCachedRequests", "setCachedRequests"))
			->getMock();

		$link_helper->expects($this->once())
			->method("getCachedRequests")
			->will($this->returnValue(array()));

		$this->assertEquals(array(), $link_helper->_getUsersDueRequests($usr));
	}

	public function test_usersDueRequestsFromPlugin() {
		if(class_exists("ilCourseCreationPlugin")) {
			$xccr_plugin = $this->getMockBuilder(ilCourseCreationPlugin::class)
				->setMethods(array("getActions"))
				->disableOriginalConstructor()
				->getMock();

			$actions = $this->getMockBuilder(ilActions::class)
				->setMethods(array("getDueRequestsOf"))
				->disableOriginalConstructor()
				->getMock();

			$actions->expects($this->once())
				->method("getDueRequestsOf")
				->will($this->returnValue(array()));

			$xccr_plugin->expects($this->once())
				->method("getActions")
				->will($this->returnValue($actions));

			$usr = $this->getMockBuilder(ilObjUser::class)
				->setMethods(array("getId"))
				->disableOriginalConstructor()
				->getMock();

			$usr->expects($this->exactly(3))
				->method("getId")
				->will($this->returnValue(2));

			$link_helper = $this->getMockBuilder(LinkHelperMock::class)
				->setMethods(array("getCachedRequests", "setCachedRequests"))
				->getMock();

			$link_helper->expects($this->exactly(2))
				->method("getCachedRequests")
				->will($this->onConsecutiveCalls(null, array()));

			$link_helper->expects($this->once())
				->method("setCachedRequests")
				->with(
					$this->equalTo(2),
					$this->equalTo(array())
				);

			$this->assertEquals(array(), $link_helper->_getUsersDueRequests($usr, $xccr_plugin));
		}
	}

	public function test_getTrainingTitleByRequest() {
		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->setMethods(array("getConfigurations"))
			->disableOriginalConstructor()
			->getMock();

		$request->expects($this->once())
			->method("getConfigurations")
			->will($this->returnValue(array(array(array("title" => "Course Title")))));

		$link_helper = new LinkHelperMock();
		$this->assertEquals("Course Title", $link_helper->_getTrainingTitleByRequest($request));
	}

	public function test_getTrainingTitleByRequestException() {
		$request = $this->getMockBuilder(\ILIAS\TMS\CourseCreation\Request::class)
			->setMethods(array("getConfigurations"))
			->disableOriginalConstructor()
			->getMock();

		$request->expects($this->once())
			->method("getConfigurations")
			->will($this->returnValue(array()));

		$link_helper = new LinkHelperMock();
		$thrown = false;
		try {
			$title = $link_helper->_getTrainingTitleByRequest($request);
		} catch(\RuntimeException $e) {
			$thrown = true;
		}

		$this->assertTrue($thrown);
	}
}