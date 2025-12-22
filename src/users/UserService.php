<?php
declare(strict_types=1);

namespace App\Users;

use RuntimeException;

class UserService
{
	protected string $storageDir = __DIR__ . '/../../storage/users';

	protected string $storagePath;

	protected string $counterPath;

	public function __construct()
	{
		$this->storagePath = $this->storageDir . '/users.json';
		$this->counterPath = $this->storageDir . '/user_id_counter.txt';
	}

	public function create(string $name, string $role, string $gender): bool
	{
		$newUserId = $this->generate_incremental_id($this->counterPath);
		$newUserRfidTag = $this->get_unique_rfid_tag();
		$newUser = new User($newUserId, $name, $role, $gender, $newUserRfidTag);
		$users = $this->get_users();
		array_push($users, $newUser);
		return $this->save_users($users);
	}

	public function get_user_by_id(int $id): ?User
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->id === $id) {
				return $user;
			}
		}

		return null;
	}

	public function get_user_by_rfid(string $rfidTag): ?User
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->rfidTag === $rfidTag) {
				return $user;
			}
		}

		return null;
	}

	public function update(int $id, string $newName, string $newRole, string $newGender): bool
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->id === $id) {
				$user->name = $newName;
				$user->role = $newRole;
				$user->gender = $newGender;

				return $this->save_users($users);
			}
		}

		return false;
	}

	public function delete(int $id): bool
	{
		$users = $this->get_users();
		$users = array_filter($users, fn(User $user) => $user->id !== $id);
		return $this->save_users(array_values($users));
	}

	public function exists(int $id): bool
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->id === $id) {
				return true;
			}
		}

		return false;
	}

	public function get_users(): array
	{
		if (!file_exists($this->storagePath)) {
			return [];
		}

		$json = file_get_contents($this->storagePath);
		$data = json_decode($json, true) ?? [];

		return array_map(fn($item) => User::fromArray($item), $data);
	}

	private function save_users(array $users): bool
	{
		$data = array_map(fn(User $user) => $user->toArray(), $users);
		$json = json_encode($data, JSON_PRETTY_PRINT);
		return file_put_contents($this->storagePath, $json) !== false;
	}

	private function get_unique_rfid_tag(): string
	{
		$users = $this->get_users();

		do {
			$tag = $this->random_rfid_tag();
			$isUnique = true;

			foreach ($users as $user) {
				if ($user->rfidTag === $tag) {
					$isUnique = false;
					break;
				}
			}
		} while (!$isUnique);

		return $tag;
	}

	private function random_rfid_tag(): string
	{
		return bin2hex(random_bytes(4));
	}

	function generate_incremental_id(string $path): int
	{
		$dir = dirname($path);

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$fp = fopen($path, 'c+');

		if (!$fp) {
			throw new RuntimeException('Cannot open file at: ' . $path);
		}

		flock($fp, LOCK_EX);

		$current = (int) trim(stream_get_contents($fp));
		$next = $current + 1;

		rewind($fp);
		ftruncate($fp, 0);
		fwrite($fp, (string) $next);

		flock($fp, LOCK_UN);
		fclose($fp);

		return $next;
	}
}
