<?php

namespace CaT\IliasUserOrguImport\Orgu;


/**
 * Deals with the AMD of Orgunits.
 */
class OrguAMDWrapper
{
	public function __construct(OrguConfig $orgu_config, $db)
	{
		$this->config = $orgu_config;
		$this->db = $db;
	}

	/**
	 * Amd field id for a keyword.
	 *
	 * @param	string	$keyword
	 * @return	inst|null
	 */
	public function fieldId($keyword)
	{
		assert('is_string($keyword)');
		return $this->amd_field_ids[$keyword];
	}


	/**
	 * Possible keywords for amd.
	 *
	 * @return	string[]
	 */
	public static function possibleAmdKeywords()
	{
		return [];
	}

	/**
	 * Konfigured keywords.
	 *
	 * @return	string[]
	 */
	public function availiableAmdKeywords()
	{
		return array_intersect(self::possibleAmdKeywords(), $this->keywords());
	}

	/**
	 * All keywords.
	 *
	 * @return	string[]
	 */
	protected function keywordsAll()
	{
		return [
			self::PROP_ID,
			self::PROP_TITLE
		];
	}

	/**
	 * @param	ilObjOrgu
	 * @return	mixed[string]
	 */
	public function getAmdPropertiesOfOrgu($orgu)
	{
		$return = [];
		$id = $orgu->getId();
		$res = $this->db->query(
			'SELECT field_id,value'
			.'	FROM adv_md_values_text'
			.'	WHERE obj_id = '.$this->db->quote($id, 'integer')
			.'		AND sub_type = '.$this->db->quote('orgu_type', 'text')
			.'		AND sub_id = '.$this->db->quote($this->orgu_type, 'integer')
		);
		while ($rec = $this->db->fetchAssoc($res)) {
			$keyword = $this->keyword((int)$rec['field_id']);
			if ($keyword !== null) {
				$return[$keyword] = (string)$rec['value'];
			}
		}
		return $return;
	}

	/**
	 * @param	ilObjOrgu
	 * @param	mixed[string]
	 */
	public function setAmdPropertiesOfOrgu($orgu, array $properties)
	{
		$id = $orgu->getId();
		$this->db->manipulate(
			'DELETE FROM adv_md_values_text'
			.'	WHERE obj_id = '.$this->db->quote($id, 'integer')
			.'		AND sub_type = '.$this->db->quote('orgu_type', 'text')
			.'		AND sub_id = '.$this->db->quote($this->orgu_type, 'integer')
		);
		foreach ($properties as $keyword => $value) {
			$field_id = $this->fieldId($keyword);
			if ($field_id !== null) {
				$this->db->replace(
					'adv_md_values_text',
					[
						'obj_id' => ['integer',$id],
						'field_id' => ['integer',$field_id],
						'sub_type' => ['text','orgu_type'],
						'sub_id' => ['integer',$this->orgu_type]
					],
					[
						'value' => ['text',$value],
						'disabled' => ['integer',0]
					]
				);
			}
		}
	}


	/**
	 * All ids => keywords.
	 *
	 * @return	string[]
	 */
	public function keywords()
	{
		return $this->amd_keywords;
	}


	/**
	 * All keywords => ids.
	 *
	 * @return	int[string]
	 */
	public function ids()
	{
		return $this->amd_field_ids;
	}

	/**
	 * Keyword for an id.
	 *
	 * @param	int	$id
	 * @return	string
	 */
	public function keyword($id)
	{
		assert('is_int($id)');
		return $this->amd_keywords[$id];
	}

	const PROP_ID = 'xuoi_orgu_amd_prop_id';
	const PROP_TITLE = 'xuoi_orgu_amd_prop_title';

}
