<?php
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class GevDictionaryTest extends DictionaryTestBase {
	protected $backupGlobals = FALSE;

	const SERACH_IN_GENDER = "gender";
	const SERACH_IN_GROUP_OF_PERSONS = "group_of_persons";

	public function setUp() {
		$this->dictionary = new gevWBDDictionary();
	}
}