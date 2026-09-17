Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$epubPath = "storage/app/public/ebooks/briksh-zkhn-ktha-ble.epub"
$sourceDir = "scratch/clean_epub_build"

if (Test-Path $epubPath) {
    Remove-Item $epubPath -Force
}

$zipStream = [System.IO.File]::Open($epubPath, [System.IO.FileMode]::Create)
$archive = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create)

# 1. Add mimetype without compression
$mimetypeEntry = $archive.CreateEntry("mimetype", [System.IO.Compression.CompressionLevel]::NoCompression)
$mimetypeWriter = New-Object System.IO.StreamWriter($mimetypeEntry.Open())
$mimetypeWriter.Write("application/epub+zip")
$mimetypeWriter.Dispose()

# 2. Add all other files with Optimal compression
$sourceDirItem = Get-Item $sourceDir
$files = Get-ChildItem -Path $sourceDir -Recurse -File | Where-Object { $_.Name -ne "mimetype" }
foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($sourceDirItem.FullName.Length + 1).Replace("\", "/")
    $entry = $archive.CreateEntry($relativePath, [System.IO.Compression.CompressionLevel]::Optimal)
    $entryStream = $entry.Open()
    $fileStream = [System.IO.File]::OpenRead($file.FullName)
    $fileStream.CopyTo($entryStream)
    $fileStream.Dispose()
    $entryStream.Dispose()
}

$archive.Dispose()
$zipStream.Dispose()

Write-Host "Created EPUB at: $epubPath with size: $((Get-Item $epubPath).Length) bytes"
