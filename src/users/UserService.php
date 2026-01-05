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
		$rfidTag = $this->get_unique_rfid_tag();

		$user = new User($id, $name, $role, $gender, $rfidTag);
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

	public function get_user_by_rfid(int $rfidTag): ?User
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

	private function get_unique_rfid_tag(): int
	{
		$used = array_map(fn(User $u) => $u->rfidTag, $this->get_users());

		if (count($used) >= 1000) {
			throw new RuntimeException('No available RFID tags (1 to 1000 exhausted)');
		}

		do {
			$tag = random_int(1, 1000);
		} while (in_array($tag, $used, true));

		return $tag;
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
