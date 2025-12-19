<?php
declare(strict_types=1);

namespace App\Service;

class UserService
{
	protected string $filePath;

	public function __construct()
	{
		$this->filePath = __DIR__ . '/../../storage/users.json';
	}

	public function create(string $userName): bool
	{
		$newUserId = $this->getUserCount() + 1;
		$newUserRfidTag = $this->generateRfidTag();
		$newUser = new User($newUserId, $userName, $newUserRfidTag);

		$users = $this->getUsers();
		array_push($users, $newUser);
		return $this->saveUsers($users);
	}

	public function read(string $id): ?User
	{
		$users = $this->getUsers();

		foreach ($users as $user) {
			if ($user->id === $id) {
				return $user;
			}
		}

		return null;
	}

	public function update(string $id, ?string $userName, ?string $rfidTag): bool
	{
		$users = $this->getUsers();

		foreach ($users as $user) {
			if ($user->id === $id) {
				if ($userName !== null) {
					$user->name = $userName;
				}
				if ($rfidTag !== null) {
					$user->rfidTag = $rfidTag;
				}

				return $this->saveUsers($users);
			}
		}

		return false;
	}

	public function delete(string $id): bool
	{
		$users = $this->getUsers();
		$users = array_filter($users, fn(User $user) => $user->id !== $id);
		return $this->saveUsers(array_values($users));
	}

	private function getUsers(): array
	{
		if (!file_exists($this->filePath)) {
			return [];
		}

		$json = file_get_contents($this->filePath);
		$data = json_decode($json, true) ?? [];

		return array_map(fn($item) => User::fromArray($item), $data);
	}

	private function saveUsers(array $users): bool
	{
		$data = array_map(fn(User $user) => $user->toArray(), $users);
		$json = json_encode($data, JSON_PRETTY_PRINT);
		return file_put_contents($this->filePath, $json) !== false;
	}

	private function getUserCount(): int
	{
		$users = $this->getUsers();
		return count($users);
	}

	private function generateRfidTag(): string
	{
		return bin2hex(random_bytes(4));
	}
}