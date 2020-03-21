#!/bin/bash
## run this script every hour, to update access and speedtest data into html report
# using goaccess to generate a static report html based on all access logs: every hour
source /home/ubuntu/miniconda3/bin/activate base
sudo -- bash -c 'zcat /var/log/apache2/access.log.*.gz | goaccess /var/log/apache2/access.log /var/log/apache2/access.log.1 - -o /var/www/html/report.html --log-format=COMBINED'
# export csv file from mysql database speedtest, table speedtest_users: every hour
sudo -- bash -c 'rm /var/lib/mysql-files/*.csv' # delete all existing file in mysql output directory
mysql -uroot -p'mypass' -Dmydatabase</home/ubuntu/CMIP6-SpeedTest/resources/result-update/speedtest_result_export.sql
filename=$(sudo bash -c  'filename= ls -tr1 /var/lib/mysql-files| tail -1') # only select the latest file
sudo rm /home/ubuntu/CMIP6-SpeedTest/*.csv
sudo cp /var/lib/mysql-files/$filename ~/CMIP6-SpeedTest #copy it from mysql output directory to working directory
cd ~/CMIP6-SpeedTest
sudo sed -i '1 i\id,timestamp,ip,ispinfo,server,ua,lang,dl,ul,ping,jitter,log' $filename
# generate html, original file is jupyter notebook
rm Speedtest_Result.html
jupyter nbconvert Speedtest_Result.ipynb --execute --no-input
sudo cp Speedtest_Result.html  /var/www/html
