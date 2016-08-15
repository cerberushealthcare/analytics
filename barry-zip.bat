echo This will create archive barry-sec.zip
pause
"C:\Program Files\7-Zip\7z.exe" a barry-sec.zip sec\ -xr0!*.txt -xr0!*.psp -xr0!*.log -xr0!*.sql -xr0!*.zip -xr!barry -xr!client-import -xr!ftpvc