<?php
require "json-rpc.php";
require "api-config.php";

header('Content-type: application/json');

$account = array_key_exists('account', $_GET) ? $_GET['account'] : '';

if(strlen($account) == 42 && ctype_alnum($account) && strpos($account, '0x') >= 0) {
  $id = 1;
  $json = getEtherRpc($api_host, $api_port, 'eth_getTransactionCount', '"'.$account.'", "latest"', $id++);

  $data = json_decode($json, true);
  $error = array_key_exists('error', $data) ? $data['error'] : null;

  $result = array();
  if($error) {
    $result['error'] = $error;
  }
  else {
    $value = $data["result"];
    $result['error'] = null;
    $result['result']['count'] = hexdec(str_replace('0x', '', $value));
  }

  echo json_encode($result);
}
else {
  $result = array();
  $result['error']['code'] = '-1';
  $result['error']['message'] = 'Invalid address';

  echo json_encode($result);
}
