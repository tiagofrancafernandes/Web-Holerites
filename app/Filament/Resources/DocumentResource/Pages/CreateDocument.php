<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\StorageFile;
use Illuminate\Support\Arr;

/**
 * @property ?array $data
 * @inheritDoc
 */
class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentUserId = auth()->user()->id;
        $data['created_by'] = $currentUserId;
        $data['document_category_id'] ??= $data['category_id'] ?? null;

        return DocumentResource::mutateDataVisibleTo($data);
    }

    protected function afterCreate(): void
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
