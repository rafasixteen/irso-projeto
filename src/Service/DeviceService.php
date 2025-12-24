<?php
declare(strict_types=1);

namespace App\Service;

abstract class DeviceService
{
	protected string $storagePath;

	public function __construct(string $storageDir)
	{
		$this->storagePath = __DIR__ . '/../../storage/' . $storageDir;

		if (!is_dir($this->storagePath)) {
			mkdir($this->storagePath, 0777, true);
		}
	}

	public function get_device_names(): array
	{
		$files = glob($this->storagePath . '/*.json');
		return array_map(fn($file) => basename($file, '.json'), $files ?: []);
	}

	public function get_latest_history(): array
	{
		$allNames = $this->get_device_names();
		$latestHistory = [];

		foreach ($allNames as $name) {
			$history = $this->get_history($name);

			if (!empty($history)) {
				$latestHistory[$name] = end($history);
			}
		}

		return $latestHistory;
	}

	public function get_history(string $deviceName): array
	{
		$file = $this->get_history_file($deviceName);

		if (!file_exists($file)) {
			return [];
		}

		return json_decode(file_get_contents($file), true) ?? [];
	}

	public function get_latest_history_entry(string $deviceName): ?object
	{
		$history = $this->get_history($deviceName);

		if (empty($history)) {
			return null;
		}

		return (object) end($history);
	}

	public function add_history_entry(string $deviceName, mixed $value): bool
	{
		$file = $this->get_history_file($deviceName);

		$dir = dirname($file);

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$history = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];

		$history[] = [
			'timestamp' => date('c'),
			'value' => $this->normalizeValue($value),
		];

		return file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT)) !== false;
	}

	private function get_history_file(string $deviceName): string
	{
		return $this->storagePath . '/' . $deviceName . '.json';
	}

	private function normalizeValue(mixed $value): mixed
	{
		if ($value === null) {
			return null;
		}

		// JSON-ready structures
		if (is_array($value)) {
			return $value;
		}

		if (is_object($value)) {
			return json_decode(json_encode($value), true);
		}

		// Booleans
		if (is_bool($value)) {
			return $value;
		}

		// String booleans
		if (is_string($value)) {
			$lower = strtolower(trim($value));

			if ($lower === 'true') {
				return true;
			}

			if ($lower === 'false') {
				return false;
			}
		}

		// Numbers
		if (is_numeric($value)) {
			return str_contains((string) $value, '.') ? (float) $value : (int) $value;
		}

		// Fallback: string
		return (string) $value;
	}
}
