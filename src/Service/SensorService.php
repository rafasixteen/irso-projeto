<?php
declare(strict_types=1);

namespace App\Service;

final class SensorService extends DeviceService
{
	public function __construct()
	{
		parent::__construct('sensors');
	}
}
