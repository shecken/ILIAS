<?php
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestWPMeldung.php");
class GevWPMeldungTest extends RequestTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		$data = array("title"=>"Berufsunfähigkeitsversicherung 2013"
					  ,"begin_date" => "2015-12-20"
					  ,"end_date" => "2015-12-20"
					  ,"credit_points" => 5
					  ,"type" => "Virtuelles Training"
					  ,"wbd_topic" => "Privat-Vorsorge-Lebens-/Rentenversicherung"
					  ,"row_id"=>35214
					  ,"user_id"=>2323
					  ,"bwv_id" => "22332-565-321-65" 
					);

		$this->request = gevWBDRequestWPMeldung::getInstance($data);
	}

	public function test_isImplmentedRequest() {
		$this->assertInstanceOf("gevWBDRequestWPMeldung",$this->request);
	}

	public function xml_response_success() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</WPMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</WPMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
					,array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</WPMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
			);
	}

	public function xml_response_error_double_node() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPMeldungRueckgabewert>'
										.'<WBDBuchungsId>2015-145-1654</WBDBuchungsId>'
										.'<InternesPersonenkennzeichen>7665</InternesPersonenkennzeichen>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InterneBuchungsId>21352</InterneBuchungsId>'
										.'<Kontobeginn>2015-07-28T00:00:00+02:00</Kontobeginn>'
									.'</WPMeldungRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
				))
			);
	}

	public function xml_response_error() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
							.'<soap:Body>'
								.'<soap:Fault>'
									.'<faultcode>soap:Server</faultcode>'
									.'<faultstring>Der Benutzer wurde von einem anderen TP angelegt: 5702136776</faultstring>'
									.'<detail>'
										.'<ns1:ExterneDoubletteException xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/" />'
									.'</detail>'
								.'</soap:Fault>'
							.'</soap:Body>'
						.'</soap:Envelope>'
									))
			);
	}

	//Bool = False needed
	/**
     * @dataProvider xml_response_error_double_node
     * @expectedException LogicException
     */
	public function test_parseResponseXMLErrorDoubleNode($xml) {
		$this->request->createWBDSuccess($xml);
	}

	//Array needed
	/**
     * @dataProvider xml_response_success
     */
	public function test_returnWBDSuccessObject($xml) {
		$this->request->createWBDSuccess($xml);
		$this->assertInstanceOf("WBDSuccess",$this->request->getWBDSuccess());
	}

	/**
	 * @dataProvider xml_response_success
	 * @expectedException LogicException
	 */
	public function test_returnWBDErrorObjectOnSuccess($xml) {
		$this->request->createWBDSuccess($xml);
		$this->assertInstanceOf("WBDError",$this->request->getWBDError());
	}
}