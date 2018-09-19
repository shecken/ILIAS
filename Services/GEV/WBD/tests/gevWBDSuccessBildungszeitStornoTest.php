<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessBildungszeitStorno.php");
class gevWBDSuccessBildungszeitStornoTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;	

	public function setUp() {
		$this->row_id = 25;
		$this->success = new gevWBDSuccessBildungszeitStorno(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPStornoRueckgabewert>'
															.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
															.'<gutberatenId>20150728-100390-74</gutberatenId>'
															.'<InternesPersonenkennzeichen>21352</InternesPersonenkennzeichen>'
															.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
														.'</WPStornoRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									),$this->row_id);
	}

	public function test_isWBDSuccessVvAenderung() {
		$this->assertInstanceOf("gevWBDSuccessBildungszeitStorno",$this->success);
	}

	public function test_extractedValuesFromXML() {
		$this->assertEquals("2015-145-1654", $this->success->wbdBookingId());
		$this->assertEquals("20150728-100390-74", $this->success->agentId());
		$this->assertEquals("21352", $this->success->internalAgentId());
		$this->assertInstanceOf(ilDate::class, $this->success->beginOfCertificationPeriod());
	}
}