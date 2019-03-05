<?php
require "json-rpc.php";
require "api-config.php";

header('Content-type: application/json');

$rawdata = array_key_exists('data', $_GET) ? $_GET['data'] : '';

if(strlen($rawdata) > 0) {
  $id = 1;
  $json = getEtherRpc($api_host, $api_port, 'eth_sendRawTransaction', '"'.$rawdata.'"', $id++);

  $data = json_decode($json, true);
  $error = array_key_exists('error', $data) ? $data['error'] : null;

  $result = array();
  if($error) {
    $result['error'] = $error;
  }
  else {
    $txhash = $data["result"];
    $result['error'] = null;
    $result['result']['txhash'] = $txhash;
  }

  echo json_encode($result);
}
else {
  $result = array();
  $result['error']['code'] = '-1';
  $result['error']['message'] = 'Invalid data';

  echo json_encode($result);
}
