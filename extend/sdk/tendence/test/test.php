<?php

require_once '../src/TLSSigAPIv2.php';

$api = new \Tencent\TLSSigAPIv2( 1400594375, 'ad6fc915e3992c9e7dc5e63a08a09ce31122e5b8a9868b3059b6678ad930f0e6' );
$sig = $api->genUserSig( 'Android_trtc_6078' );
echo $sig . "\n";
// $init_time = 0;
// $expire = 0;
// $err_msg = '';
// $ret = $api->verifySig( $sig, 'xiaojun', $init_time, $expire, $err_msg );
// if ( !$ret ) {
//     echo $err_msg . '\n';
// } else {
//     echo "verify ok expire $expire init time $init_time\n";
// }
// $userbuf = '';
// $ret = $api->verifySigWithUserBuf( $sig, 'xiaojun', $init_time, $expire, $userbuf, $err_msg );
// if ( !$ret ) {
//     echo $err_msg . '\n';
// } else {
//     echo "verify ok expire $expire init time $init_time userbuf $userbuf\n";
// }
// $sig = $api->genPrivateMapKey( 'xiaojun', 86400*180, 10000, 255 );
// echo $sig . '\n';
// $sig = $api->genPrivateMapKeyWithStringRoomID( 'xiaojun', 86400*180, "aaa", 255 );
// echo $sig . '\n';
// $init_time = 0;
// $expire = 0;
// $err_msg = '';
// $ret = $api->verifySig( $sig, 'xiaojun', $init_time, $expire, $err_msg );
// if ( !$ret ) {
//     echo $err_msg . '\n';
// } else {
//     echo "verify ok expire $expire init time $init_time\n";
// }
// $userbuf = '';
// $ret = $api->verifySigWithUserBuf( $sig, 'xiaojun', $init_time, $expire, $userbuf, $err_msg );
// if ( !$ret ) {
//     echo $err_msg . '\n';
// } else {
//     echo "verify ok expire $expire init time $init_time userbuf $userbuf\n";
// }
