from PIL import Image
import numpy as np

src = r"public/general/assets/img/paloma-paz.png"
dst = r"public/general/assets/img/paloma-paz-transparent.png"

img = Image.open(src).convert("RGBA")
w, h = img.size

# Escalar a 3x para mejor procesamiento
img_big = img.resize((w * 3, h * 3), Image.LANCZOS)
data = np.array(img_big, dtype=np.float32)

r = data[:,:,0]
g = data[:,:,1]
b = data[:,:,2]

# Luminosidad perceptual
luma = 0.299 * r + 0.587 * g + 0.114 * b

# Saturación
cmax = np.maximum(np.maximum(r, g), b)
cmin = np.minimum(np.minimum(r, g), b)
sat  = (cmax - cmin) / (cmax + 1e-5)

# Fondo = muy claro (luma > 185) Y poca saturación (sat < 0.20)
is_bg = (luma > 185) & (sat < 0.20)

# Transición suave 185 → 245
fade = np.clip((luma - 185.0) / (245.0 - 185.0), 0, 1)

# Alpha: fondo = transparente, paloma = opaco
alpha_float = np.where(is_bg, (1.0 - fade) * 255, 255.0)

result = data.copy().astype(np.uint8)
result[:,:,3] = np.clip(alpha_float, 0, 255).astype(np.uint8)

# Reducir a 2x con LANCZOS (antialiasing perfecto)
out_img = Image.fromarray(result, "RGBA")
out_img = out_img.resize((w * 2, h * 2), Image.LANCZOS)
out_img.save(dst, "PNG", optimize=True)
print(f"OK - {w*2}x{h*2}px guardado en {dst}")
