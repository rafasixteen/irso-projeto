<?php
declare(strict_types=1);

namespace App\Service;

class User
{
	public int $id;
	public string $name;
	public string $rfidTag;

	public function __construct(int $id, string $name, string $rfidTag)
	{
		$this->id = $id;
		$this->name = $name;
		$this->rfidTag = $rfidTag;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'rfidTag' => $this->rfidTag,
		];
	}

	public static function fromArray(array $data): self
	{
		return new self($data['id'], $data['name'], $data['rfidTag']);
	}
}