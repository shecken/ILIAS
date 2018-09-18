<?php
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessBildungAbfrage.php");
class gevWBDSuccessBildungAbfrageTest extends SuccessTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		$this->success = new gevWBDSuccessBildungAbfrage(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
								.'<soap:Body>'
									.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
										.'<WPAbfrageRueckgabewert>'
											.'<gutberatenId>20150728-100390-74</gutberatenId>'
											.'<InternesPersonenkennzeichen>21352</InternesPersonenkennzeichen>'
											.'<ZertifizierungsPeriode>003</ZertifizierungsPeriode>'
											.'<BeginnErsteZertifizierungsperiode>2015-07-28T00:00:00+02:00</BeginnErsteZertifizierungsperiode>'
											.'<WeiterbildungsBuchungListe>'
												.'<WBDBuchungsId>2015-07-28</WBDBuchungsId>'
												.'<BuchendeOrganisationsId>123456785</BuchendeOrganisationsId>'
												.'<AnlegerNutzerId>321654</AnlegerNutzerId>'
												.'<NameBildungsmaßnahme>Aus doof mach Schlau</NameBildungsmaßnahme>'
												.'<Bildungszeit>00:30</Bildungszeit>'
												.'<BuchungsDatum>2015-07-28T00:00:00+02:00</BuchungsDatum>'
												.'<SeminarsDatumVon>2015-07-28T00:00:00+02:00</SeminarsDatumVon>'
												.'<SeminarDatumBis>2015-07-28T00:00:00+02:00</SeminarDatumBis>'
												.'<LernArt>004</LernArt>'
												.'<LernInhalt>005</LernInhalt>'
												.'<InterneBuchungsId>21501</InterneBuchungsId>'
												.'<Stornierdt>false</Stornierdt>'
												.'<StornoOrganisationsId></StornoOrganisationsId>'
												.'<StornoNutzerId></StornoNutzerId>'
												.'<StornoDatum></StornoDatum>'
												.'<Korrekturbuchung>false</Korrekturbuchung>'
												.'<gutberatenId>20150728-100390-74</gutberatenId>'
												.'<BasiertAufWBDBuchungsId>2015-07-28</BasiertAufBuchungsId>'
												.'<Hinweis>Nix zu weisen</Hinweis>'
												.'<ansprechpartnerDatenId>225</AnsprechpartnerDatenId>'
												.'<OrganisationsName>225</OrganisationsName>'
											.'</WeiterbildungsBuchungListe>'
											.'<GesamtzeitKJ>20</GesamtzeitKJ>'
											.'<GesamtzeitZertifizierungsperiode>20</GesamtzeitZertifizierungsperiode>'
											.'<GesamtzeitAllerZertifizierungsperioden>20</GesamtzeitAllerZertifizierungsperioden>'
										.'</WPAbfrageRueckgabewert>'
									.'</ns1:putResponse>'
								.'</soap:Body>'
							.'</soap:Envelope>'
					),10);
	}

	public function success_xml_error_double_node() {
		return array(array(simplexml_load_string('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope">'
							.'<soap:Body>'
								.'<ns1:putResponse xmlns:ns1="http://erstanlage.stammdaten.external.service.wbd.gdv.de/">'
									.'<WPAbfrageRueckgabewert>'
										.'<gutberatenId>20150728-100390-74</gutberatenId>'
										.'<InternesPersonenkennzeichen>21352</InternesPersonenkennzeichen>'
										.'<ZertifizierungsPeriode>003</ZertifizierungsPeriode>'
										.'<BeginnErsteZertifizierungsperiode>2015-07-28T00:00:00+02:00</BeginnErsteZertifizierungsperiode>'
										.'<WeiterbildungsBuchungListe>'
											.'<WBDBuchungsId>2015-07-28</WBDBuchungsId>'
											.'<BuchendeOrganisationsId>123456785</BuchendeOrganisationsId>'
											.'<AnlegerNutzerId>321654</AnlegerNutzerId>'
											.'<NameBildungsmaßnahme>Aus doof mach Schlau</NameBildungsmaßnahme>'
											.'<Bildungszeit>00:30</Bildungszeit>'
											.'<BuchungsDatum>2015-07-28T00:00:00+02:00</BuchungsDatum>'
											.'<SeminarsDatumVon>2015-07-28T00:00:00+02:00</SeminarsDatumVon>'
											.'<SeminarDatumBis>2015-07-28T00:00:00+02:00</SeminarDatumBis>'
											.'<LernArt>004</LernArt>'
											.'<LernArt>004</LernArt>'
											.'<LernInhalt>005</LernInhalt>'
											.'<InterneBuchungsId>21501</InterneBuchungsId>'
											.'<Stornierdt>false</Stornierdt>'
											.'<StornoOrganisationsId></StornoOrganisationsId>'
											.'<StornoNutzerId></StornoNutzerId>'
											.'<StornoDatum></StornoDatum>'
											.'<Korrekturbuchung>false</Korrekturbuchung>'
											.'<gutberatenId>20150728-100390-74</gutberatenId>'
											.'<BasiertAufWBDBuchungsId>2015-07-28</BasiertAufBuchungsId>'
											.'<Hinweis>Nix zu weisen</Hinweis>'
											.'<ansprechpartnerDatenId>225</AnsprechpartnerDatenId>'
											.'<OrganisationsName>225</OrganisationsName>'
										.'</WeiterbildungsBuchungListe>'
										.'<GesamtzeitKJ>20</GesamtzeitKJ>'
										.'<GesamtzeitZertifizierungsperiode>20</GesamtzeitZertifizierungsperiode>'
										.'<GesamtzeitAllerZertifizierungsperioden>20</GesamtzeitAllerZertifizierungsperioden>'
									.'</WPAbfrageRueckgabewert>'
								.'</ns1:putResponse>'
							.'</soap:Body>'
						.'</soap:Envelope>'
					)
				)
			);
	}

	public function test_isWPAbfrage() {
		$this->assertInstanceOf("gevWBDSuccessBildungAbfrage",$this->success);
	}

	/**
	* @dataProvider success_xml_error_double_node
	* @expectedException LogicException
	*/
<<<<<<< HEAD:Services/GEV/WBD/tests/GevWBDSuccessWPAbfrageTest.php
	public function test_xml_error_double_node($xml) {
		$success = new gevWBDSuccessWPAbfrage($xml,10);
		$this->assertNotInstanceOf("gevWBDSuccessWPAbfrage",$success);
=======
	public function test_cantCreateSuccessObject($xml) {
		$success = new gevWBDSuccessBildungAbfrage($xml,10);
		$this->assertNotInstanceOf("gevWBDSuccessBildungAbfrage",$success);
>>>>>>> f1b04d4ae5... Rename tests:Services/GEV/WBD/tests/gevWBDSuccessBildungAbfrageTest.php
	}

	public function test_agentId() {
		$this->assertInternalType("string", $this->success->agentId());
	}
}