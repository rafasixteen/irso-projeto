<?php
declare(strict_types=1);

namespace App\Doors;

class DoorService
{
	private string $storageDir = __DIR__ . '/../../storage/doors';

	private string $doorsFile;

	private string $historyFile;

	public function __construct()
	{
		$this->doorsFile = $this->storageDir . '/doors.json';
		$this->historyFile = $this->storageDir . '/door_history.json';
	}

	public function get_door_by_id(string $id): ?Door
	{
		foreach ($this->load_doors() as $door) {
			if ($door->id === $id) {
				return $door;
			}
		}
		return null;
	}

	public function create(Door $door): bool
	{
		$doors = $this->load_doors();
		$doors[] = $door;

		$saved = $this->save_doors($doors);

		if ($saved) {
			$this->log_history($door->id, false);
		}

		return $saved;
	}

	public function update_state(string $id, bool $open): bool
	{
		$current = $this->is_door_open($id);

		if ($current === $open) {
			return true;
		}

		return $this->log_history($id, $open);
	}

	public function delete(string $id): bool
	{
		$doors = $this->load_doors();
		$filtered = array_filter($doors, fn(Door $d) => $d->id !== $id);

		if (count($doors) === count($filtered)) {
			return false;
		}

		$this->save_doors(array_values($filtered));
		return true;
	}

	public function exists(string $id): bool
	{
		foreach ($this->load_doors() as $door) {
			if ($door->id === $id) {
				return true;
			}
		}
		return false;
	}

	public function is_door_open(string $id): ?bool
	{
		$history = $this->get_history($id);

		if (empty($history)) {
			return null;
		}

		$latest = end($history);
		return $latest['open'] ?? null;
	}

	public function get_history(string $doorId): array
	{
		if (!file_exists($this->historyFile)) {
			return [];
		}

		$logs = json_decode(file_get_contents($this->historyFile), true) ?? [];

		return array_values(array_filter($logs, fn($log) => $log['door'] === $doorId));
	}

	private function load_doors(): array
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

	private function log_history(string $doorId, bool $open): bool
	{
		$logs = [];

		if (file_exists($this->historyFile)) {
			$logs = json_decode(file_get_contents($this->historyFile), true) ?? [];
		}

		$logs[] = [
			'door' => $doorId,
			'open' => $open,
			'timestamp' => gmdate('c'),
		];

		return file_put_contents($this->historyFile, json_encode($logs, JSON_PRETTY_PRINT)) !== false;
	}
}