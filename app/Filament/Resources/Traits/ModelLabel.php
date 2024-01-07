<?php

namespace App\Filament\Resources\Traits;

trait ModelLabel
{
    public static function getTranslatedDotLabel(string $key, ?string $default = null): string
    {
        $key = str($key);
        $key = $key->startsWith('models.') ? $key->after('models.') : $key;

        $value = __(
            $key
                ->prepend('models.')
                ->toString()
        );

        if (!str($value)->startsWith('models.')) {
            return $value;
        }

        return $default ?? __(str($key)->headline()->title()->toString());
    }

    public static function getTranslatedLabel(string $key, string $parentKey, ?string $model = null): string
    {
        $model ??= str(
            method_exists(static::class, 'getModel') ? static::getModel() : ''
        )
            ->afterLast('\\')
            ->toString();

        $value = __(
            str($model)
                ->afterLast('\\')
                ->prepend('models.')
                ->append(".{$parentKey}")
                ->append(".{$key}")
                ->toString()
        );

        if (!str($value)->startsWith('models.')) {
            return $value;
        }

        $value = __(
            str('models')
                ->append('.fallback')
                ->append(".{$parentKey}")
                ->append(".{$key}")
                ->toString()
        );

        if (!str($value)->startsWith('models.')) {
            return $value;
        }

        return __(str($key)->headline()->title()->toString());
    }

    public static function getModelLabel(): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.modelLabel')
                ->toString()
        );
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.titleCaseModelLabel')
                ->toString()
        );
    }

    public static function getPluralModelLabel(): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.pluralModelLabel')
                ->toString()
        );
    }

    public static function getActionLabel(string $key, string|null $model = null): string
    {
        return static::getTranslatedLabel(
            parentKey: 'actions',
            key: $key,
            model: $model,
        );
    }

    public static function getTableAttributeLabel(string $key, string|null $model = null): string
    {
        return static::getTranslatedLabel(
            parentKey: 'table',
            key: $key,
            model: $model,
        );
    }

    public static function getFormAttributeLabel(string $key, string|null $model = null): string
    {
        return static::getTranslatedLabel(
            parentKey: 'form',
            key: $key,
            model: $model,
        );
    }

    public static function getFilterLabel(string $key, string|null $model = null): string
    {
        return static::getTranslatedLabel(
            parentKey: 'filters',
            key: $key,
            model: $model,
        );
    }

    public static function getNavigationBadge(): ?string
    {
        // return static::$model::whereColumn('qty', '<', 'security_stock')->count();
        return static::getModel()::count();
    }
}
