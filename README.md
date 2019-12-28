# CMIP6-SpeedTest
A download speed test for cmip6 data nodes, based on librespeed, with telemetry enabled

## About the test
- This site tests your speed to download a file from all available cmip6 data nodes in browser:
- The data node list is extracted from https://esgf-node.llnl.gov/search/cmip6/
- Ping and Jitter are tested against the data nodes
- Download speed is tested by downloading an nc file from selected data node(s)
### Why is this site in http?
- Because most of the cmip6 data nodes are http(Although it is safer in https, it's impossible to force all of the nodes to upgrade). This speed test requires the browser to request those http resources, if this site is in https, the request would be blocked.
## Deploy and modification
See https://github.com/librespeed/speedtest for detailed dedeployment instruction.
