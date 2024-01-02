<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Concerns\EvaluatesClosures;
use Closure;

class EditDocument extends EditRecord
{
    use EvaluatesClosures;

    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(static::getResource()::getActionLabel('delete')),
        ];
    }

    public function evaluateContent(null|Closure $content = null): mixed
    {
        return $this->evaluate($content, [
            'record' => $this->getRecord(),
        ]) ?? null;
    }
}
