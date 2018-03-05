<?php

namespace CaT\IliasUserOrguImport\User;

use CaT\UserOrguImport\User as User;

/**
 * Locates relevant useres in ilias and provides their data.
 */
class UserLocator
{

	protected $id;
	protected $udf_w;
	protected $db;

	protected $usr_data;

	public function __construct(UserIdentifier $id, UdfWrapper $udf_w, $db)
	{
		$this->id = $id;
		$this->udf_w = $udf_w;
		$this->db = $db;
	}

	/**
	 * The user ids of all relevant users (pnr or aob set).
	 *
	 * @return	int[]
	 */
	public function relevantUserIds()
	{
		$f_ids =  [$this->udf_w->fieldId(UdfWrapper::PROP_PNR)];
		return $this->userIdsWithSetFields($f_ids);
	}

	/**
	 * The user ids of all relevant users (pnr or aob set),
	 * havin a given extern role (sammelrolle).
	 *
	 * @param	string	$extern_role
	 * @return	int[]
	 */
	public function relevantUserIdsWithExternRole($extern_role)
	{
		assert('is_string($extern_role)');

		$query = 'SELECT usr_id, value'
				.'	FROM udf_text'
				.'	WHERE field_id = '.$this->db->quote($this->udf_w->fieldId(UdfWrapper::PROP_FLAG_KU), 'integer')
				.'		AND value LIKE '.$this->db->quote('%'.$extern_role.'%', 'text')
				.'		AND '.$this->db->in('usr_id', $this->relevantUserIds(), false, 'integer');

		$res = $this->db->query($query);
		$return = [];
		while ($rec = $this->db->fetchAssoc($res)) {
			$ext_roles = preg_split('#[,;]#', $rec['value']);
			if (in_array($extern_role, $ext_roles)) {
				$return[] = (int)$rec['usr_id'];
			}
		}
		return $return;
	}

	/**
	 * IliasUser objects represeinting given ilias user ids.
	 *
	 * @param	int[]	$usr_ids
	 * @return	User\Users
	 */
	public function usersByUserIds(array $usr_ids)
	{
		$usrs = new User\Users($this->id);
		$usr_data = $this->loadUsersData($usr_ids);
		foreach ($this->udf_w->udfDataOfUsers($usr_ids) as $usr_id => $udf_data) {
			$usrs->add(new IliasUser(array_merge($udf_data, $usr_data[(int)$usr_id]), $this->id, $usr_id));
		}
		return $usrs;
	}

	/**
	 * IliasUser object represeinting given ilias user id.
	 *
	 * @param	int	$usr_id
	 * @return	IliasUser
	 */
	public function userByUserId($usr_id)
	{
		foreach ($this->usersByUserIds([$usr_id]) as $key => $value) {
			return $value;
		}
	}

	/**
	 * Get a user for a given pnr.
	 *
	 * @param	string	$pnr
	 */
	public function userIdByPNR($pnr)
	{
		assert('is_string($pnr)');
		$ret = $this->userIdBYFieldValue($pnr, $this->udf_w->fieldId(UdfWrapper::PROP_PNR));
		if ($ret) {
			return $ret;
		}
		return null;
	}

	/**
	 * Get all user_ids, for which the corrsponding users have a given entry
	 * in a given field.
	 *
	 * @param	int	$field_id
	 * @param	string	$value
	 * @return	int[]
	 */
	protected function userIdByFieldValue($value, $field_id)
	{
		assert('is_int($field_id)');
		$query = 'SELECT usr_id'
				.'	FROM udf_text'
				.'	WHERE field_id = '.$this->db->quote($field_id, 'integer')
				.'		AND value = '.$this->db->quote($value, 'text')
				.'		AND value IS NOT NULL AND value != \'\'';
		return $this->db->fetchAssoc($this->db->query($query))['usr_id'];
	}

	protected function loadUsersData(array $usr_ids)
	{
		$query = 'SELECT'
				.'	login as '.UdfWrapper::PROP_LOGIN
				.'	,lastname as '.UdfWrapper::PROP_LASTNAME
				.'	,firstname as '.UdfWrapper::PROP_FIRSTNAME
				.'	,gender as '.UdfWrapper::PROP_GENDER
				.'	,birthday as '.UdfWrapper::PROP_BIRTHDAY
				.'	,email as '.UdfWrapper::PROP_EMAIL
				.'	,usr_id'
				.'	FROM usr_data'
				.'	WHERE '.$this->db->in('usr_id', $usr_ids, false, 'integer');
		$return = [];
		$res = $this->db->query($query);
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[(int)$rec['usr_id']] = $rec;
		}
		return $return;
	}

	public function relevantUsersWithPNRs(array $pnrs)
	{
		return $this->usersByUserIds($this->relevantUserIdsWithPNRs($pnrs));
	}

	public function relevantUserIdsWithPNRs(array $pnrs)
	{
		return $this->udf_w->userIdsByPropertyValues(
			$this->udf_w->fieldId(UdfWrapper::PROP_PNR),
			$pnrs
		);
	}

	/**
	 * Get the list of all relevant Users within ilias.
	 *
	 * @return User\Users
	 */
	public function relevantUsers()
	{
		return $this->usersByUserIds($this->relevantUserIds());
	}

	/**
	 * The ilias user_ids of all users, who have a pnr != null
	 *
	 * @return	int[]
	 */
	public function userIdsWithSetPNR()
	{
		return $this->usersWithSetFields([$this->udf_w->fieldId(UdfWrapper::PROP_PNR)]);
	}

	/**
	 * The ilias user_ids of all users, who have any of the fields set
	 *
	 * @param	int[]
	 * @return	int[]
	 */
	protected function userIdsWithSetFields(array $fields)
	{
		$query = 'SELECT DISTINCT usr_id'
				.'	FROM udf_text'
				.'	WHERE '.$this->db->in('field_id', $fields, false, 'integer')
				.'		AND value IS NOT NULL AND value != \'\'';
		$res = $this->db->query($query);
		$return = [];
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['usr_id'];
		}

		return $return;
	}
}
