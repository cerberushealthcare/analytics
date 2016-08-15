echo This will create archive sec.zip 
pause
"C:\Program Files\7-Zip\7z.exe" a sec.zip sec\ -xr0!*.txt -xr0!*.php_ -xr0!*.old -xr0!*.project -xr0!*.cert -xr0!*.psp -xr0!*.log -xr0!*.sql -xr0!*.csv -xr0!*.zip -xr0!*.pdf -xr!user-folders -xr!client-import -xr!ftpvc -xr!mpdf -xr!tcpdf -xr!fonts -xr!tiny_mce -xr!testing -xr!notused -xr!_not_used -xr!samples -xr!out -xr!_prod
pause