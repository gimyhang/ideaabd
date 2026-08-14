<?php
$zip = new ZipArchive();
$filename = 'deploy.zip';
if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit('Cannot open <$filename>\n');
}

$rootPath = realpath(__DIR__);
$excludes = ['.git', 'node_modules', 'vendor', 'deploy.zip'];

$files = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        function ($current, $key, $iterator) use ($excludes, $rootPath) {
            $subPath = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $current->getPathname());
            foreach ($excludes as $exclude) {
                if (str_starts_with($subPath, $exclude)) {
                    return false;
                }
            }
            return true;
        }
    ),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}
$zip->close();
echo 'deploy.zip created successfully!';
