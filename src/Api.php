<?php

namespace kugouMusic;

use GuzzleHttp\Client;
use kugouMusic\Storage;

class Api {

    protected $_client;

    public function __construct()
    {
        $this->_client = new Client();
    }


    /**
     * 搜索
     * @param string $keyword
     * @param string $type 可选 track,album,artist,playlist
     * @param integer $page 页数，默认1
     * @param integer $pageSize 每页条数，默认15，最大50
     * @return array
     */
    public function search($keyword, $type = '', $page = 1, $pageSize = 15)
    {
        $page > 0 || $page = 1;
        $pageSize > 0 || $pageSize = 15;
        $url = 'https://api.kkbox.com/v1.1/search';
        $param = [
            'q' => $keyword,
            'type' => $type ?: 'track,album,artist,playlist',
            'territory' => 'HK', // 可选 HK,JP,MY,SG,TW
            'offset' => ($page - 1) * $pageSize,
            'limit' => $pageSize
        ];
        $url .= '?'.http_build_query($param);

        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        return $this->_success($data);
    }


    /**
     * 获取歌手信息
     * @param string $singerId 歌手ID
     * @return array
     */
    public function getSingerInfo($singerId)
    {
        $url = "https://gateway.kugou.com/v1/getfansnum";
        $param = [
            'appid' => 1000,
            // 'clientver' => 10329,
            // 'mid' => '95057cefcf0df9c68c56c389c86514661c85d345',
            // 'clienttime' => 1606136436,
            // 'key' => '4d68dddcd82e5eed74f9c01771e5a4c5',
            // 'p' => 'C6C1A5E29B8A35B6B833102949B7D60AAE97BC88BC3F777A7BE23FC3DDF034FA059BBDD2F87FE375CB64675B413A9D33DC5CB924086823AD6DC5B5581D721304A7A6A57349DC159A23C6019B36E6476983FE2D13103A49B09A5D8EB7F4BAB544FADA9494C83041858D3B6C163059860259CD62CEE961812A10F4B2DCBCF601AE',
            'p' => '06DD344443BB68275C6313F63F5258502868AAD20D5C7FE126BCD2DC73F3AB5DE1BC7A61244ECD1F5A51C5BEDC5005CC246E00F6770DA471E9882DD313FC91F7EE5340F836AECDF5BB44B12D7F5F77E952429628DD735A87060368F3E505F8629CDA31A14612CC5508541DB0BA5362F239A49CF1D61CA6E475A24AAB1F52DC29'
        ];
        $url .= '?' . http_build_query($param);
        try {
            $response = $this->_client->get($url, [
                'headers' => [
                    // 'User-Agent' => 'IPhone-10329-CloudPlayList',
                    // 'dfid' => '2lzruy3psWRe3Xmp790EpFbY',
                    // 'UNI-UserAgent' => 'iOS14.2-Phone10329-1009-0-WiFi',
                    'x-router' => 'followservice.kugou.com',
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        // file_put_contents('temp.txt', $result);
        
        // echo json_encode($response->getHeaders());die;
        // echo $result."\n";die;
        echo zlib_decode($result)."\n";die;
        

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
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        if ($data['errcode'] != 0) {
            return $this->_error($data['error']);
        }
        return $this->_success($data['data']);
    }

    /**
     * 获取歌手专辑
     * @param string $singerId 歌手ID
     * @return array
     */
    public function getSingerAlbums($singerId, $page = 1, $pageSize = 50)
    {
        $url = "https://www.kugou.com/yy/?r=singer/album";
        $param = [
            'sid' => $singerId,
            'p' => $page
        ];
        $url .= '?'.http_build_query($param);
        try {
            $response = $this->_client->get($url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        echo json_encode($result, JSON_UNESCAPED_UNICODE)."\n";
        $data = json_decode($result, true);
        unset($data['paging']);
        foreach ($data['data'] as &$val) {
            unset($val['artist']);
        }
        return $this->_success($data);
    }

    /**
     * 获取歌手热门歌曲
     * @param string $singerId 歌手ID
     * @return array
     */
    public function getSingerTopSongs($singerId)
    {
        $url = 'https://api.kkbox.com/v1.1/artists/'.$singerId.'/top-tracks?territory=HK&limit=500'; // limit最大500
        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        unset($data['paging']);
        return $this->_success($data);
    }

    /**
     * 获取专辑歌曲
     * @param string $albumId 专辑ID
     * @return array
     */
    public function getAlbumSongs($albumId)
    {
        $url = 'https://api.kkbox.com/v1.1/albums/'.$albumId.'/tracks?territory=HK&limit=500'; // limit最大500
        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        unset($data['paging']);
        return $this->_success($data);
    }

    /**
     * 获取歌曲信息
     * @param mixed $songIds 歌曲ID
     * @return array
     */
    public function getSongs($songIds)
    {
        if (is_array($songIds)) {
            $songIds = implode(',', $songIds);
        }
        $url = 'https://api.kkbox.com/v1.1/tracks?ids='.$songIds.'&territory=HK';
        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        isset($data['data']) && $data = $data['data'];
        return $this->_success($data);
    }

    /**
     * 获取榜单列表
     * @return array
     */
    public function getCharts()
    {
        $url = 'https://api.kkbox.com/v1.1/charts?territory=HK';
        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        unset($data['paging']);
        return $this->_success($data);
    }

    /**
     * 获取榜单歌曲
     * @param string $chartId 榜单ID
     * @return array
     */
    public function getChartSongs($chartId)
    {
        $url = 'https://api.kkbox.com/v1.1/charts/'.$chartId.'/tracks?territory=HK&limit=500';
        $option = ['headers' => ['authorization' => 'Bearer '. $this->_getToken()]];
        if ($this->_proxy) {
            $option['proxy'] = $this->_proxy;
        }
        try {
            $response = $this->_client->get($url, $option);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->_error('get token failed, [' . $e->getCode().'] '. $e->getMessage());
        }
        $result = $response->getBody()->getContents();
        $data = json_decode($result, true);
        unset($data['paging']);
        return $this->_success($data);
        
    }

    /**
     * 获取歌手所在榜单
     * @param string $singerIds 歌手ID
     * @return array
     */
    public function getSingerCharts($singerIds)
    {
        if (!is_array($singerIds)) {
            $singerIds = explode(',', $singerIds);
        }
        $singerIds = array_flip($singerIds);
        $charts = $this->getCharts()['data'];
        if (!$charts) {
            return $this->_error('no charts found.');
        }
        $data = [];
        foreach ($charts['data'] as $chart) {
            $list = $this->getChartSongs($chart['id'])['data'];
            if (!$list) {
                continue;
            }
            $songs = [];
            foreach ($list['data'] as $key => $song) {
                $singer = $song['album']['artist'];
                if (isset($singerIds[$singer['id']])) {
                    $song['rank'] = $key + 1;
                    $songs[] = $song;
                }
            }
            if ($songs) {
                $data[] = [
                    'chart' => $chart,
                    'songs' => $songs
                ];
            }
        }
        return $this->_success($data);
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