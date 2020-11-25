<?php

namespace kugouMusic;

class ChartConfig {
    
    // 酷狗飙升榜
    const UP_CHART = 6666; 
    // 酷狗TOP500
    const TOP_CHART = 8888; 
    // 酷狗专辑畅销榜
    const BEST_SELL_CHART = 30946;
    // 酷狗雷达榜
    const LD_CHART = 37361;
    // 网络红歌榜
    const INFLUENCER_CHART = 23784;
    // DJ热歌榜
    const DJ_CHART = 24971;
    // 会员专享热歌榜
    const MENBER_CHART = 35811;
    // 华语新歌榜
    const HY_NEW_CHART = 31308;
    // 欧美新歌榜
    const OM_NEW_CHART = 31310;
    // 韩国新歌榜
    const KOREA_NEW_CHART = 31311;
    // 日本新歌榜
    const JAPAN_NEW_CHART = 31312;
    // 粤语新歌榜
    const CANTO_NEW_CHART = 31313;
    // ACG新歌榜
    const ACG_NEW_CHART = 33162;
    // 酷狗分享榜
    const SHARE_CHART = 21101;
    // 酷狗说唱榜
    const RAP_CHART = 44412;
    // 国风新歌榜
    const CHINOISERIE_NEW_CHART = 33161;
    // 综艺新歌榜
    const SHOW_CHART = 46910;
    // 影视金曲榜
    const TV_CHART = 33163;
    // 欧美金曲榜
    const OM_HOT_CHART = 33166;
    // 粤语金曲榜
    const CANTO_HOT_CHART = 33165;

    static $dayCharts = [
        self::UP_CHART,
        self::TOP_CHART,
        // self::BEST_SELL_CHART, // 每30分钟
        // 工作日
        self::LD_CHART, 
        self::HY_NEW_CHART, 
        // self::OM_NEW_CHART, 
        // self::KOREA_NEW_CHART,
        // self::JAPAN_NEW_CHART,
        self::CANTO_NEW_CHART,
    ];

    static $weekCharts = [
        // 周一
        // self::INFLUENCER_CHART, 
        // self::MENBER_CHART,
        // 周二
        self::SHARE_CHART, 
        // 周三
        // self::DJ_CHART,
        // self::ACG_NEW_CHART, 
        // self::CHINOISERIE_NEW_CHART,
        self::RAP_CHART,
        self::SHOW_CHART,
        self::TV_CHART,
        // self::OM_HOT_CHART,
        self::CANTO_HOT_CHART
    ];

}