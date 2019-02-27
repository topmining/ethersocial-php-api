<?php
require "api-config.php";
require __DIR__."/vendor/autoload.php";

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

header('Content-type: application/json');

$account = array_key_exists('account', $_GET) ? $_GET['account'] : '';

if(strlen($account) == 42 && ctype_alnum($account) && strpos($account, '0x') >= 0) {
  $wei = "1000000000000000000";

  $conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);

  $pagesize = 30;

  $result['result']['address'] = $account;
  $result['result']['history'] = array();

  if($query = $conn->query("select * from txaccount where fromaddr='$account' or toaddr='$account' order by blocknumber desc limit 0, $pagesize")) {
    while($row = $query->fetch_array()) {
      $txhash = $row['hash'];
      $blocknumber = $row['blocknumber'];
      $fromaddr = $row['fromaddr'];
      $toaddr = $row['toaddr'];
      $timestamp = $row['timestamp'];
      $value = BigDecimal::of($row['value'])->dividedBy($wei, 9, Brick\Math\RoundingMode::DOWN);

      $result['result']['history'][] = array(
        'hash' => $txhash,
        'blocknumber' => $blocknumber,
        'fromaddr' => $fromaddr,
        'toaddr' => $toaddr,
        'timestamp' => $timestamp,
        'value' => $value);
    }
    $query->close();

    echo json_encode($result);
  }

  $conn->close();
}
else {
  $result = array();
  $result['error']['code'] = '-1';
  $result['error']['message'] = 'Invalid address';

  echo json_encode($result);
}
