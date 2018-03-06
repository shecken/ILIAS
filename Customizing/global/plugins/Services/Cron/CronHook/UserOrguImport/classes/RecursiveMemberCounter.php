<?php

namespace CaT\IliasUserOrguImport;

class RecursiveMemberCounter
{

	protected $children = [];
	protected $parents = [];
	protected $num_members = [];
	protected $depth = [];


	public function addNode($node_id, $num_members, $parent = null)
	{
		assert('is_string($node_id) || is_int($node_id)');
		assert('is_int($num_members)');
		if (array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('may not overwrite node '.$node_id);
		}
		if ($parent !== null) {
			assert('is_string($parent) || is_int($parent)');
			if (!array_key_exists($parent, $this->children)) {
				throw new \InvalidArgumentException('unknown node '.$parent);
			}
			$this->parents[$node_id] = $parent;
			$this->children[$parent][] = $node_id;
			$this->depth[$node_id] = $this->depth[$parent] + 1;
		} else {
			$this->depth[$node_id] = 0;
		}

		$this->children[$node_id] = [];
		$this->num_members[$node_id] = $num_members;
	}

	public function children($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->children[$node_id];
	}

	public function parent($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->parents[$node_id];
	}

	public function members($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->num_members[$node_id];
	}

	public function depth($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->depth[$node_id];
	}

	public function recursiveMembers()
	{
		$swp_depth = $this->depth;
		arsort($swp_depth);
		$return = array_fill_keys(array_keys($this->children), 0);
		foreach ($swp_depth as $node_id => $depth) {
			// run tree nodes from deep to shallow
			// once we visit a node at level x we may guarantee all deeper nodes are done
			// recursive members count = count of node self ...
			// plus recursive count of children
			$return[$node_id] += $this->members($node_id);
			$parent = $this->parent($node_id);
			if ($parent !== null) {
				$return[$parent] += $return[$node_id];
			}
		}
		return $return;
	}
}
