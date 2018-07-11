<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class EitherTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->f = new \ILIAS\TMS\Filter\TypeFactory();
		$this->type = $this->f->either($this->f->int(), $this->f->string());
	}

	public function test_repr() {
		$this->assertEquals("(int|string)", $this->type->repr());
	}

	public function test_contains() {
		$this->assertTrue($this->type->contains(1));
		$this->assertTrue($this->type->contains("one"));
		$this->assertFalse($this->type->contains([]));
		$this->assertFalse($this->type->contains(new \stdClass));
	}

	public function test_unflatten_int() {
		$vals = [1, "two", "three"];
		$this->assertEquals(1, $this->type->unflatten($vals));
		$this->assertEquals(["two", "three"], $vals);
	}

	public function test_unflatten_string() {
		$vals = ["one", "two", "three"];
		$this->assertEquals("one", $this->type->unflatten($vals));
		$this->assertEquals(["two", "three"], $vals);
	}

	public function test_flatten_int() {
		$val = 0; 
		$this->assertEquals($this->f->int()->flatten($val), $this->type->flatten($val));
	}

	public function test_flatten_string() {
		$val = "zero"; 
		$this->assertEquals($this->f->string()->flatten($val), $this->type->flatten($val));
	}
}
