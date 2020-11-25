<?php

namespace kugouMusic;

use GuzzleHttp\Client;

class Api
{

    protected $_client;

    public function __construct()
    {
        $this->_client = new Client();
    }


    /**
     * 搜索
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        $url = 'https://gateway.kugou.com/api/v3/search/keyword_recommend_multi';
        $param = [
            'apiver' => '14',
            'osversion' => '6.0.1',
            'plat' => '0',
            'nocorrect' => '0',
            'userid' => '0',
            'version' => '0',
            'keyword' => $keyword
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url, [
                'headers' => ['x-router' => 'msearch.kugou.com']
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('search failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data']['info'] ?: [];
        return $this->_success($data);
    }


    /**
     * 获取歌手信息
     * @param integer $singerId 歌手ID
     * @return array
     */
    public function getSingerInfo($singerId)
    {
        $url = "http://mobilecdngz.kugou.com/api/v3/singer/info";
        $param = [
            'singerid' => $singerId,
            'singername' => '容祖儿',
            'version' => 10329,
            'plat' => 2,
            'with_listener_index' => 1
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get singer info failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        if ($data['errcode'] != 0) {
            return $this->_error($data['error']);
        }
        return $this->_success($data['data']);
    }


    /**
     * 获取歌手歌曲
     * @param integer $singerId
     * @param integer $page
     * @param integer $pageSize
     * @return array
     */
    public function getSingerSongs($singerId, $page = 1, $pageSize = 50)
    {
        $url = 'http://mobilecdnbj.kugou.com/api/v3/singer/song';
        $param = [
            'singerid' => $singerId,
            'page' => $page,
            'pagesize' => $pageSize,
            'sorttype' => 0, // 默认按热度排序，1是按时间排序
            'area_code' => 1
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get singer songs failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data']['info'] ?: [];
        $songs = [];
        foreach ($data as $val) {
            $filename = explode('-', $val['filename']);
            $singername = rtrim($filename[0]);
            $songname = ltrim($filename[1]);
            $songs[] = [
                'id' => $val['audio_id'],
                'album_id' => $val['album_id'],
                'name' => $songname,
                'hash' => $val['hash'],
                'publish_date' => $val['publish_date'],
                'duration' => $val['duration'],
                'remark' => $val['remark'],
                'sing_name' => $singername,
                'album_audio_id' => $val['album_audio_id']
            ];
        }
        return $this->_success($songs);
    }


    /**
     * 获取歌手专辑
     * @param integer $singerId 歌手ID
     * @return array
     */
    public function getSingerAlbums($singerId, $page = 1, $pageSize = 50)
    {
        $url = 'http://mobilecdnbj.kugou.com/api/v3/singer/album';
        $param = [
            'singerid' => $singerId,
            'page' => $page,
            'pagesize' => $pageSize
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get singer albums failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data']['info'] ?: [];
        return $this->_success($data);
    }


    /**
     * 获取专辑收藏数
     * @param integer $singerId
     * @param integer $albumId
     * @return array
     */
    public function getListCollectionNum($singerId, $albumId)
    {
        $url = 'https://gateway.kugou.com/v1/get_song_collect_status';
        $param = [
            'list_create_userid' => $singerId,
            'appid' => '1005',
            'source' => 2,
            'list_create_listid' => $albumId,
            'mid' => '147210170508080006059062317931575972186',
            'clientver' => '10359',
            'userid' => '0'
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url, [
                'headers' => ['x-router' => 'cloudlist.service.kugou.com']
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get collection num failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['error_code'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data'] ?: [];
        return $this->_success($data);
    }



    /**
     * 获取专辑信息
     * @param mixed $albumIds
     * @return array
     */
    public function getAlbumInfo($albumIds)
    {
        if (!is_array($albumIds)) {
            $albumIds = explode(',', $albumIds);
        }
        $array = [];
        foreach ($albumIds as $id) {
            $array[] = ['album_id' => $id];
        }
        $url = 'https://gateway.kugou.com/container/v1/album';
        $param = [
            'appid' => 1005,
            'clienttime' => microtime(true),
            'clientver' => 10359,
            'data' => $array,
            'key' => '571faa90f10f752c15d927cbd696c526',
            'mid' => '147210170508080006059062317931575972186',
        ];
        try {
            $response = $this->_client->post($url, [
                'headers' => ['x-router' => 'kmr.service.kugou.com'],
                'body' => json_encode($param)
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get album info failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['error_code'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data'];
        return $this->_success($data);
    }


    /**
     * 获取专辑歌曲
     * @param string $albumId 专辑ID
     * @return array
     */
    public function getAlbumSongs($albumId)
    {
        $url = 'http://mobilecdn.kugou.com/api/v3/album/song';
        $param = [
            'version' => '9108',
            'albumid' => $albumId,
            'plat' => 0,
            'page' => 1,
            'pagesize' => 100,
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get album songs failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        echo json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        die;
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data'] ?: [];
        $songs = [];
        foreach ($data['info'] as $val) {
            $filename = explode('-', $val['filename']);
            $singername = rtrim($filename[0]);
            $songname = ltrim($filename[1]);
            $songs[] = [
                'id' => $val['audio_id'],
                'album_id' => $val['album_id'],
                'name' => $songname,
                'hash' => $val['hash'],
                'publish_date' => $val['publish_date'],
                'duration' => $val['duration'],
                'remark' => $val['remark'],
                'sing_name' => $singername,
                'album_audio_id' => $val['album_audio_id']
            ];
        }
        return $this->_success(['songs' => $songs, 'total' => $data['total']]);
    }


    /**
     * 获取歌曲评论数
     * @param string $hash
     * @return array
     */
    public function getSongCommentNum($hash)
    {
        $url = 'https://gateway.kugou.com/index.php';
        $param = [
            'r' => 'comments/getcommentsnum',
            'code' => 'fc4be23b4e972707f36b8a828a93ba8a',
            'hash' => $hash,
            'clienttime' => microtime(true),
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url, [
                'headers' => ['x-router' => 'sum.comment.service.kugou.com']
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get song comments num failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if (isset($result[$hash])) {
            return $this->_success($result[$hash]);
        }
        return $this->_success(0);
    }


    /**
     * 获取歌曲榜单信息
     * @param mixed $songIds
     * @return array
     */
    public function getSongRankTop($songIds)
    {
        if (!is_array($songIds)) {
            $songIds = explode(',', $songIds);
        }
        $array = [];
        foreach ($songIds as $id) {
            $array[] = ['album_audio_id' => $id];
        }
        $url = 'https://gateway.kugou.com/container/v1/rank/top';
        $param = [
            'appid' => 1005,
            'clienttime' => microtime(true),
            'clientver' => 10359,
            'data' => $array,
            'key' => '',
            'mid' => '147210170508080006059062317931575972186'
        ];
        try {
            $response = $this->_client->post($url, [
                'headers' => ['x-router' => 'kmr.service.kugou.com'],
                'body' => json_encode($param)
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get song rank top failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['error_code'] != 0) {
            return $this->_error($result['error_code']);
        }
        $data = $result['data'] ?: [];
        $data = array_filter($data);
        return $this->_success($data);
    }


    /**
     * 获取榜单歌曲列表
     * @param integer $chartId
     * @return array
     */
    public function getChartSongs($rankId, $volid = '')
    {
        // 榜单信息
        $url = 'http://mobilecdnbj.kugou.com/api/v3/rank/info';
        $param = [
            'version' => 9108,
            'rankid' => $rankId
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get song rank top failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $info = $result['data'];
        // 榜单歌曲
        $url = 'http://mobilecdnbj.kugou.com/api/v3/rank/song';
        $param = [
            'version' => 9108,
            // 'plat' => 0,
            'page' => 1,
            'pagesize' => 500,
            'rankid' => $rankId
        ];
        if ($volid) {
            $param['volid'] = $volid;
        }
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get chart songs failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        $data = $result['data']['info'] ?: [];
        $songs = [];
        foreach ($data as $key => $val) {
            $filename = explode('-', $val['filename']);
            $songs[] = [
                'id' => $val['audio_id'],
                'album_id' => $val['album_id'],
                'name' => ltrim($filename[1]),
                'hash' => $val['hash'],
                'addtime' => $val['addtime'],
                'remark' => $val['remark'],
                'sing_name' => rtrim($filename[0]),
                'album_audio_id' => $val['album_audio_id'],
                'rank' => $key + 1,
                'update_type' => in_array($rankId, ChartConfig::$dayCharts) ? 1 : 0,
                'rank_title' => $info['rankname'],
                'top_id' => $rankId
            ];
        }
        return $this->_success($songs);
    }


    /**
     * 获取往期榜单歌曲
     * @param integer $rankId
     * @return array
     */
    public function getHistoryChartSongs($rankId)
    {
        $url = 'http://mobilecdnbj.kugou.com/api/v3/rank/vol';
        $param = [
            'version' => 9108,
            'rankid' => $rankId
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get history chart songs failed, [' . $e->getCode() . '] ' . $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        echo json_encode($result, JSON_UNESCAPED_UNICODE)."\n";die;
        if ($result['errcode'] != 0) {
            return $this->_error($result['error']);
        }
        $data = $result['data']['info'][0]['vols'] ?: [];
        $ranklist = [];
        foreach ($data as $vol) {
            $songs = $this->getChartSongs($rankId, $vol['volid'])['data'];
            if ($songs) {
                foreach ($songs as $key => $val) {
                    $songs[$key]['title_share'] = $val['title'].'-'.$vol['title'];
                }
                $ranklist = array_merge($ranklist, $songs);
            }
        }
        return $this->_success($ranklist);
    }


    /**
     * 获取歌手排行榜数据
     * @param string $singerName 歌手名
     * @param array $rankIds 排行榜ID，为空就用配置中默认的
     * @return array
     */
    public function getSingersRankInfo($singerName, $rankIds = [])
    { 
        if (!$rankIds) {
            // 配置中包含的榜单
            $rankIds = array_merge(ChartConfig::$dayCharts, ChartConfig::$weekCharts);
        }
        $songs = [];
        foreach ($rankIds as $rankId) {
            $data = $this->getChartSongs($rankId);
            if ($data['ret']) {
                $list = $data['data'];
                foreach ($list as $key => $song) {
                    if (mb_strpos($song['sing_name'], $singerName) === false) {
                        unset($list[$key]);
                    }
                }
                $list = array_values($list);
                if ($list) {
                    $songs = array_merge($songs, $list);
                }
            }
            usleep(500);
        }
        return $this->_success($songs);
    }


    private function _success($data = [])
    {
        return ['ret' => true, 'data' => $data, 'msg' => ''];
    }

    private function _error($msg = '')
    {
        return ['ret' => false, 'data' => null, 'msg' => $msg];
    }
}
