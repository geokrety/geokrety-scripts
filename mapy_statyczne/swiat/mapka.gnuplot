set terminal png transparent nocrop enhanced font arial 8  size 680,430
set title "Where are GK now?"
set output '/home/geokrety/public_html/mapki/world.png'
#set style fill   solid 1.00 border -1
set yzeroaxis linetype 0 linewidth 1.000
set xzeroaxis linetype 0 linewidth 1.000
#plot 'kontur-gnuplot.dat' with lines lt 3 , 'world.cor' with impulses

set pm3d
set palette rgb 33,13,10
set view map
set cblabel "altitude [m]"
set cbrange [ 0 : * ] noreverse nowriteback

plot 'kontur-gnuplot.dat' with filledcurves lc rgb "light-green" notitle, \
'geokrety.dat' using 1:2:3 with points palette pt 5 ps 0.5 title "GKs", \
'home.dat' with points  pt 22 ps 0.5  lc rgb "orange" title "GK home"
#plot 'dane.dat' using 1:2:3 with points palette pt 5 ps 0.5 notitle

#'moleholes.dat' with points  pt 22 ps 1.5 lc rgb "red" title "moleholes", \