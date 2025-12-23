<?php
declare(strict_types=1);

namespace App\Rfids;

final class Permission
{
	/**
	 * @param string $doorType
	 * @param array<int,string> $allowedRoles
	 * @param array<string,bool> $conditions
	 */
	public function __construct(public readonly string $doorType, public readonly array $allowedRoles, public readonly array $conditions) {}

	public static function fromArray(array $data): self
	{
		$conditions = [];

		foreach ($data['conditions'] ?? [] as $key => $value) {
			$conditions[(string) $key] = (bool) $value;
		}

		return new self($data['door_type'], $data['allowed_roles'] ?? [], $conditions);
	}
}