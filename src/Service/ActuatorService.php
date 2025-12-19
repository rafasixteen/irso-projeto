<?php
declare(strict_types=1);

namespace App\Service;

final class ActuatorService
{
	private array $actuators = [];

	public function __construct()
	{
		$this->actuators = [
			'led' => false,
			'motor_speed' => 16,
			'fan_level' => 17.4,
			'screen_text' => 'hello world',
		];
	}

	public function get(string $name): mixed
	{
		return $this->actuators[$name] ?? null;
	}

	public function getAll(): array
	{
		return $this->actuators;
	}

	public function set(string $name, mixed $value): bool
	{
		if (!array_key_exists($name, $this->actuators)) {
			return false;
		}

		$this->actuators[$name] = $value;
		return true;
	}
}
