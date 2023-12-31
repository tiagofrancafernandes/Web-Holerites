<?php

namespace App\Filament\Resources\Traits;

trait ModelLabel
{
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

    public static function getActionLabel(string $action): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.actions')
                ->append(".{$action}")
                ->toString()
        );
    }

    public static function getTableAttributeLabel(string $attribute): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.table')
                ->append(".{$attribute}")
                ->toString()
        );
    }

    public static function getFormAttributeLabel(string $attribute): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.form')
                ->append(".{$attribute}")
                ->toString()
        );
    }

    public static function getFilterLabel(string $filter): string
    {
        return __(
            str(static::getModel())
                ->afterLast('\\')
                ->prepend('models.')
                ->append('.filters')
                ->append(".{$filter}")
                ->toString()
        );
    }

    public static function getNavigationBadge(): ?string
    {
        // return static::$model::whereColumn('qty', '<', 'security_stock')->count();
        return static::getModel()::count();
    }
}
