<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPStorno.php");
class GevWBDSuccessWPStornoTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->row_id = 25;
		$this->success = new gevWBDSuccessWPStorno(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
												.'<soap:Body>'
													.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
														.'<WPStornoRueckgabewert>'
															.'<WeiterbildungsPunkteBuchungsId>2015-145-1654</WeiterbildungsPunkteBuchungsId>'
															.'<VermittlerId>20150728-100390-74</VermittlerId>'
															.'<InterneVermittlerId>21352</InterneVermittlerId>'
															.'<BeginnErstePeriode>2015-07-28T00:00:00+02:00</BeginnErstePeriode>'
														.'</WPStornoRueckgabewert>'
													.'</ns1:putResponse>'
												.'</soap:Body>'
											.'</soap:Envelope>'
									),$this->row_id);
	}

	public function test_isWBDSuccessVvAenderung() {
		$this->assertInstanceOf("gevWBDSuccessWPStorno",$this->success);
	}

	public function test_extractedValuesFromXML() {
		$this->assertEquals("2015-145-1654", $this->success->wbdBookingId());
		$this->assertEquals("20150728-100390-74", $this->success->agentId());
		$this->assertEquals("21352", $this->success->internalAgentId());
		$this->assertInstanceOf(ilDate::class, $this->success->beginOfCertificationPeriod());
	}
}