SET @TS = DATE_FORMAT(NOW(),'_%Y_%m_%d_%H_%i_%s');
SET @FOLDER = '/var/lib/mysql-files/';
SET @PREFIX = 'speedtest_result';
SET @EXT    = '.csv';
SET @CMD = CONCAT("SELECT id, timestamp, ip, ispinfo, extra, ua, lang, dl, ul, ping, jitter,log FROM speedtest_users INTO OUTFILE '",@FOLDER,@PREFIX,@TS,@EXT,"' FIELDS ENCLOSED BY '\"' TERMINATED BY ',' ESCAPED BY '\"' LINES TERMINATED BY '\r\n';");
PREPARE statement FROM @CMD;
EXECUTE statement;
