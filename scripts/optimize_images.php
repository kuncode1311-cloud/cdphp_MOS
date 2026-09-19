<?php

$dir = __DIR__ . '/../public/images';
$files = glob($dir . '/*.{jpg,jpeg,png}', GLOB_BRACE);

foreach ($files as $file) {
    $info = getimagesize($file);
    if (!$info) continue;

    $mime = $info['mime'];
    $origSize = filesize($file);
    $origName = basename($file);

    echo "Processing $origName ($origSize bytes)... ";

    if ($mime === 'image/jpeg') {
        $img = imagecreatefromjpeg($file);
        if ($img) {
            // Compress JPEG with quality 82
            imagejpeg($img, $file, 82);
            imagedestroy($img);
        }
    } elseif ($mime === 'image/png') {
        $img = imagecreatefrompng($file);
        if ($img) {
            imagepalettetotruecolor($img);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            // Compress PNG
            imagepng($img, $file, 9);
            imagedestroy($img);
        }
    }

    clearstatcache();
    $newSize = filesize($file);
    $saved = round((1 - $newSize / $origSize) * 100, 1);
    echo "New size: $newSize bytes (Saved {$saved}%)\n";
}
