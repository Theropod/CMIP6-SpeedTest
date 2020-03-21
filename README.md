# CMIP6 Data Nodes Speed Test
## About this test
Use Chrome extension to Allow CORS and use webpage to test your download speed from all available cmip6 data nodes, and with telemetry enabled.
## Deployment
This site is modified from LibreSpeed/Speedtest, so the deployment procedures are the same.
I installed:
  - mysql
  - php
  - php-mysqli
  - phpmyadmin(not necessary)
  and changed settings in ./results/telemetry_settings.php for connecting the database
## Report Generation
run `./resources/result-update/generate_report_example.sh` to generate a html report from jupyter noteboook , which will be embedded in test-result.html (you may need corresponding envs installed and modify the file paths). I made a cron job to automate the generation.
## About the test datasource
- use XMLHttpRequest to test your speed to download a file from all available cmip6 data nodes in browser:
- /backend/getESGFDataNodes.php: The data node list is extracted from https://esgf-node.llnl.gov/search/cmip6/ and download url is extrated from the wget download script provided by cmip6 api. Changed http/https in some url to successfully access that website.
- Ping and Jitter are tested against the data nodes thredds/catalog/catalog.html page with 'HEAD' request
- Download speed is tested by downloading an nc file from selected data node(s)
## Why is this site in http?
Because most of the cmip6 data nodes are http(Although it is safer in https, it's impossible to force all of the nodes to upgrade). This speed test requires the browser to request those http resources, if this site is in https, the request would be blocked.
