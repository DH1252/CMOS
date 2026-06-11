from PIL import Image

img = Image.open('/mnt/c/Users/LEGION/Pictures/Screenshots/Screenshot 2026-06-09 002505.png')
rgb_img = img.convert('RGB')
width, height = img.size

# sample middle row
y = height // 2
for x in range(0, width, width // 10):
    print(f"X: {x}, Color: {rgb_img.getpixel((x, y))}")
