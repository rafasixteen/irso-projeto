<?php
declare(strict_types=1);

namespace App\Access;

use App\Access\DoorPermission;
use App\Doors\Door;
use App\Users\User;

class AccessService
{
	private string $permissionsFile = __DIR__ . '/../../storage/doors/permissions.json';

	private bool $isGymOpen = false;

	public function checkAccess(User $user, Door $door): array
	{
		$permissions = $this->loadPermissions();
		$matchingPermissions = array_filter($permissions, fn($permission) => $permission->doorType === $door->type);

		if (empty($matchingPermissions)) {
			return ['authorized' => false, 'reason' => 'no matching permission'];
		}

		foreach ($matchingPermissions as $permission) {
			if (!in_array($user->role, $permission->allowedRoles, true)) {
				continue;
			}

			$allConditionsMet = true;

			foreach ($permission->conditions as $condition => $expected) {
				switch ($condition) {
					case 'gym_open':
						if ($expected !== $this->isGymOpen) {
							$allConditionsMet = false;
						}
						break;

					case 'gender_match':
						if ($expected && $door->allowedGender !== null && $user->gender !== $door->allowedGender) {
							$allConditionsMet = false;
						}
						break;
				}

				if (!$allConditionsMet) {
					break;
				}
			}

			if ($allConditionsMet) {
				return ['authorized' => true, 'reason' => 'access granted'];
			}
		}

		return ['authorized' => false, 'reason' => 'no permission matched'];
	}

	/** @return DoorPermission[] */
	private function loadPermissions(): array
	{
		if (!file_exists($this->permissionsFile)) {
			return [];
		}

		return array_map(fn(array $perm) => DoorPermission::fromArray($perm), json_decode(file_get_contents($this->permissionsFile), true) ?? []);
	}
}
