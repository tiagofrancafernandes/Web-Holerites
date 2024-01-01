<?php

namespace App\Filament\Concerns\Default;

class PageHelpers
{
    /**
     * resourceGetUrl function
     *
     * @param string|object $resource
     * @param string $routeName
     * @param array $parameters
     * @param mixed ...$aditionalParameters
     *
     * @return string|null
     */
    public static function resourceGetUrl(
        string|object $resource,
        string $routeName,
        array $parameters = [],
        ...$aditionalParameters,
    ): ?string {
        if (!method_exists($resource, 'getUrl')) {
            return null;
        }

        return call_user_func([$resource, 'getUrl'], ...[
            $routeName,
            $parameters,
            ...$aditionalParameters,
        ]);
    }

    /**
     * resourceUrlIsCurrent function
     *
     * @param mixed ...$params
     *
     * @return boolean
     */
    public static function resourceUrlIsCurrent(
        ...$params
    ): bool {
        return static::resourceGetUrl(...$params) === url()->current();
    }

    /**
     * resourcePages function
     *
     * @param string|object $resource
     *
     * @return array
     */
    public static function resourcePages(
        string|object $resource,
    ): array {
        if (!method_exists($resource, 'getPages')) {
            return [];
        }

        return (array) (call_user_func([$resource, 'getPages']) ?? null);
    }
}
