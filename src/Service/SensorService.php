<?php
declare(strict_types=1);

namespace App\Service;

final class SensorService
{
	private array $sensors = [];

	public function __construct()
	{
		$this->sensors = [
			'temperature' => 25,
			'humidity' => 60,
			'pressure' => 1013,
			'light' => 300,
			'motion' => 1,
		];
	}

	public function get(string $name): ?int
	{
		return $this->sensors[$name] ?? null;
	}

	public function getAll(): array
	{
		return $this->sensors;
	}

	public function set(string $name, string $value): bool
	{
		if (!array_key_exists($name, $this->sensors)) {
			return false;
		}

		$this->sensors[$name] = $value;
		return true;
	}
}
