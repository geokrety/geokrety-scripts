set terminal png size 1024, 600
set output '/var/www/html/templates/wykresy/aktywni_userzy.png'

set key font ",8"

set style data histogram
set style histogram cluster gap 1
set xtics 4
set xtics rotate
set style fill solid border rgb "black"
set auto x
set yrange [0:*]
plot 'out/aktywni_userzy.dat' using 2:xtic(1) title "users", \
        '' using 3:xtic(1) title "heavy users"
