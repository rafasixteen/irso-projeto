<?php
declare(strict_types=1);

namespace App\Doors;

class DoorService
{
	private string $storageDir;

	private string $doorsFile;

	public function __construct()
	{
		$this->storageDir = __DIR__ . '/../../storage/doors';
		$this->doorsFile = $this->storageDir . '/doors.json';
	}

	public function add_door(Door $door): bool
	{
		$doors = $this->load_doors();
		$doors[] = $door;
		return $this->save_doors($doors);
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

	public function update_door(Door $updatedDoor): bool
	{
		$doors = $this->load_doors();
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
		$doors = $this->load_doors();
		$filtered = array_filter($doors, fn(Door $d) => $d->id !== $id);

		if (count($doors) === count($filtered)) {
			return false;
		}

		return $this->save_doors(array_values($filtered));
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
}