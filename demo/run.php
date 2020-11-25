<?php

use kugouMusic\Api;

include_once __DIR__.'/../src/Api.php';
include_once __DIR__.'/../vendor/autoload.php';


$api = new Api();
$ret = $api->search('容祖儿');
// $ret = $api->getSingerInfo('5785');
// $ret = $api->getSingerSongs('5785', 1, 10);
// $ret = $api->getSingerAlbums('5785');
// $ret = $api->getListCollectionNum(5785, 39618301);
// $ret = $api->getAlbumInfo([39618301,24593038]);
// $ret = $api->getAlbumSongs('39618301');
// $ret = $api->getSongRankTop([171444851,277577912]);
// $ret = $api->getSongCommentNum('3AF29B9361BFC1FCFC8C1D8B5EADA11F');
$ret = $api->getSingersRankInfo('容祖儿');
echo json_encode($ret, JSON_UNESCAPED_UNICODE)."\n";