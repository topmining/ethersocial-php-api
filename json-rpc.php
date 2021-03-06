<?php
function getEtherRpc($host, $port, $method, $params, $id) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_PORT, $port);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, '{"jsonrpc":"2.0","method":"'.$method.'","params":['.$params.'],"id":'.$id.'}');

	$ret = curl_exec($ch);
	curl_close($ch);

	return $ret;
}

function getEtherRpcMulti($host, $port, $list) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_PORT, $port);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POST, TRUE);

  	$data = "[";
	$n = 0;
	foreach($list as $item) {
		if($n > 0) $data .= ",";
		$data .= '{"jsonrpc":"2.0","method":"'.$item['method'].'","params":['.$item['params'].'],"id":'.$item['id'].'}';
		$n++;
	}
	$data .= "]";
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$ret = curl_exec($ch);
	curl_close($ch);

	return $ret;
}
