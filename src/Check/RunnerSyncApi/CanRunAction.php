<?php

declare(strict_types=1);

namespace Keboola\PermissionChecker\Check\RunnerSyncApi;

use Keboola\PermissionChecker\BranchType;
use Keboola\PermissionChecker\Exception\PermissionDeniedException;
use Keboola\PermissionChecker\Feature;
use Keboola\PermissionChecker\PermissionCheckInterface;
use Keboola\PermissionChecker\Role;
use Keboola\PermissionChecker\StorageApiToken;

class CanRunAction implements PermissionCheckInterface
{
    public function __construct(
        private readonly BranchType $branchType,
        private readonly string $componentId,
    ) {
    }

    public function checkPermissions(StorageApiToken $token): void
    {
        if (!$token->hasAllowedComponent($this->componentId)) {
            throw PermissionDeniedException::missingComponent($this->componentId);
        }

        if ($token->hasFeature(Feature::PROTECTED_DEFAULT_BRANCH)) {
            $this->checkProtectedDefaultBranch($token->getRole());
        } elseif ($token->isRole(Role::READ_ONLY)) {
            throw PermissionDeniedException::roleDenied($token->getRole(), 'run actions');
        }
    }

    private function checkProtectedDefaultBranch(Role $role): void
    {
        $isAllowed = match ($role) {
            Role::PRODUCTION_MANAGER => $this->branchType === BranchType::DEFAULT,
            Role::DEVELOPER, Role::REVIEWER => $this->branchType !== BranchType::DEFAULT,
            default => false,
        };

        if (!$isAllowed) {
            throw new PermissionDeniedException(sprintf(
                'Role "%s" is not allowed to run actions on %s branch',
                $role->value,
                $this->branchType->value,
            ));
        }
    }
}
