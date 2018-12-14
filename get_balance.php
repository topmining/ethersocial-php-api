<?php
ini_set('display_errors', 'On');

require "json-rpc.php";
require "api-config.php";
require __DIR__."/vendor/autoload.php";

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;

header('Content-type: application/json');

$account = $_GET['account'];

$id = 1;
$json = getEtherRpc($api_host, $api_port, 'eth_getBalance', '"'.$account.'", "latest"', $id++);

$data = json_decode($json, true);
$value = $data["result"];
$value = str_replace("0x", "", $value);

$wei = BigInteger::parse($value, 16);
$ether = BigDecimal::of($wei)->dividedBy("1000000000000000000", 9, RoundingMode::DOWN);
$balance = "$ether";

$result = array();
$result["result"] = array("balance"=>$balance);

echo json_encode($result);
