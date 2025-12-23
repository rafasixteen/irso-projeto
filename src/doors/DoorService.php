<?php
declare(strict_types=1);

namespace App\Doors;

class DoorService
{
	private string $storageDir;

	private string $doorsFile;

	private string $historyDir;

	public function __construct()
	{
		$this->storageDir = __DIR__ . '/../../storage/doors';
		$this->doorsFile = $this->storageDir . '/doors.json';
		$this->historyDir = $this->storageDir . '/history';
	}

	public function add_door(Door $door): bool
	{
		$doors = $this->get_doors();
		$doors[] = $door;
		return $this->save_doors($doors);
	}

	public function get_door_by_id(string $id): ?Door
	{
		foreach ($this->get_doors() as $door) {
			if ($door->id === $id) {
				return $door;
			}
		}

		return null;
	}

	public function update_door(Door $updatedDoor): bool
	{
		$doors = $this->get_doors();
		$found = false;

		foreach ($doors as &$door) {
			if ($door->id === $updatedDoor->id) {
				$door = $updatedDoor;
				$found = true;
				break;
			}
		}

		if (!$found) {
			return false;
		}

		return $this->save_doors($doors);
	}

	public function delete_door_by_id(string $id): bool
	{
		$doors = $this->get_doors();
		$filtered = array_filter($doors, fn(Door $d) => $d->id !== $id);

		if (count($doors) === count($filtered)) {
			return false;
		}

		return $this->save_doors(array_values($filtered));
	}

	public function get_latest_history(): array
	{
		$doors = $this->get_doors();
		$result = [];

		foreach ($doors as $door) {
			$history = $this->get_history($door->id);
			$latest = end($history) ?: null;
			$result[] = [
				'id' => $door->id,
				'latest' => $latest,
			];
		}

		return $result;
	}

	public function get_history(string $doorId): array
	{
		$file = $this->get_history_file($doorId);

		if (!file_exists($file)) {
			return [];
		}

		return json_decode(file_get_contents($file), true) ?? [];
	}

	public function get_latest_history_entry(string $doorId): ?object
	{
		$history = $this->get_history($doorId);

		if (empty($history)) {
			return null;
		}

		return (object) end($history);
	}

	public function add_history_entry(string $id, string $state): bool
	{
		$file = $this->get_history_file($id);

		$dir = dirname($file);

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$history = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];

		$history[] = [
			'timestamp' => date('c'),
			'state' => $state,
		];

		return file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT)) !== false;
	}

	private function get_history_file(string $id): string
	{
		return $this->historyDir . '/' . $id . '.json';
	}

	private function get_doors(): array
	{
		if (!file_exists($this->doorsFile)) {
			return [];
		}

		$data = json_decode(file_get_contents($this->doorsFile), true) ?? [];
		return array_map(fn($d) => Door::fromArray($d), $data);
	}

	private function save_doors(array $doors): bool
	{
		$data = array_map(fn(Door $d) => $d->toArray(), $doors);
		return file_put_contents($this->doorsFile, json_encode($data, JSON_PRETTY_PRINT)) !== false;
	}
}