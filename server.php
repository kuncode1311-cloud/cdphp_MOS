<?php

/**
 * Bộ định tuyến cho `php artisan serve` (chạy trên Railway).
 *
 * Giống bộ định tuyến mặc định của Laravel, nhưng gắn thêm header cache cho file tĩnh (ảnh, video, CSS/JS, font)
 * để trình duyệt lưu lại trên máy: các lần tải trang sau không phải tải lại. File .htaccess chỉ có tác dụng trên
 * Apache nên không áp dụng được ở đây.
 */
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && is_file($publicPath.$uri)) {
    $file = $publicPath.$uri;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $cacheable = ['ico', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'mp3', 'wav', 'woff', 'woff2', 'ttf', 'otf', 'css', 'js'];

    // Chỉ gửi file nằm trong public (hoặc storage công khai qua liên kết public/storage), chặn đường dẫn kiểu ../
    $real = realpath($file);
    $allowedRoots = array_filter([realpath($publicPath), realpath($publicPath.'/../storage/app/public')]);
    $insideAllowed = $real !== false && array_reduce($allowedRoots, fn ($ok, $root) => $ok || str_starts_with($real, $root.DIRECTORY_SEPARATOR), false);

    if ($insideAllowed && in_array($ext, $cacheable, true)) {
        // File build của Vite có mã băm trong tên nên không bao giờ đổi nội dung: lưu 1 năm.
        // Ảnh/video khác giữ 7 ngày, sau đó trình duyệt chỉ hỏi lại (304) chứ không tải lại cả file.
        $isHashedBuild = str_starts_with($uri, '/build/assets/');
        $maxAge = $isHashedBuild ? 31536000 : 604800;
        header('Cache-Control: public, max-age='.$maxAge.($isHashedBuild ? ', immutable' : ''));

        $mtime = filemtime($file);
        $etag = '"'.dechex($mtime).'-'.dechex(filesize($file)).'"';
        header('ETag: '.$etag);
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');

        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
        $ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : null;
        $isRangeRequest = isset($_SERVER['HTTP_RANGE']);

        if (! $isRangeRequest && (($ifNoneMatch !== null && $ifNoneMatch === $etag) || ($ifNoneMatch === null && $ifModifiedSince !== null && $ifModifiedSince >= $mtime))) {
            http_response_code(304);

            return true;
        }

        // Tự gửi file (PHP không gắn header của router vào file tĩnh mặc định), có hỗ trợ tua video (Range)
        $mimes = [
            'ico' => 'image/x-icon', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'mp4' => 'video/mp4', 'webm' => 'video/webm', 'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav', 'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'otf' => 'font/otf',
            'css' => 'text/css; charset=utf-8', 'js' => 'application/javascript; charset=utf-8',
        ];
        $size = filesize($file);
        $start = 0;
        $end = $size - 1;
        header('Content-Type: '.$mimes[$ext]);
        header('Accept-Ranges: bytes');

        if ($isRangeRequest && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m) && ($m[1] !== '' || $m[2] !== '')) {
            if ($m[1] === '') {
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                $end = $m[2] === '' ? $end : min((int) $m[2], $end);
            }
            if ($start > $end || $start >= $size) {
                http_response_code(416);
                header('Content-Range: bytes */'.$size);

                return true;
            }
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        $length = $end - $start + 1;
        header('Content-Length: '.$length);

        if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
            return true;
        }

        $handle = fopen($file, 'rb');
        fseek($handle, $start);
        while ($length > 0 && ! feof($handle)) {
            $chunk = fread($handle, min(65536, $length));
            echo $chunk;
            $length -= strlen($chunk);
            flush();
            if (connection_aborted()) {
                break;
            }
        }
        fclose($handle);

        return true;
    }

    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
