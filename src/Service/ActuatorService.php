<?php
declare(strict_types=1);

namespace App\Service;

final class ActuatorService extends DeviceService
{
	public function __construct()
	{
		parent::__construct('actuators');
	}
}