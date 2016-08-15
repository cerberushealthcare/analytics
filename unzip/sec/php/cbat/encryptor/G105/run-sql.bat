echo audits
"c:\program files (x86)\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_AuditRec_105_001.sql
echo scheds
"c:\program files (x86)\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_Sched_105_001.sql
echo done
pause

