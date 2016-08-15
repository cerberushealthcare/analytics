echo run encryption sql
pause
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_Client_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_DataSync_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_Proc_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_Session_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_HL7Inbox_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_MsgThread_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_PortalUser_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_Sched_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_TrackItem_1_001.sql
"c:\program files\mysql\mysql server 5.1\bin\mysql" -uroot -pclick01 -Demrtest < Sql_AuditRec_1_001.sql
echo %time% Finished.
pause
