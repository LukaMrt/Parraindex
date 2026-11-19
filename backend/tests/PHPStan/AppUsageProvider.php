<?php

declare(strict_types=1);

namespace App\Tests\PHPStan;

use App\Api\ErrorCode;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use ReflectionEnumUnitCase;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

/**
 * Usages invisibles pour l'analyse statique (dead-code-detector) :
 * appels faits par le framework via réflexion, PropertyAccess ou sérialisation.
 */
final class AppUsageProvider extends ReflectionBasedMemberUsageProvider
{
    #[\Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        if ($method->getAttributes(AdminRoute::class) !== []) {
            return VirtualUsageData::withNote('Action EasyAdmin (#[AdminRoute])');
        }

        $isEntity   = str_starts_with($method->getDeclaringClass()->getName(), 'App\\Entity\\');
        $isAccessor = preg_match('/^(get|set|is|has|add|remove)[A-Z]/', $method->getName()) === 1;

        if ($isEntity && $isAccessor) {
            return VirtualUsageData::withNote("Accesseur d'entité (PropertyAccess : EasyAdmin, formulaires)");
        }

        return null;
    }

    #[\Override]
    protected function shouldMarkPropertyAsRead(ReflectionProperty $property): ?VirtualUsageData
    {
        $isDto = str_starts_with($property->getDeclaringClass()->getName(), 'App\\Dto\\');

        if ($isDto && $property->isPublic()) {
            return VirtualUsageData::withNote('Propriété de DTO lue par le serializer (ApiResponse)');
        }

        return null;
    }

    #[\Override]
    protected function shouldMarkEnumCaseAsUsed(ReflectionEnumUnitCase $enumCase): ?VirtualUsageData
    {
        if ($enumCase->getDeclaringClass()->getName() === ErrorCode::class) {
            return VirtualUsageData::withNote('Miroir du type ErrorCode du frontend');
        }

        return null;
    }
}
