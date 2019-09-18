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

$foo = $bar ?? 'EMPTY';
echo "BAR: ".$foo."<br>";
// define('DS', DIRECTORY_SEPARATOR);
$ds = defined('DS') ? DS : DIRECTORY_SEPARATOR;
$du = DS ?? DIRECTORY_SEPARATOR;
echo "DS is: ".$ds."<br>";

echo "SERVER HOST: ".$_SERVER['HTTP_HOST']."<br>";

// __END__
