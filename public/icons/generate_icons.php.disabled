<?php
// Simple PWA Icons Generator
function createIcon($size) {
    $image = imagecreatetruecolor($size, $size);
    
    // Background color (blue)
    $bg = imagecolorallocate($image, 59, 130, 246); // #3b82f6
    imagefill($image, 0, 0, $bg);
    
    // Text color (white)
    $text = imagecolorallocate($image, 255, 255, 255);
    
    // Font size based on icon size
    $fontSize = $size * 0.6;
    
    // Add text "P" in center
    $textX = $size / 2;
    $textY = $size / 2;
    
    // Use built-in font
    imagestring($image, 5, $textX - 10, $textY - 10, 'P', $text);
    
    return $image;
}

// Generate all required icons
$sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];

foreach ($sizes as $size) {
    $icon = createIcon($size);
    $filename = "icon-{$size}x{$size}.png";
    
    if (imagepng($icon, $filename)) {
        echo "Generated: $filename\n";
    } else {
        echo "Failed: $filename\n";
    }
    
    imagedestroy($icon);
}

echo "All icons generated successfully!\n";
?>

