<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\StorageFile;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $filePath = $data['document_file']['path'] ?? null;
        $fileOriginalName = $data['document_file']['original_name'] ?? null;

        if (!$filePath) {
            throw new \Exception(__('Fail to load document file'), 1);
        }

        $fileExtension = pathinfo($fileOriginalName, PATHINFO_EXTENSION) ?: pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = pathinfo($fileOriginalName, PATHINFO_BASENAME) ?: pathinfo($filePath, PATHINFO_BASENAME);
        $fileSize = Storage::disk(DocumentResource::getDocumentDisk())->size($filePath);

        $currentUserId = auth()->user()->id;

        $storageFile = StorageFile::create([
            'disk_name' => DocumentResource::getDocumentDisk(),
            'path' => $filePath,
            'extension' => $fileExtension,
            'size_in_kb' => $fileSize,
            'file_name' => $fileName,
            'original_name' => $fileOriginalName,
            'public' => $data['public'] ?? false,
            'uploaded_by' => $currentUserId,
            'reference_class' => DocumentResource::getModel(),
        ]);

        $data['storage_file_id'] = $storageFile?->id;
        $data['created_by'] = $currentUserId;

        unset($data['document_file']);

        return $data;
    }
}
