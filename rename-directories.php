<?php

$baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'Magento';
$directories = scandir($baseDir);

foreach ($directories as $file) {
    if (!is_dir($baseDir . DIRECTORY_SEPARATOR . $file) || $file === '..' || $file === '.') {
        continue;
    }

    $newFilename = $file;
    if (strpos($newFilename, 'module-') === 0) {
        $newFilename = str_replace('module-', '', $newFilename);
    } elseif (strpos($newFilename, 'theme-') === 0) {
        $newFilename = str_replace('theme-', '', $newFilename);
    }
    $newFilename = ucwords($newFilename, '-');
    $newFilename = str_replace('-', '', $newFilename);

    rename(
        $baseDir . DIRECTORY_SEPARATOR . $file,
        $baseDir . DIRECTORY_SEPARATOR . $newFilename
    );
}
