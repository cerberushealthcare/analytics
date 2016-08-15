@echo off
set _php="C:\Program Files (x86)\PHP\php.exe"
set _builder="C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\sec\php\data\xml\_utils\class-builder.php"
set _path=C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\sec\php\data\xml\
echo == START OF XML CLASS BUILDER ==
pause
echo+
set path=ccd\schemas
cd %_path%%path%
set in=datatypes-base.xsd
set out=datatypes-base-out.php
%_php% -f %_builder% %in% %out%
set in=POCD_MT000040.xsd
set out=POCD_MT000040-out.php
%_php% -f %_builder% %in% %out%
echo+
echo == END OF XML CLASS BUILDER ==
pause 
