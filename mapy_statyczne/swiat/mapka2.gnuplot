reset

set terminal png transparent nocrop enhanced font arial 8  size 680,430
set title "Where are GK now?"
set output '/home/geokrety/public_html/mapki/world2.png'

# color definitions
set border lw 1.5

unset key; unset border 
set tics scale 0
set lmargin screen 0
set bmargin screen 0 
set rmargin screen 1
set tmargin screen 1 
set format ''

set mapping spherical
set angles degrees
set hidden3d
# Set xy-plane to intersect z axis at -1 to avoid an offset between the lowest z
# value and the plane
set xyplane at -1
#set view 56,81
set view 56,180

set parametric
set isosamples 25
set urange[0:360]
set vrange[-90:90]

r = 0.99
splot r*cos(v)*cos(u),r*cos(v)*sin(u),r*sin(v) with lines lc rgb '#c0c0c0' lt 2 lw 1, \
      'kontur-gnuplot.dat' with lines lt 3 lc rgb 'black', \
    'geokrety.dat' using 1:2 with points pt 1 ps 0.5 lc rgb 'orange' title "GKs"
