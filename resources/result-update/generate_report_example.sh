#!/bin/bash
## run this script every hour, to update access and speedtest data into html report
source /home/ubuntu/miniconda3/bin/activate base
# using goaccess to generate a static report html based on all access logs, ignore crawlers
sudo -- bash -c 'zcat /var/log/apache2/access.log.*.gz | goaccess /var/log/apache2/access.log /var/log/apache2/access.log.1 - -o /var/www/html/access_report.html --log-format=COMBINED -q --ignore-crawlers'
# change accesss log page title
sed -i 's/Dashboard/CMIP6 Data Nodes Speed Test/g' /var/www/html/access_report.html
# using goaccess to generate request log in json, only selecting wanted pages, ignore crawlers and remove all irrelevant panel data
sudo -- bash -c "{ zcat /var/log/apache2/access.log.*.gz & cat /var/log/apache2/access.log & cat /var/log/apache2/access.log.1; } | grep 'GET / HTTP\|GET /contact.html\|GET /index.html\|GET /test-result.html\|GET /test.html\|GET /tutorial.html\| GET /resources/result-update/Speedtest_Result.html\|GET /Speedtest_Result.html\|GET /report.html\|GET /access_report.html\| GET /backend/getESGFDataNodes.php\|GET /phpmyadmin' | goaccess - -o /var/www/html/access_report.json --log-format=COMBINED -q --ignore-crawlers --ignore-panel=VISITORS --ignore-panel=REQUESTS_STATIC --ignore-panel=NOT_FOUND --ignore-panel=HOSTS --ignore-panel=OS --ignore-panel=BROWSERS --ignore-panel=VISIT_TIMES --ignore-panel=VIRTUAL_HOSTS --ignore-panel=REFERRERS --ignore-panel=REFERRING_SITES --ignore-panel=KEYPHRASES --ignore-panel=STATUS_CODES --ignore-panel=REMOTE_USER --ignore-panel=GEO_LOCATION"

# export csv file from mysql database speedtest, table speedtest_users
sudo -- bash -c 'rm /var/lib/mysql-files/*.csv' # delete all existing file in mysql output directory
mysql -uroot -p'your_password' -Dyour_db</home/ubuntu/CMIP6-SpeedTest/resources/result-update/speedtest_result_export.sql
resultfile=$(sudo bash -c  'filename= ls -tr1 /var/lib/mysql-files| grep speedtest_result |tail -1') # only select the latest file
distributionfile=$(sudo bash -c  'filename= ls -tr1 /var/lib/mysql-files| grep speed_distribution |tail -1') # only select the latest file
sudo rm ~/CMIP6-SpeedTest/resources/result-update/speedtest_result*.csv /var/www/html/resources/result-update/speed_distribution*.csv
sudo cp /var/lib/mysql-files/$resultfile ~/CMIP6-SpeedTest/resources/result-update #copy it from mysql output directory to working directory
sudo cp /var/lib/mysql-files/$distributionfile /var/www/html/resources/result-update #copy it from mysql output directory to working directory
cd /home/ubuntu/CMIP6-SpeedTest/resources/result-update/
# add column name to resultfile
sudo sed -i '1 i\id,timestamp,ip,ispinfo,server,ua,lang,dl,ul,ping,jitter,log' $resultfile
# convert 

# generate html, original file is jupyter notebook
rm Speedtest_Result.html
jupyter nbconvert Speedtest_Result.ipynb --execute --no-input
sudo cp Speedtest_Result.html  /var/www/html/resources/result-update