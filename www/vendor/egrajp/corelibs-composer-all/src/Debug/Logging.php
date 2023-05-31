<?php

/*
 * This is a wrapper placeholder for
 * \CoreLibs\Logging\Logger
 */

declare(strict_types=1);

namespace CoreLibs\Debug;

/**
 * @deprecated Use \CoreLibs\Logger\Logging
 */
class Logging extends \CoreLibs\Logging\Logging
{
	/**
	 *
	 * @param array<mixed> $options Array with settings options
	 */
	public function __construct(array $options = [])
	{
		parent::__construct($options);
	}
}

// __END__
