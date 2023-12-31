<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Facades\Route;
use App\Filament\Resources\DocumentResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageFilesController extends Controller
{
    /**
     * function storageDocumentShow
     *
     * @param \Illuminate\Http\Request request
     * @param mixed $path = null
     *
     * @return JsonResponse|View|Response|BinaryFileResponse
     */
    public function storageDocumentShow(
        Request $request,
        $path = null,
        ?bool $download = null,
    ): JsonResponse|View|Response|BinaryFileResponse {
        $documentsDisk = DocumentResource::getDocumentDisk();
        $download ??= $request->input('download');

        abort_if(!filled($path) || !is_string($path), 'Fail');
        abort_if(!filled($documentsDisk) || !is_string($documentsDisk), 'Fail');

        $fullPath = Storage::disk($documentsDisk)->path($path);
        $this->validatePath($path, $documentsDisk);

        try {
            return $download ? response()->download($fullPath) : response()->file($fullPath);
        } catch (Throwable $th) {
            if (!app()->isProduction()) {
                throw $th;
            }

            abort(404);
        }
    }

    public function abortIf($condition, ?string $exceptionMessage = null)
    {
        if (!$condition) {
            return;
        }

        abort(404, $exceptionMessage);
    }

    public function validatePath(string|null $path, string|null $disk = null): void
    {
        $this->abortIf($path === null, 'Empty path');
        $fullPath = $disk ? Storage::disk($disk)->path($path) : Storage::path($path);
        $fileExists = $disk ? Storage::disk($disk)->exists($path) : Storage::exists($path);

        abort_unless($fileExists, 404);

        $allowedRoot = realpath(storage_path('app/public'));

        // `storage_path('app/public')` doesn't exist, so it cannot contain files
        $this->abortIf($allowedRoot === false, "Storage root doesn't exist");

        // User is attempting to access a file outside the $allowedRoot folder
        $this->abortIf(!str($fullPath)->startsWith($allowedRoot), 'Accessing a file outside the storage root');
    }

    public static function routes()
    {
        Route::get('/storage-documents/{path?}', [StorageFilesController::class, 'storageDocumentShow'])
            ->where('path', '(.*)')
            ->name('storage_documents.show');
    }
}
