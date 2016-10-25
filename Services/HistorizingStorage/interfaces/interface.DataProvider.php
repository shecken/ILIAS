<?php
/** 
 * Immutable data provider of data for historizing dependent on
 * component and event.
 */
interface DataProvider {
	public function caseId();
	public function data();
	public function creator();
	public function massAction();
}