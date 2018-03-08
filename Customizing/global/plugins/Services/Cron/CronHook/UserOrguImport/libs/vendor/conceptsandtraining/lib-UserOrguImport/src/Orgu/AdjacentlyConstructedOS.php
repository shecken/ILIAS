<?php

namespace CaT\UserOrguImport\Orgu;

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;

/**
 * This implementation aims for unhierarchical construction of
 * trees. I.e. a orgu may be inserted into the tree before it's
 * parent. However, no structural information may be requested,
 * before the construction is valid.
 *
 * Notice, the tree may cosist of several subtrees, all dangling
 * from their own root.
 *
 * Any Orgu is identified by its id-properties. However, two sets
 * of these may represent the same Orgu without being equal. To
 */

class AdjacentlyConstructedOS implements OrgStructure
{
	protected $orgus = [];
	protected $roots = [];

	protected $placeholders = [];

	protected $children = [];
	protected $placeholders_children = [];

	protected $validated = false;

	protected $ident;

	public function __construct(Identifier $ident)
	{
		$this->ident = $ident;
	}

	public function addRootOrgu(OrgUnit $ou)
	{
		if ($this->getSilblingAllreadyInRoot($ou) !== null && $this->getSilblingAllreadyIntree($ou) !== null) {
			throw new \LogicException($this->ident->digestId($ou).' already in tree');
		}
		$id = $this->ident->digestId($ou);
		$this->orgus[$id] = $ou;
		$this->roots[$id] = $ou;
		$placeholder = $this->getSilblingAllreadyInPlaceholders($ou);
		if ($placeholder !== null) {
			// $ou serves as putative parent to some other orgu allready in tree
			// replace the placeholder by $ou
			$ph_id = $this->ident->digestId($placeholder);
			$this->children[$id] = $this->placeholders_children[$ph_id];
			unset($this->placeholders_children[$ph_id]);
			unset($this->placeholders[$ph_id]);
		} else {
			// apparently $ou does not yet serve as parent
			$this->children[$id] = [];
		}
		$this->validated = false;
	}

	/**
	 * @inheritdoc
	 */
	public function addOrgu(AdjacentOrgUnit $ou)
	{
		if ($this->getSilblingAllreadyIntree($ou) !== null) {
			throw new \LogicException($this->ident->digestId($ou).' already in tree');
		}

		$id = $this->ident->digestId($ou);
		$this->orgus[$id] = $ou;

		$parent_aux = new OrgUnit($ou->parentOrguIdProperties(), $this->ident);
		$parent = $this->getSilblingAllreadyInTree($parent_aux);

		if ($parent !== null) {
			// the putative parent is actually in tree
			// associate $ou with it as child
			$pa_id = $this->ident->digestId($parent);
			assert('isset($this->orgus[$pa_id])');
			if (!isset($this->children[$pa_id])) {
				$this->children[$pa_id] = [$id];
			} else {
				$this->children[$pa_id][] = $id;
			}
		} else {
			// the putative parent is not in tree yet
			$parent_placeholder = $this->getSilblingAllreadyInPlaceholders($parent_aux);
			if ($parent_placeholder !== null) {
				// the putative parent is allready in placeholder, i.e. a different orgu
				// claims it as its parent, asoociate ou with it
				$this->placeholders_children[$this->ident->digestId($parent_placeholder)][] = $id;
			} else {
				// the putative parent not in placeholder yet, add and asoociate ou with it
				$pa_id = $this->ident->digestId($parent_aux);
				$this->placeholders[$pa_id] = $parent_aux;
				$this->placeholders_children[$pa_id] = [$id];
			}
		}
		$placeholder = $this->getSilblingAllreadyInPlaceholders($ou);
		if ($placeholder !== null) {
			// $ou serves as putative parent to some other orgu allready in tree
			// replace the placeholder by $ou
			$ph_id = $this->ident->digestId($placeholder);
			$this->children[$id] = $this->placeholders_children[$ph_id];
			unset($this->placeholders_children[$ph_id]);
			unset($this->placeholders[$ph_id]);
		} else {
			// apparently $ou does not yet serve as parent
			$this->children[$id] = [];
		}
		$this->validated = false;
	}

	protected function getSilblingAllreadyInTree(OrgUnit $ou)
	{
		return $this->getSilblingInArray($ou, $this->orgus);
	}

	protected function getSilblingAllreadyInPlaceholders(OrgUnit $ou)
	{
		return $this->getSilblingInArray($ou, $this->placeholders);
	}

	protected function getSilblingAllreadyInRoot(OrgUnit $ou)
	{
		return $this->getSilblingInArray($ou, $this->roots);
	}

	protected function getSilblingInArray(OrgUnit $ou, array $orgus)
	{
		if (isset($orgus[$this->ident->digestId($ou)])) {
			return $orgus[$this->ident->digestId($ou)];
		}
		if (!$this->ident->digestUnique()) {
			foreach ($orgus as $a_orgu) {
				if ($this->ident->same($a_orgu, $ou)) {
					return $a_orgu;
				}
			}
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function orgu(array $id_properties)
	{
		return $this->getSilblingAllreadyIntree(new OrgUnit($id_properties, $this->ident));
	}


	/**
	 * @inheritdoc
	 */
	public function rootOrgus()
	{
		if (!$this->treeConsistent()) {
			throw new \LogicException('may not query structural information, since the tree is invalid');
		}
		return array_values($this->roots);
	}

	/**
	 * @inheritdoc
	 */
	public function subOrgus(OrgUnit $ou)
	{
		if (!$this->treeConsistent()) {
			throw new \LogicException('may not query structural information, since the tree is invalid');
		}
		return $this->getSubOrgusOf($ou);
	}

	protected function getSubOrgusOf(OrgUnit $ou)
	{
		$id = $this->ident->digestId($this->getSilblingAllreadyIntree($ou));
		$return = [];
		foreach ($this->children[$id] as $c_id) {
			$return[] = $this->orgus[$c_id];
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function treeConsistent()
	{
		if ($this->validated) {
			return $this->valid;
		}
		// run the tree top to bottom and make sure every orgu is visited
		$visited = [];
		$queue = array_keys($this->roots);

		while ($ou = array_shift($queue)) {
			if (isset($visited[$ou])) {
				return false;
			} else {
				$visited[$ou] = 1;
			}
			foreach ($this->children[$ou] as $sub_id) {
				array_push($queue, $sub_id);
			}
		}
		$return = true;

		foreach ($this->orgus as $id => $orgu) {
			if ($visited[$id] !== 1) {
				$return = false;
				break;
			}
		}
		$this->validated = true;
		$this->valid = $return;
		return $return;
	}
}
