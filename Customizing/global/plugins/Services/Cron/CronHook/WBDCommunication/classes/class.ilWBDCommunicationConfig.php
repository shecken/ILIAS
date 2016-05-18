<?php
/**
 * WBDCommunication Configuration Data
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @method string siggy()
 * @method string wbd()
 * @method array actions()
 * @method array|NULL stornoRows()
 * @method array|NULL requestIds()
 * @method string configPath()
 * @method string runScript()
 * @method bool loaded()
 */
class ilWBDCommunicationConfig {
	protected $siggy;
	protected $wbd;
	protected $actions;
	protected $storno_rows;
	protected $request_ids;
	protected $config_path;
	protected $run_script;
	protected $loaded;

	private $gUser;
	private $gDB;

	const TABLE = "wbd_cron_settings";

	public function __construct() {
		global $ilUser, $ilDB;

		$this->gUser = $ilUser;
		$this->gDB = $ilDB;

		$this->storno_ids = null;
		$this->request_ids = null;
		$this->loaded = false;
	}

	/**
	 * get value of protected property
	 *
	 * @throws BadMethodCallException
	 * 
	 * @param string $name name of called function
	 * @param array $params
	 * 
	 * @return mixed $this->$name value of protected property
	 */
	final public function __call($name, $params) {
		assert('count($params) === 0');
		$name = $this->from_camel_case($name);
		
		$props = array_filter($this->protectedProperties(), function($prop) use ($name) {
			return $prop->name == $name;
		});

		if (empty($props)) {
			throw new BadMethodCallException
						("Could not call unknown getter for field '$name'");
		}
		return $this->$name;
	}

	private function from_camel_case($name) {
		return preg_replace_callback("/[A-Z]/", function ($matches) {
			return "_".strtolower($matches[0]);
		}, $name);
	}

	/**
	 * values to DB
	 */
	public function save() {
		$next_id = $this->gDB->nextId(self::TABLE);
		$storno_rows  = ($this->storno_rows === null) ? null : serialize($this->storno_rows);
		$request_ids = ($this->request_ids === null) ? null : serialize($this->request_ids);

		$query = "INSERT INTO ".self::TABLE."\n"
				." (id, siggy, wbd, actions, storno_rows, request_ids, config_path, run_script, changed_by, changed_date)\n"
				." VALUES(".$next_id
					.", ".$this->gDB->quote($this->siggy, "text")
					.", ".$this->gDB->quote($this->wbd, "text")
					.", ".$this->gDB->quote(serialize($this->actions), "text")
					.", ".$this->gDB->quote($storno_rows, "text")
					.", ".$this->gDB->quote($request_ids, "text")
					.", ".$this->gDB->quote($this->config_path, "text")
					.", ".$this->gDB->quote($this->run_script, "text")
					.", ".$this->gDB->quote($this->gUser->getId(), "integer")
					.", NOW())";

		$this->gDB->manipulate($query);
		$this->loaded = true;
	}

	/**
	 * values from DB
	 */
	public function load() {
		$query = "SELECT siggy, wbd, actions, storno_rows, request_ids, config_path, run_script\n"
				." FROM ".self::TABLE
				." ORDER BY id DESC"
				." LIMIT 1";

		$res = $this->gDB->query($query);
		if($this->gDB->numRows($res) == 0) {
			$this->loaded = false;
			return;
		}

		$row = $this->gDB->fetchAssoc($res);
		$this->setValueByArray($row);
		$this->loaded = true;
	}

	/**
	 * fill properties
	 * @param array $values
	 */
	public function setValueByArray(array $values) {
		foreach ($this->protectedProperties() as $value) {
			if(isset($values[$value->name])) {
				$val = $values[$value->name];
				if(is_string($val) && $val_un = unserialize($val)) {
					$this->{$value->name} = $val_un;
					continue;
				}

				$this->{$value->name} = $val;
			}
		}
	}

	/**
	 * create db table
	 */
	public static function setUpDB($ilDB) {
		$fields = array(
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'siggy' => array(
				'type' => 'text',
				'length' => 255,
				'notnull' => true
			),
			'wbd' => array(
				'type' => 'text',
				'length' => 255,
				'notnull' => true
			),
			'actions' => array(
				'type' => 'clob',
				'notnull' => true
			),
			'storno_rows' => array(
				'type' => 'clob',
				'notnull' => false
			),
			'request_ids' => array(
				'type' => 'clob',
				'notnull' => false
			),
			'config_path' => array(
				'type' => 'clob',
				'notnull' => true
			),
			'run_script' => array(
				'type' => 'clob',
				'notnull' => true
			),
			'changed_by' => array(
				'type' => 'text',
				'length' => 70,
				'notnull' => true
			),
			'changed_date' => array(
				'type' => 'timestamp',
				'notnull' => true
				)
		);
 
		$ilDB->createTable(self::TABLE, $fields);
		$ilDB->createSequence(self::TABLE);
	}

	/**
	 * get all protected properties
	 *
	 * @return array
	 */
	protected function protectedProperties() {
		$reflect = new ReflectionClass($this);
		return $reflect->getProperties(ReflectionProperty::IS_PROTECTED);
	}
}