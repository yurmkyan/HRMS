<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_GET['refresh'])) {
    unset($_SESSION['captcha_hash'], $_SESSION['captcha_created'], $_SESSION['captcha_display']);
}

$code = captcha_code();
$width = 210;
$height = 64;
$image = imagecreatetruecolor($width, $height);
$background = imagecolorallocate($image, 14, 31, 53);
$ink = imagecolorallocate($image, 236, 244, 255);
$blue = imagecolorallocate($image, 85, 184, 255);
$coral = imagecolorallocate($image, 255, 118, 101);
$muted = imagecolorallocate($image, 130, 164, 202);

imagefill($image, 0, 0, $background);

for ($index = 0; $index < 90; $index++) {
    $color = [$blue, $coral, $muted][random_int(0, 2)];
    imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
}

for ($index = 0; $index < 7; $index++) {
    $color = [$blue, $coral, $muted][random_int(0, 2)];
    imageline($image, random_int(0, 30), random_int(0, $height), random_int($width - 30, $width), random_int(0, $height), $color);
}

$fontCandidates = [
    __DIR__ . '/assets/fonts/DejaVuSans-Bold.ttf',
    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
];
$windowsDirectory = getenv('WINDIR');
if ($windowsDirectory) {
    $fontCandidates[] = rtrim($windowsDirectory, '\\/') . '/Fonts/segoeui.ttf';
    $fontCandidates[] = rtrim($windowsDirectory, '\\/') . '/Fonts/arial.ttf';
}
$font = null;
foreach ($fontCandidates as $candidate) {
    if (is_file($candidate)) {
        $font = $candidate;
        break;
    }
}
$spacing = 37;
foreach (str_split($code) as $position => $character) {
    $angle = random_int(-18, 18);
    $x = 12 + ($position * $spacing);
    $y = random_int(43, 52);
    if ($font !== null) {
        imagettftext($image, 28, $angle, $x, $y, $ink, $font, $character);
    } else {
        imagestring($image, 5, $x, 23, $character, $ink);
    }
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
imagepng($image);
imagedestroy($image);
