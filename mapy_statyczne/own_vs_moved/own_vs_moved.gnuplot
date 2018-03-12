set terminal png transparent nocrop enhanced font arial 8  size 640,400
set xlabel "moved"; set ylabel "created";
set title "created vs moved"
set output '/home/geokrety/public_html/templates/wykresy/created_vs_moved.png'
set pm3d
set palette rgb 33,13,10
set view map
plot 'dane.dat' using 1:2:3 with points palette pt 5 ps 0.5 notitle
