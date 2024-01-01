<?php

if (!function_exists('file_info')) {
    /**
     * function file_info
     *
     * @param string $path
     * @return \SplFileInfo
     */
    function file_info(string $path): \SplFileInfo
    {
        return new \SplFileInfo($path);
    }
}
