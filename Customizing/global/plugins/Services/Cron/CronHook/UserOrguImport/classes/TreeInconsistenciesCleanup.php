<?php

namespace CaT\IliasUserOrguImport;

class TreeInconsistenciesCleanup
{
	protected $db;
	public function __construct(\ilDB $db, Orgu\OrguConfig $oc)
	{
		$this->db = $db;
		$this->oc = $oc;
	}

	public function cleanupTreeInconsistencies()
	{
		// first delete all the objects that landed in thrash somehow
		$this->deleteObjectsInThrash();
		// now delete anything in the tree, that lacks an object
		$this->removeNonExistingObjectsInTree();
	}

	public function deleteObjectsInThrash()
	{
		$ref_ids = $this->getRecursiveObjectsInThrash();
		foreach ($ref_ids as $ref_id) {
			$obj = \ilObjectFactory::getInstanceByRefId($ref_id)->delete();
		}
		if (count($ref_ids) > 0) {
			$this->db->manipulate('DELETE FROM tree WHERE '.$this->db->in('child', $ref_ids, false, 'integer'));
		}
	}

	protected function getRecursiveObjectsInThrash()
	{
		$import_path = $this->importTreePath();
		if ($import_path === null) {
			return [];
		}
		$q = 'SELECT ref_id, path'
			.'	FROM object_data'
			.'	JOIN object_reference USING(obj_id)'
			.'  JOIN tree ON ref_id = child'
			.'	WHERE path LIKE '.$this->db->quote($import_path.'.%', 'text')
			.'		AND (deleted IS NOT NULL OR tree < 0)';
		$return = [];
		$res = $this->db->query($q);
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['ref_id'];
			$return = array_merge($return, $this->getUndeletedSubobjects($rec['path']));
		}
		return array_unique($return);
	}

	public function getUndeletedSubobjects($path)
	{
		$q = 'SELECT ref_id'
			.'	FROM object_data'
			.'	JOIN object_reference USING(obj_id)'
			.'  JOIN tree ON ref_id = child'
			.'	WHERE path LIKE '.$this->db->quote($path.'.%', 'text')
			.'		AND deleted IS NULL';
		$return = [];
		$res = $this->db->query($q);
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['ref_id'];
		}
		return $return;
	}

	public function removeNonExistingObjectsInTree()
	{
		$children = $this->locateNonexistingObjectsInTree();
		var_dump($children);
		if (count($children) > 0) {
			$this->db->manipulate('DELETE FROM tree WHERE '.$this->db->in('child', $children, false, 'integer'));
			$this->db->manipulate('DELETE FROM object_reference WHERE '.$this->db->in('ref_id', $children, false, 'integer'));
		}
	}

	protected function importTreePath()
	{
		$q = 'SELECT path FROM tree'
			.'	WHERE child = '
			.$this->db->quote($this->oc->getRootRefId(), 'integer');
		$res = $this->db->query($q);
		$rec = $this->db->fetchAssoc($res);
		if ($rec) {
			return $rec['path'];
		}
		return null;
	}

	public function locateNonexistingObjectsInTree()
	{
		$import_path = $this->importTreePath();
		if ($import_path === null) {
			return [];
		}
		$q = 'SELECT child'
			.'	FROM tree'
			.'	LEFT JOIN object_reference ref ON ref.ref_id = child'
			.'	LEFT JOIN object_data data ON ref.obj_id = data.obj_id'
			.'	WHERE path LIKE '.$this->db->quote($import_path.'.%', 'text')
			.'		AND (ref.ref_id IS NULL OR data.obj_id IS NULL)';
		$return = [];
		$res = $this->db->query($q);
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['child'];
		}
		return $return;
	}
}
