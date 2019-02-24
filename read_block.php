<?php
require "json-rpc.php";
require "api-config.php";
require __DIR__."/vendor/autoload.php";

use Brick\Math\BigInteger;

$tonumber = 0;
$id = 1;
$json = getEtherRpc($api_host, $api_port, 'eth_blockNumber', '"latest"', $id++);
if($json) {
  $data = json_decode($json, true);
  if($data) {
    $tonumber = hexdec($data['result']);
    echo "Current block height: $tonumber\n";
  }
}

$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$fromblock = 0;
if($query = $conn->query("select max(blocknumber) from block")) {
  if($row = $query->fetch_row()) {
    $fromblock = $row[0] ? $row[0] + 1 : 0;
  }
  $query->close();
}
else {
  $conn->close();
  die("Read block error");
}

$blocknumber=$fromblock;
while($blocknumber<=$tonumber) {
  $list = [];
  for($i=0; $i<100; $i++) {
    if($blocknumber > $tonumber) break;
    $list[] = array(
      "method" => "eth_getBlockByNumber",
      "params" => '"0x'.dechex($blocknumber).'", true',
      "id" => $id++
    );
    $blocknumber++;
  }

  $json = getEtherRpcMulti($api_host, $api_port, $list);
  $datas = json_decode($json, true);

  foreach($datas as $data) {
    $result = $data['result'];
    if($result) {
      $hash = $result['hash'];
      $timestamp = hexdec($result['timestamp']);
      $trcount = 0;
      $transactions = $result['transactions'];
      if($transactions && count($transactions) > 0) {
        $trcount = count($transactions);

        foreach($transactions as $tr) {
          $txhash = $tr['hash'];
          $fromaddr = $tr['from'];
          $toaddr = $tr['to'];
          $bnumber = hexdec($tr['blockNumber']);
          $blockhash = $tr['blockHash'];
          $txindex = hexdec($tr['transactionIndex']);
          $gas = BigInteger::parse(str_replace("0x", "", $tr['gas']), 16);
          $gasprice = BigInteger::parse(str_replace("0x", "", $tr['gasPrice']), 16);
          $value = BigInteger::parse(str_replace("0x", "", $tr['value']), 16);
          $input = $tr['input'];
          $v = $tr['v'];
          $r = $tr['r'];
          $s = $tr['s'];
          $nonce = $tr['nonce'];

          // 블록체인 공격자 제외
          if(strcasecmp($fromaddr, "0x2f16af67dbd141c53beb03a533de6ab3bd0e69df") == 0) continue;

          $sql = "insert into txaccount (hash, fromaddr, toaddr, timestamp, blocknumber,
            blockhash, txindex, gas, gasprice, value, input, v, r, s, nonce) values (
            '$txhash', '$fromaddr', '$toaddr', $timestamp, $bnumber,
            '$blockhash', $txindex, $gas, $gasprice, $value, '$input', '$v', '$r', '$s', '$nonce')
    				on duplicate key update
    				fromaddr='$fromaddr', toaddr='$toaddr', timestamp=$timestamp, blocknumber=$bnumber,
            blockhash='$blockhash', txindex=$txindex, gas=$gas, gasprice=$gasprice, value=$value,
            input='$input', v='$v', r='$r', s='$s', nonce='$nonce'";

          if(!$conn->query($sql)) {
            $conn->close();
            die("Write txaccount error");
          }
        }
      }

      $bnumber = hexdec($result['number']);
      $sql = "insert into block (blocknumber, timestamp, blockhash, transactions)
        values ($bnumber, $timestamp, '$hash', $trcount)
        on duplicate key update timestamp=$timestamp, blockhash='$hash', transactions=$trcount";
      if(!$conn->query($sql)) {
        $conn->close();
        die("Write block error");
      }
    }
  }

  echo "Block: $blocknumber\n";
}

$conn->close();
