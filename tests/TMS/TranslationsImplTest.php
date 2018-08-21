<?php
use ILIAS\TMS;

class TranslationsImplTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->txts = array(
			'CONST_1' => 'TXT_1',
			'CONST_2' => 'TXT_2'
		);
		$this->trans = new ILIAS\TMS\TranslationsImpl($this->txts);
	}

	public function test_construction() {
		$this->assertEquals($this->txts['CONST_1'],	$this->trans->getTxt('CONST_1'));
		$this->assertEquals($this->txts['CONST_2'],	$this->trans->getTxt('CONST_2'));
	}

	public function test_decoration() {
		$txts = array(
			'CONST_2' => 'TXT_3'
		);
		$trans = new ILIAS\TMS\TranslationsImpl($txts, $this->trans);
		$this->assertEquals('TXT_1', $trans->getTxt('CONST_1'));
		$this->assertEquals('TXT_3', $trans->getTxt('CONST_2'));
		$this->assertEquals('TXT_2', $this->trans->getTxt('CONST_2'));
	}

}
