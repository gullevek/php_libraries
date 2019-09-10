<?php declare(strict_types=1);

namespace Foo;

class FooBar
{
	public $foo = '';

	public function __construct()
	{
		$this->foo = 'BAR';
	}

	public function otherBarBar($wrong)
	{
		echo "B: $wrong<br>";
	}

	public function barBar($wrong)
	{
		echo "B: $wrong<br>";
	}
}
