<?php

namespace CaT\IliasUserOrguImport;

class RecursiveMemberCounter
{

	/**
	 * @var string|int[][string|int]
	 */
	protected $children = [];

	/**
	 * @var string|int[string|int]
	 */
	protected $parents = [];

	/**
	 * @var int[string|int]
	 */
	protected $num_members = [];

	/**
	 * @var int[string|int]
	 */
	protected $depth = [];

	/**
	 * Add a node under parent havin a certain number of members.
	 * Parent must be present, if defined. Node must not be present.
	 *
	 * @param	string|int	$node_id
	 * @param	int	$members
	 * @param	string|int|null	$parent
	 */
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

	/**
	 * Get an array of node-ids corresponding to the children of a node.
	 *
	 * @param	string|int	$node_id
	 * @return	string|int[]
	 */
	public function children($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->children[$node_id];
	}

	/**
	 * Get the node-id corresponding to the parent of a node or null,
	 * if node is root.
	 *
	 * @param	string|int	$node_id
	 * @return	string|int|null
	 */
	public function parent($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->parents[$node_id];
	}


	/**
	 * Get the number of members associated with a node.
	 *
	 * @param	string|int	$node_id
	 * @return	int
	 */
	public function members($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->num_members[$node_id];
	}

	/**
	 * Get the depth of a node.
	 *
	 * @param	string|int	$node_id
	 * @return	int
	 */
	public function depth($node_id)
	{
		assert('is_string($node_id) || is_int($node_id)');
		if (!array_key_exists($node_id, $this->children)) {
			throw new \InvalidArgumentException('unknown node '.$node_id);
		}
		return $this->depth[$node_id];
	}

	/**
	 * Get the associative array node-id => recursive members.
	 *
	 * @param	string|int	$node_id
	 * @return	int[string|int]
	 */
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
