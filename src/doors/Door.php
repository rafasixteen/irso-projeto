<?php
declare(strict_types=1);

namespace App\Doors;

class Door
{
	public string $id;

	public string $type;

	public ?string $allowedGender;

	public function __construct(string $id, string $type, ?string $allowedGender = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->allowedGender = $allowedGender;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'allowed_gender' => $this->allowedGender,
		];
	}

	public static function fromArray(array $data): self
	{
		return new self($data['id'], $data['type'], $data['allowed_gender'] ?? null);
	}
}
