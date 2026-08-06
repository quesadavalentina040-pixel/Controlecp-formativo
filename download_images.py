import urllib.request
import os

os.makedirs('public/general/assets/img', exist_ok=True)

try:
    urllib.request.urlretrieve('https://geminiusercontent.com/Pq7ZfIu.png', 'public/general/assets/img/hero-img.png')
    print("Downloaded hero-img.png successfully")
except Exception as e:
    print(f"Failed to download hero-img: {e}")

try:
    urllib.request.urlretrieve('https://geminiusercontent.com/hD9eC9O.png', 'public/general/assets/img/sena-empresa.png')
    print("Downloaded sena-empresa.png successfully")
except Exception as e:
    print(f"Failed to download sena-empresa.png: {e}")
