<?php
declare(strict_types=1);

namespace App\Users;

class UserService
{
	protected string $filePath;

	public function __construct()
	{
		$this->filePath = __DIR__ . '/../../storage/users.json';
	}

	public function create(string $userName): bool
	{
		$newUserId = $this->get_user_count() + 1;
		$newUserRfidTag = $this->get_unique_rfid_tag();
		$newUser = new User($newUserId, $userName, $newUserRfidTag);

		$users = $this->get_users();
		array_push($users, $newUser);
		return $this->save_users($users);
	}

	public function read(int $id): ?User
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->id === $id) {
				return $user;
			}
		}

		return null;
	}

	public function update(int $id, string $newName): bool
	{
		$users = $this->get_users();

		foreach ($users as $user) {
			if ($user->id === $id) {
				$user->name = $newName;

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
		if (!file_exists($this->filePath)) {
			return [];
		}

		$json = file_get_contents($this->filePath);
		$data = json_decode($json, true) ?? [];

		return array_map(fn($item) => User::fromArray($item), $data);
	}

	private function save_users(array $users): bool
	{
		$data = array_map(fn(User $user) => $user->toArray(), $users);
		$json = json_encode($data, JSON_PRETTY_PRINT);
		return file_put_contents($this->filePath, $json) !== false;
	}

	private function get_user_count(): int
	{
		$users = $this->get_users();
		return count($users);
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
}
