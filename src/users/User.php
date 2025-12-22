<?php
declare(strict_types=1);

namespace App\Users;

class User
{
	public int $id;

	public string $name;

	public string $role;

	public string $gender;

	public string $rfidTag;

	public function __construct(int $id, string $name, string $role, string $gender, string $rfidTag)
	{
		$this->id = $id;
		$this->name = $name;
		$this->role = $role;
		$this->gender = $gender;
		$this->rfidTag = $rfidTag;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'role' => $this->role,
			'gender' => $this->gender,
			'rfidTag' => $this->rfidTag,
		];
	}

	public static function fromArray(array $data): self
	{
		return new self($data['id'], $data['name'], $data['role'], $data['gender'], $data['rfidTag']);
	}
}
