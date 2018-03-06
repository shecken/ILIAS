<?php

use CaT\IliasUserOrguImport as UOI;

class RecursiveMeberCounterTest extends PHPUnit_Framework_TestCase
{

	const MEMBERS = 'members';
	const PARENT_NODE = 'parent';

	protected static $tree = [
								'n1' => [self::MEMBERS => 1, self::PARENT_NODE => null]
									,'sn11' => [self::MEMBERS => 0, self::PARENT_NODE => 'n1']
										,'ssn111' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn11']
											,'sssn1111' => [self::MEMBERS => 0, self::PARENT_NODE => 'ssn111']
										,'ssn112' => [self::MEMBERS => 1, self::PARENT_NODE => 'sn11']
									,'sn12' => [self::MEMBERS => 0, self::PARENT_NODE => 'n1']#
								,'n3' => [self::MEMBERS => 0, self::PARENT_NODE => null]
								,'n2' => [self::MEMBERS => 0, self::PARENT_NODE => null]
									,'sn21' => [self::MEMBERS => 0, self::PARENT_NODE => 'n2']
										,'ssn211' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn21']
										,'ssn212' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn21']
									,'sn22' => [self::MEMBERS => 0, self::PARENT_NODE => 'n2']
										,'ssn221' => [self::MEMBERS => 1, self::PARENT_NODE => 'sn22']
										,'ssn222' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn22']
									,'sn23' => [self::MEMBERS => 1, self::PARENT_NODE => 'n2']
										,'ssn231' => [self::MEMBERS => 3, self::PARENT_NODE => 'sn23']
										,'ssn232' => [self::MEMBERS => 2, self::PARENT_NODE => 'sn23']
									,'sn24' => [self::MEMBERS => 0, self::PARENT_NODE => 'n2']
										,'ssn241' => [self::MEMBERS => 1, self::PARENT_NODE => 'sn24']
											,'sssn2411' => [self::MEMBERS => 0, self::PARENT_NODE => 'ssn241']
												,'ssssn24111' => [self::MEMBERS => 0, self::PARENT_NODE => 'sssn2411']
									,'sn31' => [self::MEMBERS => 0, self::PARENT_NODE => 'n3']
										,'ssn311' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn31']
											,'sssn3111' => [self::MEMBERS => 0, self::PARENT_NODE => 'ssn311']
										,'ssn312' => [self::MEMBERS => 0, self::PARENT_NODE => 'sn31']
									,'sn32' => [self::MEMBERS => 0, self::PARENT_NODE => 'n3']


							];


	protected static $cnts = [
								'n1' => 2
								,'sn11' => 1
								,'ssn111' => 0
								,'sssn1111' => 0
								,'ssn112' => 1
								,'sn12' => 0
								,'n2' => 8
								,'sn21' => 0
								,'ssn211' => 0
								,'ssn212' => 0
								,'sn22' => 1
								,'ssn221' => 1
								,'ssn222' => 0
								,'sn23' => 6
								,'ssn231' => 3
								,'ssn232' => 2
								,'sn24' => 1
								,'ssn241' => 1
								,'sssn2411' => 0
								,'ssssn24111' => 0
								,'n3' => 0
								,'sn31' => 0
								,'ssn311' => 0
								,'sssn3111' => 0
								,'ssn312' => 0
								,'sn32' => 0
							];

	public function test_init()
	{
		return new UOI\RecursiveMemberCounter();
	}

	/**
	 * @depends test_init
	 */
	public function test_build_tree($rc)
	{
		foreach (self::$tree as $node_id => $props) {
			$rc->addNode($node_id, $props[self::MEMBERS], $props[self::PARENT_NODE]);
		}
		return $rc;
	}

	/**
	 * @depends test_build_tree
	 */
	public function test_provoke_errors($rc)
	{
		try {
			$rc->addNode('sn11', 1, null); // allready present
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
		try {
			$rc->addNode('sn11', 1, 'n1'); // allready present
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
		try {
			$rc->addNode('sn11', 1, 'bar'); // invalid parent
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
		try {
			$rc->children('foo'); // invalid node
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
		try {
			$rc->parent('foo'); // invalid node
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
		try {
			$rc->members('foo'); // invalid node
			$this->assertFalse('did not throw');
		} catch (\Exception $e) {
		}
	}

	/**
	 * @depends test_build_tree
	 */
	public function test_read_tree_properties($rc)
	{
		foreach (self::$tree as $node_id => $props) {
			$this->assertEquals($rc->parent($node_id), self::$tree[$node_id][self::PARENT_NODE]);
			$this->assertEquals($rc->members($node_id), self::$tree[$node_id][self::MEMBERS]);
		}
		$this->assertEquals($rc->children('n1'), ['sn11','sn12']);
		$this->assertEquals($rc->children('sssn3111'), []);
		$this->assertEquals($rc->children('sn21'), ['ssn211','ssn212']);

		$this->assertEquals($rc->depth('n1'), 0);
		$this->assertEquals($rc->depth('sssn3111'), 3);
		$this->assertEquals($rc->depth('sn21'), 1);
		return $rc;
	}

	/**
	 * @depends test_read_tree_properties
	 */
	public function test_calc_rec_members($rc)
	{
		$this->assertEquals($rc->recursiveMembers(), self::$cnts);
	}
}
