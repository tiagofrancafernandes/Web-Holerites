<?php

use Illuminate\Http\UploadedFile;

if (!function_exists('file_info')) {
    /**
     * function file_info
     *
     * @param string $path
     * @param ?string $originalName
     * @param string|null $mimeType
     * @param int|null $error
     * @param bool|null $test
     * @return UploadedFile
     */
    function file_info(
        string $path,
        ?string $originalName = null,
        string|null $mimeType = null,
        int|null $error = null,
        bool|null $test = false,
    ): UploadedFile
    {
        $originalName ??= pathinfo($path, PATHINFO_BASENAME);

        return new UploadedFile(
            $path,
            $originalName,
            $mimeType,
            $error,
            $test,
        );
    }
}
