<?php

require_once('./Services/Certificate/classes/class.ilCertificateAdapter.php');



/*
 * Simple class, wich only returns a course id by the method getCertificateID. All other methods are
 * inherit from the abstract basis class.
 * Its a mockclass. Only used for instantiation of an ilCertificate class wich needs an ilCartificateAdapter class. 
 */
class ilPrimitiveCertificateAdapter extends ilCertificateAdapter {
  
  function __construct($course_id) {
	$this->course_id = $course_id;
  }
  
	/**
	 * @inherit
	 */
	public function getCertificateID() {
	  return $this->course_id;
	}
  
	/**
	 * @inherit
	 */
	public function getCertificatePath(){}
	
	/**
	 * @inherit
	 */
	public function getCertificateVariablesForPreview(){}

	/**
	 * @inherit
	 */
	public function getCertificateVariablesForPresentation($params = array()){}

	/**
	 * @inherit
	 */
	public function getCertificateVariablesDescription(){}

	/**
	 * @inherit
	 */
	public function getAdapterType(){}
}