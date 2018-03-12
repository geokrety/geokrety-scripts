set xlabel "GK moved by users"
set ylabel "counts"
set terminal png transparent nocrop enhanced font arial 8  size 640,400
set output '/home/geokrety/public_html/templates/wykresy/histogram_moved.png'
set style fill   solid 1.00 border -1
set style histogram clustered gap 1 title  offset character 0, 0, 0
binwidth=1
set boxwidth binwidth
bin(x,width)=width*floor(x/width) + binwidth/2.0
plot 'dane.dat' using (bin($25,binwidth)):(1.0) smooth freq with boxes notitle
