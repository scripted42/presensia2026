#!/usr/bin/env python3
"""
Simple PWA Icons Generator
Creates basic icons for PWA using PIL (Pillow)
"""

try:
    from PIL import Image, ImageDraw, ImageFont
    PIL_AVAILABLE = True
except ImportError:
    PIL_AVAILABLE = False
    print("PIL not available, creating simple icons...")

def create_simple_icon(size):
    """Create a simple icon using basic Python"""
    # Create a simple colored square
    # This is a fallback if PIL is not available
    print(f"Creating icon {size}x{size}...")
    
    # For now, create a simple text file as placeholder
    with open(f"icon-{size}x{size}.txt", "w") as f:
        f.write(f"PWA Icon {size}x{size}\n")
        f.write("=" * 20 + "\n")
        f.write("P\n")
        f.write("=" * 20 + "\n")
        f.write("This is a placeholder icon.\n")
        f.write("Replace with actual PNG file.\n")

def create_pil_icon(size):
    """Create icon using PIL"""
    if not PIL_AVAILABLE:
        return create_simple_icon(size)
    
    # Create image
    img = Image.new('RGB', (size, size), color='#3b82f6')
    draw = ImageDraw.Draw(img)
    
    # Add text "P"
    try:
        font = ImageFont.truetype("arial.ttf", size // 2)
    except:
        font = ImageFont.load_default()
    
    # Get text size
    bbox = draw.textbbox((0, 0), "P", font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    
    # Center text
    x = (size - text_width) // 2
    y = (size - text_height) // 2
    
    draw.text((x, y), "P", fill='white', font=font)
    
    # Save as PNG
    img.save(f"icon-{size}x{size}.png")
    print(f"Created: icon-{size}x{size}.png")

def main():
    sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512]
    
    print("Generating PWA icons...")
    
    for size in sizes:
        if PIL_AVAILABLE:
            create_pil_icon(size)
        else:
            create_simple_icon(size)
    
    print("Done!")

if __name__ == "__main__":
    main()

