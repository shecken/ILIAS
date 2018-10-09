<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessBildungszeitMeldung.php");
class gevWBDSuccessBildungszeitMeldungTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->success = new gevWBDSuccessBildungszeitMeldung(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<BzMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</BzMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				),'2015-06-19', 6);
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<BzMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</BzMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				)
					)
				);
	}

	public function test_isWBDSuccessWPMeldung() {
		$this->assertInstanceOf("gevWBDSuccessBildungszeitMeldung",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessBildungszeitMeldung($xml,'2015-06-19', 6);
		$this->assertNotInstanceOf("gevWBDSuccessBildungszeitMeldung",$success);
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}