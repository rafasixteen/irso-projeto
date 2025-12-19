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

	protected function getFilePath(string $name): string
	{
		return $this->storagePath . '/' . $name . '.json';
	}

	public function getLatestValue(string $name): mixed
	{
		$file = $this->getFilePath($name);

		if (!file_exists($file)) {
			return null;
		}

		$data = json_decode(file_get_contents($file), true);

		if (empty($data)) {
			return null;
		}

		$last = end($data);
		return $last['value'] ?? null;
	}

	public function getHistory(string $name): array
	{
		$file = $this->getFilePath($name);

		if (!file_exists($file)) {
			return [];
		}

		return json_decode(file_get_contents($file), true) ?? [];
	}

	public function appendValue(string $name, mixed $value): bool
	{
		$file = $this->getFilePath($name);

		$history = [];

		if (file_exists($file)) {
			$history = json_decode(file_get_contents($file), true) ?? [];
		}

		$history[] = [
			'timestamp' => gmdate('c'),
			'value' => $this->normalizeValue($value),
		];

		return file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT), LOCK_EX) !== false;
	}

	public function getAllNames(): array
	{
		$files = glob($this->storagePath . '/*.json');

		return array_map(fn($file) => basename($file, '.json'), $files ?: []);
	}

	protected function normalizeValue(mixed $value): mixed
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