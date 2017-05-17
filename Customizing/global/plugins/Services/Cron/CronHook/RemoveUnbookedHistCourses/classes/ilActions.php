<?php

namespace CaT\Plugins\RemoveUnbookedHistCourses;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{

	/**
	 * @var \ilRemoveUnbookedHistCoursesPlugin
	 */
	protected $plugin;

	/**
	 * @var ilDB
	 */
	protected $data_base;

	public function __construct(\ilRemoveUnbookedHistCoursesPlugin $plugin, ilDB $data_base)
	{
		$this->plugin = $plugin;
		$this->data_base = $data_base;
	}

	/**
	 * Get crs id of deleted courses withour members
	 *
	 * @return int[]
	 */
	public function getCourseToDelete()
	{
		return $this->data_base->getCourseToDelete();
	}

	/**
	 * Marks every entry of course as historic.
	 *
	 * @param int 	$crs_id
	 *
	 * @return null
	 */
	public function markCourseHistoric($crs_id)
	{
		assert('is_int($crs_id)');
		$this->data_base->markCourseHistoric($crs_id);
	}
}
