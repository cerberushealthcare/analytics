echo This will create archive papyrus.zip
pause
"C:\Program Files\7-Zip\7z.exe" a papyrus.zip sec\ -xr0!*.txt -xr0!*.psp -xr0!*.log -xr0!*.sql -xr0!*.zip -xr0!*.pdf -xr!user-folders -xr!client-import -xr!batch -xr!ftpvc