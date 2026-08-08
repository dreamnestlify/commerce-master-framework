from PIL import Image, ImageDraw, ImageFont
import glob
import os

image_dir = "/Users/jianjun/Downloads/Telegram Desktop"
output_path = "/Users/jianjun/WorkBuddy/2026-08-08-22-28-49/uploads/jewelry-contact-sheet.jpg"

# Get all jewelry images and sort by number
image_paths = sorted(
    glob.glob(os.path.join(image_dir, "photo_*.jpg")),
    key=lambda p: int(os.path.basename(p).split('_')[1])
)

print(f"Found {len(image_paths)} images")

# Grid: 8 columns x 6 rows
ncols = 8
nrows = 6
thumb_w = 240
thumb_h = 240
padding = 10
text_h = 20

sheet_w = ncols * (thumb_w + padding) + padding
sheet_h = nrows * (thumb_h + text_h + padding) + padding

sheet = Image.new('RGB', (sheet_w, sheet_h), (255, 255, 255))
draw = ImageDraw.Draw(sheet)

try:
    font = ImageFont.truetype("/System/Library/Fonts/PingFang.ttc", 14)
except:
    font = ImageFont.load_default()

for idx, path in enumerate(image_paths):
    if idx >= ncols * nrows:
        break
    
    col = idx % ncols
    row = idx // ncols
    x = padding + col * (thumb_w + padding)
    y = padding + row * (thumb_h + text_h + padding)
    
    img = Image.open(path)
    img.thumbnail((thumb_w, thumb_h))
    # Center the thumbnail in the cell
    paste_x = x + (thumb_w - img.width) // 2
    paste_y = y + (thumb_h - img.height) // 2
    sheet.paste(img, (paste_x, paste_y))
    
    label = os.path.basename(path).replace('.jpg', '')
    draw.text((x, y + thumb_h + 2), label, fill=(0, 0, 0), font=font)

sheet.save(output_path, quality=90)
print(f"Saved contact sheet: {output_path}")
