<?php
declare(strict_types=1);

namespace App\Users;

use RuntimeException;

class UserService
{
	protected string $storageDir;

	protected string $storagePath;

	protected string $counterPath;

	public function __construct()
	{
		$this->storageDir = __DIR__ . '/../../storage/users';
		$this->storagePath = $this->storageDir . '/users.json';
		$this->counterPath = $this->storageDir . '/user_id_counter.txt';
	}

	public function add_user(string $name, string $role, string $gender): ?User
	{
		$id = $this->generate_incremental_id($this->counterPath);
		$ridTag = $this->get_unique_rfid_tag();

		$user = new User($id, $name, $role, $gender, $ridTag);
		$users = $this->get_users();
		$users[] = $user;

		return $this->save_users($users) ? $user : null;
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

	public function update_user(User $updatedUser): bool
	{
		$users = $this->get_users();
		$found = false;

		foreach ($users as &$user) {
			if ($user->id === $updatedUser->id) {
				$user = $updatedUser;
				$found = true;
				break;
			}
		}

		if (!$found) {
			return false;
		}

		return $this->save_users($users);
	}

	public function delete_user_by_id(int $id): bool
	{
		$users = $this->get_users();
		$filtered = array_filter($users, fn(User $user) => $user->id !== $id);

		if (count($users) === count($filtered)) {
			return false;
		}

		return $this->save_users(array_values($filtered));
	}

	public function get_users(): array
	{
		if (!file_exists($this->storagePath)) {
			return [];
		}

		$data = json_decode(file_get_contents($this->storagePath), true) ?? [];
		return array_map(fn($d) => User::fromArray($d), $data);
	}

	private function save_users(array $users): bool
	{
		$data = array_map(fn(User $d) => $d->toArray(), $users);
		return file_put_contents($this->storagePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
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