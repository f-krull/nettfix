convert nflogo_n.png -filter Lanczos -define icon:auto-resize=64,48,32,16 -unsharp 2x1.0+0.9+0 ../src/web/img/favicon.ico
convert nflogo_n.png -filter Lanczos -resize x360 ../src/web/img/nettfix_n.png
convert nflogo.png   -filter Lanczos -resize 25% -unsharp 2x1.0+0.9+0 ../src/web/img/nettfix.png
convert nflogo.png   -filter Lanczos -resize x40 -unsharp 2x1.0+0.9+0 ../src/web/img/nettfix_x40.png