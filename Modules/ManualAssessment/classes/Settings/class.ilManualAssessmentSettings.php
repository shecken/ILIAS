<?php
/**
 * An object carrying settings of an Manual Assessment obj
 * beyond the standart information
 */
class ilManualAssessmentSettings
{
	const DEF_CONTENT = "";
	const DEF_RECORD_TEMPLATE = "";

	/**
	 * @var	string
	 */
	protected $content;

	/**
	 * @var	string
	 */
	protected $record_template;

	/**
	 * @var $boolean
	 */
	protected $file_required;

	public function __construct(ilObjManualAssessment $mass, $content = null, $record_template = null, $file_required = false, $event_time_place_required = false)
	{
		$this->id = $mass->getId();
		$this->content = $content !== null ? $content : self::DEF_CONTENT;
		$this->record_template = $record_template !== null ? $record_template : self::DEF_RECORD_TEMPLATE;
		$this->file_required = $file_required;
		$this->event_time_place_required = $event_time_place_required;
	}

	/**
	 * Get the id of corrwsponding mass-object
	 *
	 * @return	int|string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the content of this assessment, e.g. corresponding topics...
	 *
	 * @return	string
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @return	string
	 */
	public function recordTemplate()
	{
		return $this->record_template;
	}

	/**
	 * Uploading a file in participant record is required
	 *
	 * @return boolean
	 */
	public function fileRequired()
	{
		return $this->file_required;
	}

	/**
	 * Get the value of the checkbox event_time_place_require
	 *
	 * @return	integer
	 */
	public function eventTimePlaceRequired()
	{
		return $this->event_time_place_required;
	}

	/**
	 * Set the content of this assessment, e.g. corresponding topics...
	 *
	 * @param	string	$content
	 * @return	ilManualAssessment	$this
	 */
	public function setContent($content)
	{
		assert('is_string($content)');
		$this->content = $content;
		return $this;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @param	string	$record_template
	 * @return	ilManualAssessment	$this
	 */
	public function setRecordTemplate($record_template)
	{
		assert('is_string($record_template)');
		$this->record_template = $record_template;
		return $this;
	}

	/**
	 * Set a fileupload in participant record is required
	 *
	 * @param boolean 	$file_required
	 */
	public function setFileRequired($file_required)
	{
		assert('is_bool($file_required)');
		$this->file_required = $file_required;
		return $this;
	}

	/**
	 * Set the value of the checkbox event_time_place_require
	 *
	 * @param	integer	$event_time_place_require
	 * @return	ilManualAssessment	$this
	 */
	public function setEventTimePlaceRequired($event_time_place_required)
	{
		assert('is_integer($event_time_place_required)');
		$this->event_time_place_required = $event_time_place_required;
		return $this;
	}
}
