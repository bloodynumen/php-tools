<?php
/**
 * Ting全局函数库
 *
 * @category	global
 * @package		LibTing
 * @author		zhaoshunyao <zhaoshunyao@baidu.com>
 * @version		$Revision: 1.0$
 */
class LibTing
{
    /**
     * 获取用户连接WEB服务器的真实IP。如果采用代理，则返回的是代理IP。
     *
     * @param string $strDefaultIp 获取失败默认IP地址
     * @return string
     */
    public function clientIP($strDefaultIp = '0.0.0.0')
    {
        $strIp = '';
        $unknown = 'unknown'; 
        if(isset($_SERVER['HTTP_CLIENTIP']) && $_SERVER['HTTP_CLIENTIP'] && strcasecmp($_SERVER['HTTP_CLIENTIP'], $unknown))
        {
            $strIp = strip_tags($_SERVER['HTTP_CLIENTIP']);
        }
        elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown))
        {
            $strIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $strIp = strip_tags(trim($strIp));
            $intPos = strrpos($strIp, ',');
            if($intPos > 0)
            {
                $strIp = substr($strIp, $intPos + 1);
            }
        }
        elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown))
        {
            $strIp = strip_tags($_SERVER['REMOTE_ADDR']);
        }

        $strIp = trim($strIp);
        $long = ip2long($strIp);
        if($long == -1 || $long === false)
        {
            $strIp = $strDefaultIp;
            Dapper_Log::warning("clientIP:$strIp", 'libs');
        }

        return $strIp;
    }

    /**
     * 获取字符串签名,用于计数器key计算
     *
     * @param string $str
     * @return int64
     */
    public function getKey($str)
    {
        //$ids = creat_sign_mds64($str);
        $ids = creat_sign_fs64($str);
        return intval($ids[2]);
    }

    /**
     * 从from到to中获得n个不重复的随机数
     * @author liyan01@baidu.com
     * 
     * @param int $from
     * @param int $to
     * @param int $n
     * @return array
     */
    public function randEx($from, $to, $n)
    {
        $ret = array();
        $cup = array();
        for ( $i = 0; $i < $n; $i++ )
        {
            if ( $from > $to )
            {
                break;
            }
            $rand = rand($from, $to);
            !isset($cup[$rand]) &&
                $cup[$rand] = $rand;

            $ret[] = $cup[$rand];
            isset($cup[$from])
                && ($cup[$rand] = $cup[$from])
                || ($cup[$rand] = $from);
            $from++;
        }

        return $ret;
    }

    /**
     * 将dbQuery返回的数组索引转换成key对应的索引
     * @author liyan01@baidu.com
     * 
     * @example 
     * 	原始数据: 
     * 		array(
     * 			[0] => array(	'keyname' => 'key1', ...	),
     * 			[1] => array(	'keyname' => 'key2', ...	),
     * 			[2] => array(	'keyname' => 'key3', ...	),
     * 			[3] => array(	'keyname' => 'key4', ...	),
     * 		)
     * 	返回值：
     * 		array(
     * 			[key1] => array(	'keyname' => 'key1', ...	),
     * 			[key2] => array(	'keyname' => 'key2', ...	),
     * 			[key3] => array(	'keyname' => 'key3', ...	),
     * 			[key4] => array(	'keyname' => 'key4', ...	),
     * 		)
     * 
     * @param array $arrayRs
     * @param string $keyName
     * @return array
     */
    public function rs2keyvalue($arrayRs, $keyName)
    {
        $ret = array();
        foreach ((array)$arrayRs as $row)
        {
            $ret[$row[$keyName]] = $row;
        }

        return $ret;
    }

    /*
     * 获取图片地址
     * @param string $pic_id 图片代号
     * @param string $style 尺寸(big/small)
     * @return string
     * */
    public function getImageUrl($picId,$style='big')
    {
        if(empty($picId))
        {
            return '';
        }
        $picId = strip_tags($picId);

        $confObj = LibFactory::getInstance('LibConfig');
        $imageConf = $confObj->getConfig('image');

        $sites = $imageConf['website'][RUNTIME];
        $prefix = $imageConf['sub_prefix'][RUNTIME];
        $urlPre = isset($sites[$style]) ? $sites[$style] : $sites['big'];
        if(RUNTIME != 'test')
        {
            $mod = ord($picId[0]) % 3;
            $subPre = isset($prefix[$mod]) ? $prefix[$mod].'.' : 'a.';
            return 'http://'. $subPre . $urlPre . $picId . '.jpg';
        }
        else
        {
            return 'http://'. $urlPre . $picId . '.jpg';
        }
    }

    /*
     * 获取图片地址
     * @param string $picId 图片key
     * @param string $type 图片类别: website普通图片，avatar头像图片
     * @return array|false
     * */
    public function getImageAllUrl($picId, $type = 'website')
    {
        if(empty($picId))
        {
            return false;
        }
        $picId = strip_tags($picId);

        $confObj = LibFactory::getInstance('LibConfig');
        $imageConf = $confObj->getConfig('image');

        if($type == 'website'){
            $sites = $imageConf['website'][RUNTIME];
        }
        elseif($type == 'avatar'){
            $sites = $imageConf['avatar'][RUNTIME];
        }
        else{
            return false;
        }

        if(is_array($sites)){
            if(RUNTIME != 'test'){
                $prefix = $imageConf['sub_prefix'][RUNTIME];
                $mod = ord($picId[0]) % 3;
                $subPre = isset($prefix[$mod]) ? $prefix[$mod].'.' : 'a.';
                $subPre = 'http://' . $subPre;
            }
            else{
                $subPre = 'http://';
            }
            $arrUrl = array();
            foreach($sites as $k=>$v){
                $arrUrl[$k] = $subPre . $v . $picId . '.jpg';
            }

            return $arrUrl;
        }
        else{
            return false;
        }
    }

    /**
     * MIS解码函数
     */
    public function base64_unserialize($str)
    {
        $ary = unserialize($str);
        if (is_array($ary)){
            foreach ($ary as $k => $v){
                $tmp = @unserialize($v);
                if (!empty($tmp) && is_array($tmp)){
                    $ritorno[$k]=$this->base64_unserialize($v);
                }else{
                    $ritorno[$k]=base64_decode($v);
                }
            }
        }else{
            return false;
        }
        return $ritorno;
    }

    /**
     * 获取当前完整的url路径
     *
     * @return unknown
     */
    public function getMyUrl()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * 可逆的加密和解密算法（from discuz）
     * @author liyan01@baidu.com
     * 
     * @param string $string
     * @param string $operation	'ENCODE' or 'DECODE'
     * @param string $key
     * @return string
     */
    public function authcode($string, $operation, $key = '') 
    {
        $key = md5($key ? $key : md5($_SERVER['HTTP_USER_AGENT']));
        $key_length = strlen($key);

        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
        $string_length = strlen($string);

        $rndkey = $box = array();
        $result = '';

        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($operation == 'DECODE') {
            if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }

    }

    /**
     * 校验预览传递的参数
     *
     */
    public function checkPreToken($time,$token)
    {
        $now=time();
        if(($now-$time)>1200){
            return false;
        }

        $confObj = LibFactory::getInstance('LibConfig');
        $misConf = $confObj->getConfig('mis');

        $key = $misConf['api']['key'];
        $result=md5($time.$key);

        if($result != $token){
            return false;
        }else {
            return true;
        }
    }

    /**
     * gbk版本strtolower
     * 解决有些环境下，中文使用strtolower转化后数据有问题.
     * add by zhaoshunyao,2011-10-18
     */
    public function strtolowerGBK($str)
    {
        $out = '';
        $strLen = strlen($str); 
        for($i=0; $i<$strLen; $i++)
        {
            $strSub = substr($str, $i, 1);
            if(preg_match("/[\x80-\xff]/", $strSub))
            {
                $out .= $strSub;
            }
            else
            {
                $out .= strtolower($strSub);
            }
        }
        return $out;
    }

    /**
     * 过滤图片上传后回传key
     * add by zhaoshunyao,2011-11-08
     */
    public function safeFilterKey($strInput)
    {
        if(empty($strInput))
        {
            return '';
        }

        $_strOutput = '';

        //白名单过滤
        $_arrSafeString = array(
            'a'=>1,'b'=>1,'c'=>1,'d'=>1,'e'=>1,'f'=>1,'g'=>1,'h'=>1,'i'=>1,'j'=>1,'k'=>1,'l'=>1,'m'=>1,
            'n'=>1,'o'=>1,'p'=>1,'q'=>1,'r'=>1,'s'=>1,'t'=>1,'u'=>1,'v'=>1,'w'=>1,'x'=>1,'y'=>1,'z'=>1,
            '0'=>1,'1'=>1,'2'=>1,'3'=>1,'4'=>1,'5'=>1,'6'=>1,'7'=>1,'8'=>1,'9'=>1,
        );

        $_intStrlen = strlen($strInput);
        for($i = 0; $i < $_intStrlen; $i++)
        {
            if(isset($_arrSafeString[$strInput{$i}]))
            {
                $_strOutput .= $strInput{$i};
            }
        }
        return $_strOutput;
    }

    /**
     * 严格过滤url等用户输入的字符串
     * @param string $strInput
     * @return string $_strOutput
     * 
     */
    public function safeFilterStr($strInput)
    {
        if(empty($strInput))
        {
            return '';
        }

        $_strInput = strtolower($strInput);
        $_strOutput = '';

        //白名单过滤
        $_arrSafeString = array(
            'a'=>1,'b'=>1,'c'=>1,'d'=>1,'e'=>1,'f'=>1,'g'=>1,'h'=>1,'i'=>1,'j'=>1,'k'=>1,'l'=>1,'m'=>1,
            'n'=>1,'o'=>1,'p'=>1,'q'=>1,'r'=>1,'s'=>1,'t'=>1,'u'=>1,'v'=>1,'w'=>1,'x'=>1,'y'=>1,'z'=>1,
            '0'=>1,'1'=>1,'2'=>1,'3'=>1,'4'=>1,'5'=>1,'6'=>1,'7'=>1,'8'=>1,'9'=>1,'/'=>1,'-'=>1,'_'=>1
        );

        $_intStrlen = strlen($_strInput);
        for($i = 0; $i < $_intStrlen; $i++)
        {
            if(isset($_arrSafeString[$_strInput{$i}]))
            {
                $_strOutput .= $_strInput{$i};
            }
        }
        return $_strOutput;
    }

    /**
     * 提交检查，防csrf漏洞
     * add by zhaoshunyao,2011-11-09
     */
    public function checkReferer()
    {
        if(RUNTIME == 'test')
        {
            return true;
        }

        if(!isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'music.baidu.com') === false)
        {
            return false;
        }
        return true;
    }

    /**
     * 取得path_info
     * @return string
     * 
     */
    public function getPathInfo()
    {
        $_strUrl = isset($_SERVER['PATH_INFO']) ? strip_tags($_SERVER['PATH_INFO']) : '/';
        $_intTmpPos = strpos($_strUrl, '.');
        if($_intTmpPos)
        {
            $_strUrl = substr($_strUrl, 0, $_intTmpPos);
        }
        $_intTmpPos = strpos($_strUrl, '?');
        if ($_intTmpPos)
        {
            $_strUrl = substr($_strUrl, 0, $_intTmpPos);
        }
        $_intTmpPos = strrpos($_strUrl, 'index');
        if($_intTmpPos)
        {
            $_strUrl = substr($_strUrl, 0, $_intTmpPos - 1);
        }

        return $_strUrl;
    }

    /**
     * 过滤国外ip
     * @return int error
     * 
     */
    public function ipFilter()
    {
        $myIP = $this->clientIP('0.0.0.0');
        if ($myIP == '0.0.0.0')
        {
            //放行
            return SUCCESS;
        }
        $ipInfo = ip_location($myIP);
        if (false === $ipInfo)
        {
            Dapper_Log::warning('IpLocationFail:'.$myIP, 'libs');
            return SUCCESS;
        }

        $confObj = LibFactory::getInstance('LibConfig');
        $radioConf = $confObj->getConfig('radio');
        foreach ( (array)$radioConf['ip_whitelist'] as $allowedCountry )
        {
            if ( strtoupper($ipInfo['country']) === strtoupper($allowedCountry) )
            {
                return SUCCESS;
            }
        }

        return IP_FORBIDDEN;
    }

    /** 
     * @brief 签名
     * 
     * @param $arr
     * 
     * @return md5 8
     */
    public function genSig($arr){

        ksort($arr);
        reset($arr);
        $str = '';
        foreach($arr AS $k=>$v){
            $str .= $k.'='.$v;
        }
        $this->_params = $arr;
        $str = substr(md5($str.'12!@#$%^&7654@##$'), 0, 8);
        return $str;
    }

    /** 
     * @brief 合并数据
     * 
     * @param $baseData 基本信息
     * @param $appendData 要附件的信息 |一般是歌曲，专辑，音乐人基本信息
     * @param $userKey 使用的key
     * @param $isFilter true or false 开启将过滤掉基本信息不全的数据
     * 
     * @return 
     */
    public function mergeDataWithKey($baseData, $appendData, $userKey, $isFilter=true)
    {

        $result = array();

        if (is_array($baseData) && is_array($appendData) && !is_null($userKey)) {

            foreach ($baseData as $one) {
                $findAppendData = false;
                if (isset($appendData[$one[$userKey]]) && is_array($appendData[$one[$userKey]])) {
                    $findAppendData = true;
                    $one = array_merge($one, $appendData[$one[$userKey]]);        
                }

                //验证附加信息，如果附件信息不存在，则跳过这条信息。 附加信息多为歌曲，专辑，音乐人基础信息
                //?歌曲，专辑 ，音乐信息不存在意味着已下线
                //
                //如果开启过滤，对于附加信息不完整的信息将被放弃掉
                if ($isFilter && false === $findAppendData) {
                    continue;
                }

                $result[] = $one;
            }
        }

        return $result;

    }



    /**
     * 请求数据中心
     * @param array $data
     * @return 
     */
    static function muRequest($data)
    {
        $data['f'] = 'ting';
        $confObj = LibFactory::getInstance('LibConfig');
        $serverInfo = $confObj->getConfig('mucenter');
        $serverInfo = $serverInfo['server'][RUNTIME];

        //shuffle($serverInfo);
        $serverInfo = $serverInfo[0];

        $httpObj = DBFactory::getInstance('DBHttpProxy');

        $result = $httpObj->request($serverInfo, $data, 'POST');
        if($result===false)  Dapper_Log::fatal("Ucenter_Http_Error", 'dal',$data);
        return $result;
    }


    /**
     * 获取地址及端口 传入的host用于排除刚刚链接失败的
     * @author zhangguoxian
     * $name配置类型，即global中的数组索引
     * $host前次失败的host，如只有一个，即使失败了也是他
     */
    public function get_host($name,$host='') {
        $confObj = LibFactory::getInstance('LibConfig');
        $musicIndexConf = $confObj->getConfig($name);
        $hosts=$musicIndexConf[RUNTIME];

        $seed = rand ();

        if($host){ 
            $k=array_search($host['host'], $hosts);
            unset($hosts[$k]);
            $hosts=array_values($hosts);
        }

        $n=count($hosts);
        if(!$n) return $host;
        $k=$seed%$n;
        return $hosts[$k] ? $hosts[$k]:$host;
    }

    public function get_ua() {
        $tingObj = LibFactory::getInstance ( 'LibTing' );
        $host=$tingObj->get_host('adapt_ua');
        $tmp=$_SERVER;
        //var_dump($tmp);
        $ret = $this->ns_socket($tmp,$host);
        //var_dump($ret);
        //exit();
        //$Data['model'] = $ret['model'];
        //$Data['mobile_browser_id'] = $ret['mobile_browser_id'];

        return $ret;
    }

    public function ns_socket($request, $host) {
        $nsHeadObj = LibFactory::getInstance('LibNsHead');
        if (!$host) 
            return false;

        $url=explode(':',$host);
        $fp = @fsockopen($url[0], $url[1], $errno, $errstr, 30);

        if (!$fp){
            Dapper_Log::fatal("ns_socket_fail_{$url[0]}:{$url[1]}", 'analyze',array('error_socket'=>$host.$errno.$errstr));
            return false;
        }

        $query_pack = @mc_pack_array2pack($request);
        $nsheadArr['provider'] = 'music';
        $nsheadArr['body_len'] = strlen($query_pack);
        $re=$nsHeadObj->nshead_write($fp,$nsheadArr,$query_pack,$url[0], $url[1]);
        $retArr=$nsHeadObj->nshead_read($fp,true,$url[0], $url[1]);

        if(!isset($retArr['buf']) || (false === $retArr['buf']))
        {
            return false;
        }

        $ret = @mc_pack_pack2array($retArr['buf']);

        if(!is_array($ret))
        {
            return false;
        }
        //$output=$ret;
        return $ret;
    }

    /** 
     * @brief 对数组指定字段排序
     * 
     * @param $array 待排序的数组
     * @param $key 按那个字段排
     * @param $order 排序方式 ASC|DESC
     * 
     * @return Array 
     */
    public function orderArrayByColumn($array, $key, $order = "ASC")
    {
        $tmp = array();
        foreach((array)$array as $akey => $array2)
        {
            $tmp[$akey] = $array2[$key];
        }

        if($order == "DESC") {
            arsort($tmp , SORT_NUMERIC );
        } elseif ($order == 'DATE') {
            arsort($tmp , SORT_STRING );
        } elseif ($order == 'SASC') {
            asort($tmp , SORT_STRING );
        } elseif ($order == 'SDESC') {
            asort($tmp , SORT_STRING );
        } else {
            asort($tmp , SORT_NUMERIC );
        }

        $tmp2 = array();       
        foreach((array)$tmp as $key => $value)
        {
            $tmp2[$key] = $array[$key];
        }       

        return $tmp2;
    } 


    /** 
     * @brief 
     * 
     * @param $key
     * 
     * @return 
     */
    private function getCacheKey($key)
    {
        return BAIDU_MUSIC_CACHE_PREFIX.$key; 
    }

    /** 
     * @brief 根据KEY获取缓存数据
     * 
     * @param $key
     * 
     * @return 
     */
    public function getCache($key)
    {
        $pre = Dapper_Http_Request::getParam('pre', 0);
        $memObj = LibFactory::getInstance('LibZcache');

        $cacheKey = $this->getCacheKey($key);

        if (true === ENABLE_BAIDU_MUSIC_CACHE && $pre == '0') {//cache弃用
            return $memObj->get($cacheKey);
        } else {
            return false;
        }
    }

    /** 
     * @brief 设置CACHE
     * 
     * @param $key cache_key
     * @param $data 数据
     * 
     * @return 
     */
    public function setCache($key, $data=null, $cacheTime=0)
    {

        $pre = Dapper_Http_Request::getParam('pre', 0);
        $memObj = LibFactory::getInstance('LibZcache');

        $cacheKey = $this->getCacheKey($key);

        if (true == ENABLE_BAIDU_MUSIC_CACHE && $pre == '0') {

            return $memObj->set($cacheKey, $data, $cacheTime);
        } else {
            return false;
        }

    }
    //获取秒数和微秒数
    function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }


    function getTiebaArtistNameMap($artist_id)
    {
        include(ROOT_PATH . 'conf/third/tieba_artistmap.conf.php');     
        if (isset($tiebaArtistMap[$artist_id])) {
            return $tiebaArtistMap[$artist_id];
        } else {
            return null;
        }

    }

    /**
     * @brief 获取二维数组中子元素的某个值
     *
     * @author v_sunhuai@baidu.com
     * @param $array 二维数组
     * @param $key 子元素的键值
     *
     * @returns array 
     */
    public static function get2DsVal($array, $key){
        $result = array();
        if (!is_array($array)) {
            return $result;
        }
        foreach ($array as $item) {
            if (!is_array($item) || !array_key_exists($key, $item)) {
                return $result;
            }
            $result[] = $item[$key];
        }
        return $result;
    }

    /**
     * @brief 
     *
     * @param $array1 mis的数据
     * @param $array2
     * @param $key key(primary or unique)是否相等 判断merge的条件
     *
     * @returns 
     */
    public static function mis2DsDataMerge($array1, $array2, $key){
        if (!is_array($array1) || !is_array($array2)) {
            return $array1;
        }
        foreach ($array1 as $k1 => $item1) {
            if (!is_array($item1) || !isset($item1[$key])) {
                return array();
            }
            foreach ($array2 as $k2 => $item2) {
                if (!is_array($item2) || !isset($item2[$key])) {
                    return array();
                }
                if ($item1[$key] == $item2[$key]) {
                    $array1[$k1] = array_merge($item1, $item2);
                    unset($array2[$k2]);
                    break;
                }
            }
            $result[] = $item[$key];
        }
        return $array1;
    }

    function getMetaDataFromMis($meta_key)
    {
        $this->db = Dapper_Model_DB::getInstance('ns_ting');
        $q = sprintf("SELECT meta_value FROM ting_mis_meta WHERE meta_key='%s'", $meta_key); 
        return $this->db->queryFirstRow($q);
    }

    /**
     * @brief 清除掉mis中的无用数据,例如mis_editor_name等
     *
     * @param $array 二维数组
     *
     * @returns 
     */
    public static function clearMisData($array) {
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $k => $v) {
            unset($array[$k]['mis_editor_name'], $array[$k]['mis_edit_time']);
        }
        return $array;
    }
    function getHuodongData()
    {
        $memObj = LibFactory::getInstance('LibZcache');
        //$a = $memObj->delete(HUODONG2013); var_dump($a);
        $ret = $memObj->get(HUODONG2013);

        if($ret == false)
        {
            $this->db = Dapper_Model_DB::getInstance('ns_ting');
            $sql = 'SELECT `id`, `user_bdname`, `setup_num` FROM `music_hd_appsetup` ORDER BY `id` DESC LIMIT 100';
            $res = $this->db->queryAllRows($sql);
            if(empty($res) || !is_array($res))
                return false;

            foreach ($res as $key => $value) 
            {
                $uname = mb_substr($value['user_bdname'], 0, 4, 'UTF-8');
                $uname .= '***';
                $tmp[] = array('id' => $value['id'], 'uname' => $uname, 'nums' => $value['setup_num']);
            }
            $ret = serialize($tmp);

            //5分钟过期
            $memObj->set(HUODONG2013, $ret, 300);
        }

        return unserialize($ret);

    }

    /**
     * @brief 请求生成短网址 http://www.baidu.com/search/dwz.html#05
     *
     * @param $url 需要的url
     *
     * @returns array $arrResponse 接口返回的response 包含status,err_msg,tinyurl信息
     */
    public static function requestDwz($url) {
        if (!$url) {
            return '';
        }
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $data=array('url'=> $url);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $strRes=curl_exec($ch);
        curl_close($ch);
        $arrResponse=json_decode($strRes, TRUE);

        return $arrResponse;
    }

    function echo_json($arr) {
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')){
            header ( "Cache-Control:max-age=0" ); // HTTP/1.1
            header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // 过去的时间
            header ( "Connection: keep-alive" );
            header ( "Content-Type: application/javascript" );
            header ( "Pragma: public" );
        }else{
            header ( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
            header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // 过去的时间
            header ( "Content-Type: application/javascript" );
        }   
        echo json_encode ($arr);
        return ;
    }


}//end class
