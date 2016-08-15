echo This will create archive test.zip
pause
"C:\Program Files\7-Zip\7z.exe" a test.zip sec\ -xr0!*.txt -xr0!*.psp -xr0!*.log -xr0!*.sql -xr0!*.zip -xr0!*.pdf -xr!user-folders -xr!client-import -xr!ftpvc -xr!songbook -xr!chordbook -xr!flight -xr!skywatch