<?php

namespace Admin\TestLibs;

class TestL
{
	/** @var \CoreLibs\Debug\LoggingLegacy */
	public $log;
	public function __construct()
	{
		$this->log = new \CoreLibs\Debug\LoggingLegacy([
			'log_folder' => '../log/',
			'file_id' => 'DebugTestTestLLogger',
		]);
	}
	/**
	 * Undocumented function
	 *
	 * @param  string|null $ts
	 * @return bool
	 */
	public function test(?string $ts = null): bool
	{
		print "* GETCALLERCLASS(INSIDE CLASS): " . \CoreLibs\Debug\Support::getCallerClass() . "<br>";
		print "* GETCALLERTOPCLASS(INSIDE CLASS): " . \CoreLibs\Debug\Support::getCallerTopLevelClass() . "<br>";
		print "* GETCALLSTACK(INSIDE CLASS): <pre>"
			. \CoreLibs\Debug\Support::prAr(\CoreLibs\Debug\Support::getCallStack()) . "</pre><br>";
		$this->log->debug('TESTL', 'Logging in class testL' . ($ts !== null ? ': ' . $ts : ''));
		$this->log->debug('TESTL', 'Some other message');
		return true;
	}
}

// __END__
