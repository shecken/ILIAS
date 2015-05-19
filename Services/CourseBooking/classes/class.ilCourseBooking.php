<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course booking 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBooking
{	
	const STATUS_BOOKED = 1;
	const STATUS_WAITING = 2;
	const STATUS_CANCELLED_WITH_COSTS = 3;
	const STATUS_CANCELLED_WITHOUT_COSTS = 4;
	
	
	//
	// status
	//
	
	/**
	 * Is given status valid?
	 * 
	 * @param int $a_status
	 * @return bool
	 */
	protected static function isValidStatus($a_status)
	{
		return in_array($a_status, array(
			self::STATUS_BOOKED
			,self::STATUS_WAITING
			,self::STATUS_CANCELLED_WITH_COSTS
			,self::STATUS_CANCELLED_WITHOUT_COSTS
		));		
	}
		
	
	// 
	// crud
	// 
	
	/**
	 * Get user status (without changed info)
	 * 
	 * @param int $a_course_obj_id
	 * @return int
	 */
	public static function getUserStatus($a_course_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$sql = "SELECT status".
			" FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$res = $ilDB->fetchAssoc($set);
			return $res["status"];
		}
	}
	
	/**
	 * Get user status (with changed info)
	 * 
	 * @param int $a_course_obj_id
	 * @return array
	 */
	public static function getUserData($a_course_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$sql = "SELECT status, status_changed_by, status_changed_on".
			" FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$res = $ilDB->fetchAssoc($set);
			return $res;
		}
	}
	
	/**
	 * Set user status 
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 * @param int $a_status
	 * @return bool
	 */
	public static function setUserStatus($a_course_obj_id, $a_user_id, $a_status)
	{
		global $ilDB, $ilUser;
		
		if(!self::isValidStatus($a_status))
		{
			return false;
		}
		
		$fields = array(
			"status" => array("integer", $a_status)
			,"status_changed_by" => array("integer", $ilUser->getId())
			,"status_changed_on" => array("integer", time())
		);
		
		$old = self::getUserStatus($a_course_obj_id, $a_user_id);
		if(self::isValidStatus($old))
		{
			if($old == $a_status)
			{
				return true;
			}
			
			$primary = array(
				"crs_id" => array("integer", $a_course_obj_id)
				,"user_id" => array("integer", $a_user_id)
			);						
			$ilDB->update("crs_book", $fields, $primary);
		}
		else
		{
			$fields["crs_id"] = array("integer", $a_course_obj_id);
			$fields["user_id"] = array("integer", $a_user_id);
			
			$ilDB->insert("crs_book", $fields);
		}
				
		self::raiseEvent("setStatus", $a_course_obj_id, $a_user_id, $old, $a_status);
		
		return true;
	}
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	protected static function raiseEvent($a_event, $a_course_obj_id = null, $a_user_id = null, $old_status = null, $new_status = null)
	{
		global $ilAppEventHandler;
		
		$params = null;
		if($a_course_obj_id || $a_user_id)
		{
			$params = array();
			if($a_course_obj_id)
			{
				$params["crs_obj_id"] = $a_course_obj_id;
			}
			if($a_user_id)
			{
				$params["user_id"] = $a_user_id;
			}
			$params["old_status"] = $old_status;
			$params["new_status"] = $new_status;
		}
		
		$ilAppEventHandler->raise("Services/CourseBooking", $a_event, $params);
	}
	
	/**
	 * Delete user status 
	 * 
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	public static function deleteUserStatus($a_course_obj_id, $a_user_id)
	{
		global $ilDB;
		
		// :TODO: obsolete?
		
		$old = self::getUserStatus($a_course_obj_id, $a_user_id);
		if(self::isValidStatus($old))		
		{
			$sql = "DELETE FROM crs_book".
				" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer");
			$ilDB->manipulate($sql);
			
			self::raiseEvent("deleteStatus", $a_course_obj_id, $a_user_id, $old, null);			
		}					
	}
	
	
	// 
	// destructor
	//		
	
	/**
	 * Delete all course entries (all users!)
	 * 
	 * @param int $a_course_obj_id
	 */
	public static function deleteByCourseId($a_course_obj_id)
	{
		global $ilDB;
		
		// :TODO: unroll to raise events?
		
		$sql = "DELETE FROM crs_book".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer");
		$ilDB->manipulate($sql);
	}
	
	/**
	 * Delete all user entries (all courses!)
	 * 
	 * @param int $a_user_id
	 */
	public static function deleteByUserId($a_user_id)
	{
		global $ilDB;
		
		// :TODO: unroll to raise events?
		
		$sql = "DELETE FROM crs_book".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer");
		$ilDB->manipulate($sql);		
	}
			
	
	// 
	// info
	// 
	
	/**
	 * Validate status (1-n)
	 * 
	 * @param int|array $a_status
	 * @return array
	 */
	protected static function validateStatus($a_status)
	{		
		if(!is_array($a_status))
		{
			$a_status = array($a_status);
		}
		
		foreach($a_status as $idx => $status)
		{
			if(!self::isValidStatus($status))
			{
				unset($a_status[$idx]);
			}
		}
		
		if(sizeof($a_status))
		{		
			return $a_status;
		} 
	}
	
	/**
	 * Get users of course by status (1-n)
	 * 
	 * @param int $a_course_obj_id
	 * @param int|array $a_status
	 * @param bool $a_return_status
	 * @return array
	 */
	public static function getUsersByStatus($a_course_obj_id, $a_status, $a_return_status = false)
	{
		global $ilDB;
		
		$status = self::validateStatus($a_status);
		if(sizeof($status))
		{
			$res = array();
			
			$sql = "SELECT user_id, status".
				" FROM crs_book".
				" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer").
				" AND ".$ilDB->in("status", $status, "", "integer");
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				if($a_return_status)
				{
					$res[$row["user_id"]] = $row["status"];
				}
				else
				{
					$res[] = $row["user_id"];
				}
			}
			
			return $res;
		}
	}
	
	/**
	 * Get courses of user by status (1-n)
	 * 
	 * @param int $a_user_id
	 * @param int|array $a_status
	 * @return array
	 */
	public static function getCoursesByStatus($a_user_id, $a_status)
	{
		global $ilDB;
		
		$status = self::validateStatus($a_status);
		if(sizeof($status))
		{
			$res = array();
			
			$sql = "SELECT crs_id".
				" FROM crs_book".
				" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ".$ilDB->in("status", $status, "", "integer");
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row["crs_id"];
			}
			
			return $res;
		}
	}
	
	/**
	 * Get complete course booking data for table GUI
	 * 
	 * @param int $a_course_obj_id
	 * @param bool $a_show_cancellations
	 * @return array
	 */
	public static function getCourseTableData($a_course_obj_id, $a_show_cancellations = false)
	{
		global $ilDB;
		
		$res = array();
		
		$user_ids = array();
		
		if(!$a_show_cancellations)
		{
			$status = array(self::STATUS_BOOKED, self::STATUS_WAITING);
		}
		else
		{
			$status = array(self::STATUS_CANCELLED_WITHOUT_COSTS, self::STATUS_CANCELLED_WITH_COSTS);
		}		
		
		$sql = 	"SELECT ud.firstname AS firstname,ud.lastname AS lastname,ud.login AS login, su.login AS stcblogin,".
					" crsb.status, crsb.status_changed_on,crsb.crs_id,crsb.user_id,crsb.status_changed_by,".
					" GROUP_CONCAT(orgu.obj_id SEPARATOR '#|#') AS oguid, GROUP_CONCAT(orgu.title SEPARATOR '#|#') AS ogutitle".
		       		" FROM object_data orgu".
    		   		" INNER JOIN object_reference refr ON refr.obj_id = orgu.obj_id".
					" INNER JOIN object_data roles ON roles.title LIKE CONCAT('il_orgu_superior_',refr.ref_id) OR roles.title LIKE CONCAT('il_orgu_employee_',refr.ref_id)".
					" INNER JOIN rbac_ua rbac ON roles.obj_id = rbac.rol_id".
    				" INNER JOIN usr_data ud ON rbac.usr_id = ud.usr_id".
    				" INNER JOIN crs_book crsb ON ud.usr_id = crsb.user_id AND crsb.crs_id = ".$ilDB->quote($a_course_obj_id, "integer")." AND ".$ilDB->in("crsb.status", $status, "", "integer")."".
    				" INNER JOIN usr_data su ON su.usr_id = crsb.status_changed_by".
					" WHERE orgu.type = 'orgu' AND refr.deleted IS NULL".
					" GROUP BY ud.firstname, ud.lastname,ud.login,su.login, crsb.status, crsb.status_changed_on,crsb.crs_id,crsb.user_id,crsb.status_changed_by";

		$res = array();
		$arrIndex = 0;
		$set = $ilDB->query($sql);		
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[$arrIndex]["crs_id"] = $row["crs_id"];
			$res[$arrIndex]["user_id"] = $row["user_id"];
			$res[$arrIndex]["status"] = $row["status"];
			$res[$arrIndex]["status_changed_by"] = $row["status_changed_by"];
			$res[$arrIndex]["status_changed_on"] = $row["status_changed_on"];
			

			$res[$arrIndex]["firstname"] = $row["firstname"];
			$res[$arrIndex]["lastname"] = $row["lastname"];
			$res[$arrIndex]["login"] = $row["login"];
			
			$res[$arrIndex]["org_unit"] = explode("#|#",$row["oguid"]);
			
			$title = explode("#|#", $row["ogutitle"]);
			sort($title);
			$res[$arrIndex]["org_unit_txt"] = implode(", ",$title);
			
			$res[$arrIndex]["status_changed_by_txt"] = $row["stcblogin"];

			$arrIndex++;
		}
		
		return $res;
	}	
}