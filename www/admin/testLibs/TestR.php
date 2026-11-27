<?php

namespace Admin\TestLibs;

class TestR extends TestL
{
	/** @var string */
	public $foo;
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function subTest(): bool
	{
		print "** GETCALLERCLASS(INSIDE EXTND CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		print "** GETCALLERTOPCLASS(INSIDE EXTND CLASS): " . \CoreLibs\Debug\Support::getCallerTopLevelClass() . "<br>";
		print "** GETCALLSTACK(INSIDE EXTND CLASS): <pre>"
			. \CoreLibs\Debug\Support::prAr(\CoreLibs\Debug\Support::getCallStack()) . "</pre><br>";
		$this->log->debug('TESTR', 'Logging in class testR (extends testL)');
		$this->test('TESTR INSIDE');
		$this->log->debug('TESTR', 'Array: '
			. $this->log->prAr(['a', 'b']) . ', Other: ' . $this->log->prAr(['a', 'b']));
		return true;
	}
}

// __END__
