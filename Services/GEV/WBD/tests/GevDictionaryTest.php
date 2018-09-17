<?php
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
class GevDictionaryTest extends DictionaryTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->dictionary = new gevWBDDictionary();
	}
}