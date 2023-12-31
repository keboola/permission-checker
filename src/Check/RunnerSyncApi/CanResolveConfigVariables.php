<?php

declare(strict_types=1);

namespace Keboola\PermissionChecker\Check\RunnerSyncApi;

use Keboola\PermissionChecker\Exception\PermissionDeniedException;
use Keboola\PermissionChecker\PermissionCheckInterface;
use Keboola\PermissionChecker\StorageApiToken;

class CanResolveConfigVariables implements PermissionCheckInterface
{
    public function checkPermissions(StorageApiToken $token): void
    {
        $componentIds = [
            'keboola.shared-code',
            'keboola.variables',
        ];

        foreach ($componentIds as $componentId) {
            if (!$token->hasAllowedComponent($componentId)) {
                throw new PermissionDeniedException(sprintf(
                    'You do not have permission to read configurations of "%s" component',
                    $componentId,
                ));
            }
        }
    }
}
