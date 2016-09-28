<?php
require_once 'Services/VIWIS/exceptions/class.WBTLocatorException.php';
class WBTLocator {
	const WBT_TYPE_SINGLESCO = 'singlesco';
	const WBT_TYPE_MULTISCO = 'multisco';

	public function __construct(ilDB $db) {
		$this->db = $db;
	}

	/**
	 * Get an assiciative array of question ids (like 1.2.3.4.5)
	 * to the corresponding link to open the corresponding wbt at the
	 * right spot.
	 */
	public function getRedirectLinkParametersById($slm_id, $type) {
		switch($type) {
			case self::WBT_TYPE_SINGLESCO:
				return $this->extractJumpTosSinglesco($slm_id,$this->getManifestSinglesco($slm_id));
			case self::WBT_TYPE_MULTISCO:
				return $this->extractJumpTosMultisco($slm_id,$this->getManifestMultisco($slm_id));
			default:
				throw new WBTLocatorException('unknown type '.$type);
		}
	}

	protected function extractJumpTosSinglesco($slm_id, $a_xml) {
		$return = array();
		$xml = new SimpleXMLElement($a_xml);
		$items = array();
		foreach ($xml->organizations->organization->item as $item) {
			$items[] = $item;
		}
		while ( $item = array_shift($items)) {
			$ident = $item['identifier'];
			foreach ($item->metadata->lom->general->catalogentry as $cat) {
				$q_id = current($cat->entry->langstring);
				if(!isset($return[$q_id])) {
					$return[$q_id] = $id_sco;
				}
			}
			foreach ($item->item as $sub_item) {
				array_push($items, $sub_item);
			}
		}
		return $return;
	}

	protected function extractJumpTosMultisco($slm_id, $a_xml) {
		$return = array();
		$xml = new SimpleXMLElement($a_xml);
		$items = array();
		foreach ($xml->organizations->organization->item as $item) {
			$items[] = $item;
		}
		while ( $item = array_shift($items)) {
			$ident = $item['identifier'];
			$title = $item->title[0];
			$q = 	'SELECT obj_id FROM scorm_object WHERE title = '.$this->db->quote($title,'text')
					. '	AND c_type = '.$this->db->quote('sit','text').' AND slm_id = '.$this->db->quote($slm_id,'integer');
			$res = $this->db->query($q);
			$id_sco = $this->db->fetchAssoc($res)['obj_id'];
			foreach ($item->metadata->lom->general->catalogentry as $cat) {
				$q_id = current($cat->entry->langstring);
				if(!isset($return[$q_id])) {
					$return[$q_id] = $id_sco;
				}
			}
			foreach ($item->item as $sub_item) {
				array_push($items, $sub_item);
			}
		}
		return $return;
	}

	protected function getManifestSinglesco($slm_id) {
		return file_get_contents(
					CLIENT_WEB_DIR.DIRECTORY_SEPARATOR.'lm_data'.DIRECTORY_SEPARATOR.'lm_'.$slm_id.DIRECTORY_SEPARATOR.'imsmanifest_org.xml'
				);
	}

	protected function getManifestMultisco($slm_id) {
		return file_get_contents(
					CLIENT_WEB_DIR.DIRECTORY_SEPARATOR.'lm_data'.DIRECTORY_SEPARATOR.'lm_'.$slm_id.DIRECTORY_SEPARATOR.'imsmanifest.xml'
				);
	}

	protected function storeLinkParametersToDb($ref_id, $wbt_item, $question_ref) {
		$ilDB->insert(	'viwis_refs', array(
						'ref_id' =>	array( 'integer', $ref_id),
						'wbt_item' =>	array('text', $wbt_item),
						'question_ref' =>	array('text', $question_ref)));
	}

	public function getRedirectParameterForQuestionRef($question_ref) {
		$q = 'SELECT ref_id, wbt_item FROM viwis_refs WHERE '.$this->db->quote($question_ref ,'text');
		return $this->db->fetchAssoc($this->db->query($q));
	}
}