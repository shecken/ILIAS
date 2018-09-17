<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessVvErstanlage.php");
class GevWBDSuccessVvErstanlageTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->row_id = 25;

		$this->success = new gevWBDSuccessVvErstanlage(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
									.'<soap:Body>'
										.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
											.'<ErstanlageRueckgabewert>'
												.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
												.'<gutberatenId>20150728-100390-74</gutberatenId>'
												.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
											.'</ErstanlageRueckgabewert>'
										.'</ns1:putResponse>'
									.'</soap:Body>'
								.'</soap:Envelope>'
						),$this->row_id, "1 - Erstanlage TP Service");
	}

	public function success_xml_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<ErstanlageRueckgabewert>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenIda>20150728-100390-74</gutberatenIda>'
										.'<AnlageDatum>2015-07-28T00:00:00+02:00</AnlageDatum>'
									.'</ErstanlageRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
					)
				)
			);
	}

	public function test_isWBDSuccessVvErstanlage() {
		$this->assertInstanceOf("gevWBDSuccessVvErstanlage",$this->success);
	}

	/**
	* @dataProvider success_xml_error
	* @expectedException LogicException
	*/
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessVvErstanlage($xml,$this->row_id, "1 - Erstanlage TP Service");
		$this->assertNotInstanceOf("gevWBDSuccessVvErstanlage",$success);
	}

	public function test_internalAgentId() {
		$this->assertInternalType("int", $this->success->internalAgentId());
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}

	public function test_beginOfCertificationPeriod() {
		$this->assertInstanceOf("ilDate", $this->success->beginOfCertificationPeriod());
	}
}