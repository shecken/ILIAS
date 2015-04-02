<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserHistorizingHelper
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class ilUserHistorizingHelper 
{
	/** @var int $variant Used to control predictable nonsense hash. Change to get alternative data for historizing */
	protected static $variant = 1;
	
	#region Singleton

	/** Defunct member for singleton */
	private function __clone() {}

	/** Defunct member for singleton */
	private function __construct() {}

	/** @var ilUserHistorizingHelper $instance */
	private static $instance;

	/**
	 * Singleton accessor
	 * 
	 * @static
	 * 
	 * @return ilUserHistorizingHelper
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	#endregion

	/**
	 * Returns the org-unit of the given user.
	 * 
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getOrgUnitOf($user)
	{
		return gevUserUtils::getInstanceByObjOrId($user)->getOrgUnitTitle();
	}

	/**
	 * Returns the position key of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getPositionKeyOf($user)
	{
		return null;
	}

	/**
	 * Returns the exit date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate|null
	 */
	public static function getExitDateOf($user)
	{
			return null;
	}

	/**
	 * Returns the entry date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate
	 */
	public static function getEntryDateOf($user)
	{
			return null;
	}

	/**
	 * Returns the BWV-ID of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getBWVIdOf($user)
	{
		return null;
	}

	/**
	 * Returns the entry date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate
	 */
	public static function getBeginOfCertificationPeriodOf($user)
	{
		return null;
	}

	/**
	 * Returns the OKZ of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getOKZOf($user)
	{
		return null;
	}


	/**
	 * Returns the Vermittlerstatus of the given user, calculated
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getWBDAgentStatusOf($user)
	{
		return null;
	}





	/**
	 * Returns the Type: TPService/TBBasic of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getWBDTypeOf($user)
	{
		return null;
	}




	/**
	 * Returns the Adress-data of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return array
	 */
	public static function getAddressDataOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($user);
		//$uutils = gevUserUtils::getInstance($user->user_id);
		$ret = array(
			//'street'			=> $uutils->getPrivateStreet(),
			//'zipcode'			=> $uutils->getPrivateZipcode(),
			//'city'				=> $uutils->getPrivateCity(),
			//'phone_nr'			=> $uutils->getUser()->getPhoneOffice(),
			//'mobile_phone_nr'	=> $uutils->getPrivatePhone(),

			//2014-10-14:
			'street' 	=> $user->getStreet(),
			'zipcode' 	=> $user->getZipcode(),
			'city'		=> $user->getCity(),
			'phone_nr' 	=> $user->getPhoneOffice(),
			//2014-11-17
			'mobile_phone_nr'	=> $user->getPhoneMobile()
		);
		return $ret;
	}

	/**
	 * Returns the email of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getEMailOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstanceByObjOrId($user)->getEMail();
	}


	public static function getWBDEMailOf($user)
	{
		return null;
	}



	public static function getOrgUnitsAboveOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");

		$user_utils = gevUserUtils::getInstanceByObjOrId($user);
		$tree = ilObjOrgUnitTree::_getInstance();

		$orgu_0_id = $user_utils->getOrgUnitId(); // first level

		if(! $orgu_0_id){
			return array(null, null);
		}
		$orgu_0_refid = gevObjectUtils::getRefId($orgu_0_id);
		$orgu_1_refid = $tree->getParent($orgu_0_refid);
		$orgu_2_refid = $tree->getParent($orgu_1_refid);

		$titles = $tree->getTitles(array($orgu_1_refid, $orgu_2_refid));

		$orgu_1_title = $titles[$orgu_1_refid];
		$orgu_2_title = $titles[$orgu_2_refid];
		
		//better check for level?
		$invalid =  array(
			'System Settings', 
			'__OrgUnitAdministration'
		);

		if(in_array($orgu_1_title, $invalid)){
			$orgu_1_title = null;
		}

		if(in_array($orgu_2_title, $invalid)){
			$orgu_2_title = null;
		}

		return array($orgu_1_title, $orgu_2_title);
	}
 	

 	//Vermittlernummer GEV, USR_UDF_JOB_NUMMER
	public static function getJobNumberOf($user)
	{
		return null;
	}
 	
 	//ADP-Nummer GEV, USR_UDF_ADP_NUMBER
	public static function getADPNumberOf($user)
	{
		return null;
		
	}
 	
 
	public static function isVFSOf($user){
		return null;
	}


	public static function isActiveUser($user){
		return $user->getActive();
	}


}
