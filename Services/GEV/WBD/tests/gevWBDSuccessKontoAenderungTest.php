<?php{
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessKontoAenderung.php");
class gevWBDSuccessKontoAenderungTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->row_id = 25;
		$this->success = new gevWBDSuccessKontoAenderung(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
									.'<soap:Body>'
										.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
											.'<KontoAenderungRueckgabewert>'
												.'<gutberatenId>20150728-100390-74</gutberatenId>'
												.'<Synchronisationsstatus>1</Synchronisationsstatus>'
											.'</KontoAenderungRueckgabewert>'
										.'</ns1:putResponse>'
									.'</soap:Body>'
								.'</soap:Envelope>'
						),$this->row_id);
	}

	public function success_xml_double_node() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<KontoAenderungRueckgabewert>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<Synchronisationsstatus>1</Synchronisationsstatus>'
									.'</KontoAenderungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
					)
				)
			);
	}

	public function success_xml_missing_node() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<KontoAenderungRueckgabewert>'
										.'<Synchronisationsstatus>1</Synchronisationsstatus>'
									.'</KontoAenderungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
					)
				)
			);
	}

	public function test_isWBDSuccessKontoAenderung() {
		$this->assertInstanceOf("gevWBDSuccessKontoAenderung",$this->success);
	}

	/**
	* @dataProvider success_xml_double_node
	* @expectedException LogicException
	*/
	public function test_searchedNodeMultiple($xml) {
		$success = new gevWBDSuccessVvAenderung($xml, $this->row_id);
	}

	/**
	* @dataProvider success_xml_missing_node
	*/
	public function test_searchNodeMissing($xml) {
		$success = new gevWBDSuccessKontoAenderung($xml, $this->row_id);
		$this->assertFalse($success->AgentId());
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}