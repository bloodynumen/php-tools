<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
/**
* @file ATingPush.class.php
* @brief 推送类
* @author sunhuai(v_sunhuai@baidu.com)
* @version 
* @date 2012-12-3 13:28:22
 */

 
class ATingPush extends ATingExt {
    protected $_logFile = NULL;
    public $_logDir = NUll;
    public $_logPath = NULL;
    protected $pushUrl = 'http://10.40.71.98:8001/mmsg/r';//线下地址 

    public function init() {
        $this->_logDir = ROOT_PATH.'/data/ting/appLog/';
        if (!TingExtUtil::createDir($this->_logDir)) {
            exit("dir error");
        }
        if ($this->_logFile) {
            $this->_logPath = $this->_logDir . $this->_logFile;
        }
        else {
            exit('log file coult not empty');
        }
    }

    public function request($url, $data = array(), $method  = 'POST', $isHttps = TRUE, $cookie = NULL) {
        //return json_encode(array('error_no'=>1));
        $ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL			=>	$url,
            CURLOPT_CONNECTTIMEOUT	=>	1,
            CURLOPT_TIMEOUT		=>	1,
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_HEADER		=>	false,
            CURLOPT_FOLLOWLOCATION	=>	true,
            CURLOPT_USERAGENT => 'ting',
        );
        $curlOptionsLog = array(
            'CURLOPT_URL'			=>	$url,
            'CURLOPT_CONNECTTIMEOUT'	=>	1,
            'CURLOPT_TIMEOUT'		=>	1,
            'CURLOPT_RETURNTRANSFER'	=>	true,
            'CURLOPT_HEADER'		=>	false,
            'CURLOPT_FOLLOWLOCATION'	=>	true,
            'CURLOPT_USERAGENT' => 'ting',
        );
        if('POST' === $method)
        {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        if(true === $isHttps)
        {
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }
        if(isset($cookie))
        {
            $curlOptions[CURLOPT_COOKIE] = $cookie;
        }
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $this->writeLog('send status', $opt = array('curlOptions' => $curlOptionsLog, 'response' => $response, 'errno' => $errno));
        if(0 != $errno)
        {
            return FALSE;
        }
        curl_close($ch);
        return $response;
    }

    //为百度音乐app服务的格式化$ext 目前包含ipad的
    public function getFormatExt($type, $ext) {
        $extContent = array();
        $extContent['type'] = $type;
        switch($type) {
            case 1 :
                $extContent['songid'] = $ext;//歌曲
                break;
            case 2 :
                $extContent['album_id'] = $ext;//专辑
                break;
            case 3 :
                $extContent['code'] = $ext;//Ting精选
                break;
            case 4 :
                $extContent['code'] = 'ten';
                break;
            case 5 :
                $extContent['billlist_id'] = $ext;//榜单
                break;
            case 6 :
                $extContent['artist_id'] = $ext;//歌手
                break;
            case 7 :
                //春节活动 无实体
                break;
            default :
                exit();
                break;
        }
        return json_encode($extContent);
    }

    //为艺人app服务的格式化$ext
    public function getAppFormatExt($appid, $type, $ext) {
        $extContent = array();
        $extContent['appid'] = $appid;
        $extContent['type'] = $type;
        switch($type) {
            case 1 :
                $extContent['song_id'] = $ext;//歌曲
                break;
            case 2 :
                $extContent['album_id'] = $ext;//专辑
                break;
            case 3 :
                $extContent['news_id'] = $ext;//资讯
                break;
            case 4 :
                //$extContent[''] = $ext;//专辑
                break;
            case 5 :
                $extContent['post_id'] = $ext;//帖子
                break;
            case 6 :
                $extContent['video_id'] = $ext;//视频
                break;
            default :
                exit();
                break;
        }
        return json_encode($extContent);
    }

    public function writeLog($words,$opt = array()) {
        $words .= '|||' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $words .= '|||' . json_encode($opt);
        $words .= "\r\n";
        return file_put_contents($this->_logPath, $words, FILE_APPEND);
    }

    public function checkLen($ext, $content) {
        $checkResult = array();
        $checkResult['check'] = TRUE;
        $checkResult['len'] = strlen($ext . json_encode($content));
        if ($checkResult['len'] > 400) {
            $checkResult['check'] = FALSE;
        }
        return $checkResult;
    }

}
