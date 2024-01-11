<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Concerns\EvaluatesClosures;
use Closure;
use Illuminate\Support\Arr;
use App\Models\StorageFile;
use App\Enums\DocumentVisibleToType;

/**
 * @property ?array $data
 * @inheritDoc
 */
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $visibleToType = ($data['visible_to_type'] ?? null);
        $visibleToType = is_numeric($visibleToType) ? DocumentVisibleToType::tryFrom((int) $visibleToType) : null;
        $data['visible_to_type'] = $visibleToType ?: ($data['visible_to_type'] ?? null);

        if ($visibleToType === DocumentVisibleToType::GROUP) {
            $data['visible_to_group'] = $data['visible_to'] ?? null;
        }

        if ($visibleToType === DocumentVisibleToType::USER) {
            $data['visible_to_user'] = $data['visible_to'] ?? null;
        }

        return $data;
    }

    public function evaluateContent(null|Closure $content = null): mixed
    {
        return $this->evaluate($content, [
            'record' => $this->getRecord(),
        ]) ?? null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['created_by'] ??= auth()->user()->id;
        $data['document_category_id'] ??= $data['category_id'] ?? null;

        return DocumentResource::mutateDataVisibleTo($data);
    }

    protected function afterSave(): void
    {
        /**
         * @var ?StorageFile $storageFile
         */
        $storageFile = DocumentResource::storeDocumentFile(
            Arr::wrap($this->data ?? []),
            uploadedBy: auth()->user()?->id,
            referenceClass: DocumentResource::getModel(),
        );

        if ($storageFile) {
            $record = $this->getRecord();

            $record->update([
                'storage_file_id' => $storageFile?->id,
            ]);
        }
    }
}
