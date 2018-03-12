set view 56,dt*i

splot r*cos(v)*cos(u),r*cos(v)*sin(u),r*sin(v) with lines lc rgb '#c0c0c0' lt 2 lw 1, \
      'kontur-gnuplot.dat' with lines lt 3 lc rgb 'black', \
    'geokrety.dat' using 1:2 with points pt 0.6 ps 0.5 lc rgb 'blue' title sprintf("t=%i",i)

i=i+1
if (i <= (n)) reread