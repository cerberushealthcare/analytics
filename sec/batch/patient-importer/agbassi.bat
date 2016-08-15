echo NOTE: This script will build patient import Higginbotham file
pause
cd C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\sec\batch\patient-importer
"C:\Program Files (x86)\PHP\php.exe" -f patient-import-sql.php agbassi AgbassiFile 
pause 