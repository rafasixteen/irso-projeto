<?php
declare(strict_types=1);

namespace App\Rfids;

use App\Rfids\Permission;
use App\Doors\Door;
use App\Doors\DoorService;
use App\Users\User;

class RfidService
{
	private DoorService $doorService;

	private string $storageDir;

	private string $historyDir;

	private string $permissionsFile;

	private bool $isGymOpen = true;

	public function __construct()
	{
		$this->doorService = new DoorService();

		$this->storageDir = __DIR__ . '/../../storage/rfids';
		$this->historyDir = $this->storageDir . '/history';
		$this->permissionsFile = $this->storageDir . '/permissions.json';
	}

	public function check_access(User $user, Door $door): array
	{
		$permissions = $this->load_permissions();

		$permissionsForDoor = array_filter($permissions, fn($p) => $p->doorType === $door->type);

		if (empty($permissionsForDoor)) {
			return ['authorized' => false, 'reason' => 'no matching permission'];
		}

		$failureReason = 'no permission matched';

		foreach ($permissionsForDoor as $permission) {
			if (!in_array($user->role, $permission->allowedRoles, true)) {
				continue;
			}

			$result = $this->evaluate_conditions($permission->conditions, $user, $door);

			if ($result['authorized']) {
				return ['authorized' => true, 'reason' => 'access granted'];
			}

			$failureReason = $result['reason'];
		}

		return ['authorized' => false, 'reason' => $failureReason];
	}

	public function get_latest_history(): array
	{
		$doors = $this->doorService->get_doors();
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

	public function add_history_entry(string $doorId, string $rfidTag, string $message): bool
	{
		$file = $this->get_history_file($doorId);

		$dir = dirname($file);

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$history = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];

		$history[] = [
			'timestamp' => date('c'),
			'rfid_tag' => $rfidTag,
			'message' => $message,
		];

		return file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT)) !== false;
	}

	private function get_history_file(string $doorId): string
	{
		return $this->historyDir . '/' . $doorId . '.json';
	}

	private function load_permissions(): array
	{
		if (!file_exists($this->permissionsFile)) {
			return [];
		}

		return array_map(fn(array $perm) => Permission::fromArray($perm), json_decode(file_get_contents($this->permissionsFile), true) ?? []);
	}

	private function evaluate_conditions(array $conditions, User $user, Door $door): array
	{
		foreach ($conditions as $condition => $expected) {
			switch ($condition) {
				case 'gym_open':
					if ($expected !== $this->isGymOpen) {
						return ['authorized' => false, 'reason' => 'gym closed'];
					}
					break;

				case 'gender_match':
					if ($expected && $door->allowedGender !== null && $user->gender !== $door->allowedGender) {
						return ['authorized' => false, 'reason' => 'gender mismatch'];
					}
					break;
			}
		}

		return ['authorized' => true, 'reason' => 'conditions met'];
	}
}
