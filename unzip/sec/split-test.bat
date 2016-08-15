set folder=user-folders\G%1\scan-batch
set name=%2
cd %folder%
pdfimages -j "%name%" ./%name%
for /f %%a IN ('dir /b *.ppm') do (ppmquant 256 "%%a" | ppmtogif > "%%a.gif")
del *.ppm
pause