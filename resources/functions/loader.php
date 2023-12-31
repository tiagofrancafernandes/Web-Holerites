<?php

$filesToLoad = [
    __DIR__ . '/helpers.php',
];

foreach($filesToLoad as $filesPath) {
    require_once $filesPath;
}
