reset

set title "Where are GK now?"
set term gif animate nocrop enhanced font arial 8  size 680,430
#set term gif animate nocrop enhanced font arial 8  size 180,130
#set output "/home/geokrety/public_html/mapki/globus-animate-small.gif"
set output "/home/geokrety/public_html/mapki/globus-animate.gif"


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
#set view 56,31

set parametric
set isosamples 25
set urange[0:360]
set vrange[-90:90]

r = 0.99


n=70    #n frames
dt=360/n
i=0

load "globus-animowany-ins.gnuplot"
