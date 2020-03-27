<?php 
$doc = new DOMDocument;
// We don't want to bother with white spaces
$doc->preserveWhiteSpace = false;
// Most HTML Developers are chimps and produce invalid markup...
$doc->strictErrorChecking = false;
$doc->recover = true;
// default search page
$doc->loadHTMLFile('https://esgf-node.llnl.gov/search/cmip6/');
$xpath = new DOMXPath($doc);
// extract Data Nodes List from page html
$dataNodeHtml = $xpath->evaluate('string(//*[@id="search-form"]/div[1]/div/div[18]/div/div[2]/div/div)');
// remove all spaces and explode to array
$tmpNodeStr=preg_replace('/\s+/', '', $dataNodeHtml);
$tmpNodeStr=preg_replace('/\(/', '_', $tmpNodeStr);
$tmpNodeStr=preg_replace('/\)/', '_', $tmpNodeStr);
$dataNodes = explode("_", $tmpNodeStr);
// $test = json_encode($dataNodes,JSON_UNESCAPED_SLASHES);
// echo $nodesInfoJSON;
// var_dump($test);

// function to extract download url from esgf wget script
function extractDownloadUrl($node_name)
{
    $nodeWgetScriptUrl='https://esgf-node.llnl.gov/esg-search/wget?table_id=Amon&data_node='. $node_name . '&limit=20';
    echo("<script>console.log('".$nodeWgetScriptUrl."');</script>");  
    $nodeWgetScript=file_get_contents($nodeWgetScriptUrl);
    if( $nodeWgetScript ) { 
        echo '<script>console.log("get wget sh file successful")</script>';
        if(preg_match_all('/http.*nc/',$nodeWgetScript,$dlUrl)){
            echo '<script>console.log("dl url found")</script>';
            // get the 15th result to avoid small test files on data nodes
            return $dlUrl[0][10];
        }
        else {
            throw new Exception('dl url not found');
            echo '<script>console.log("dl url not found")</script>';
        }
    } 
    else { 
        echo '<script>console.log("get wget sh file failed")</script>';
        throw new Exception('get wget sh file failed');
    }
}

function getIpInfoTokenString(){
    $apikeyFile="getIP_ipInfo_apikey.php";
    if(!file_exists($apikeyFile)) return "";
    require $apikeyFile;
    if(empty($IPINFO_APIKEY)) return "";
    return "?token=".$IPINFO_APIKEY;
}

function getIPInfofromDomain($node_name){
    $ip=gethostbyname($node_name);
    $ip = preg_replace("/^::ffff:/", "", $ip);

    if ($ip == "::1") { // ::1/128 is the only localhost ipv6 address. there are no others, no need to strpos this
        echo json_encode(['processedString' => $ip . " - localhost IPv6 access", 'rawIspInfo' => ""]);
        die();
    }
    if (stripos($ip, 'fe80:') === 0) { // simplified IPv6 link-local address (should match fe80::/10)
        echo json_encode(['processedString' => $ip . " - link-local IPv6 access", 'rawIspInfo' => ""]);
        die();
    }
    if (strpos($ip, '127.') === 0) { //anything within the 127/8 range is localhost ipv4, the ip must start with 127.0
        echo json_encode(['processedString' => $ip . " - localhost IPv4 access", 'rawIspInfo' => ""]);
        die();
    }
    if (strpos($ip, '10.') === 0) { // 10/8 private IPv4
        echo json_encode(['processedString' => $ip . " - private IPv4 access", 'rawIspInfo' => ""]);
        die();
    }
    if (preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $ip) === 1) { // 172.16/12 private IPv4
        echo json_encode(['processedString' => $ip . " - private IPv4 access", 'rawIspInfo' => ""]);
        die();
    }
    if (strpos($ip, '192.168.') === 0) { // 192.168/16 private IPv4
        echo json_encode(['processedString' => $ip . " - private IPv4 access", 'rawIspInfo' => ""]);
        die();
    }
    if (strpos($ip, '169.254.') === 0) { // IPv4 link-local
        echo json_encode(['processedString' => $ip . " - link-local IPv4 access", 'rawIspInfo' => ""]);
        die();
    }

    $isp = "";
    $rawIspInfo=null;
    try {
        $json = file_get_contents("https://ipinfo.io/" . $ip . "/json".getIpInfoTokenString());
        $details = json_decode($json, true);
        $rawIspInfo=$details;
        if (array_key_exists("org", $details)){
            $isp .= $details["org"];
            $isp=preg_replace("/AS\d{1,}\s/","",$isp); //Remove AS##### from ISP name, if present
        }else{
            $isp .= "Unknown ISP";
        }
        if (array_key_exists("country", $details)){
            $isp .= ", " . $details["country"];
        }
        $clientLoc = NULL;
        if (array_key_exists("loc", $details)){
            $clientLoc = $details["loc"];
        }
    } catch (Exception $ex) {
        $isp = "Unknown ISP";
    }
    return array('processedString' => $ip . " - " . $isp, 'rawIspInfo' => $rawIspInfo);
}

// build data node information dictionary {'name':'datanode_name', 'dataset count':countnumber, 'dl_url':'download_url from wget script'}
$nodesInfo=array();
for ($i = 0; $i < count($dataNodes)/2-1; $i++) {
    $ipinfo=getIPInfofromDomain($dataNodes[2*$i]);
    // var_dump($ipinfo);
    $nodeInfo=array(
        "node_name" => $dataNodes[2*$i],
        "node_ip" => $ipinfo["processedString"],
        "node_isp_info" => $ipinfo["rawIspInfo"],
        "dataset_count" => $dataNodes[2*$i+1],
        "dl_url" => extractDownloadUrl($dataNodes[2*$i]),
    );
    $nodesInfo[] = $nodeInfo;
} 
// to json
$nodesInfoJSON = json_encode($nodesInfo,JSON_UNESCAPED_SLASHES);
echo $nodesInfoJSON;
?>
