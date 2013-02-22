<?php
class TingExtUtil
{

    /** 
     * @brief 用于标记特殊数据
     */
    const RANK_THRESHOLD = 3000;
    /**
     * @brief   解析 uri 用于分页显示
     *
     * @access   public
     * @params   string $uri    待解析的 uri
     * @return   array  解析后的结果.
     */
    public static function paseUri($uri)
    {
        $res = array();

        if ($uri)
        {
            $para = explode('/', $uri);
            if (is_array($para))
            {
                $count = count($para);
                for ($i = 0; $i < $count; $i++)
                {
                    if ('pn' == strtolower($para[$i]))
                    {
                        $i++;
                        $res['pn'] = isset($para[$i]) ? $para[$i] + 0 : 0;
                    }
                    if ('rn' == strtolower($para[$i]))
                    {
                        $i++;
                        $res['rn'] = isset($para[$i]) ? $para[$i] + 0 : 0;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @brief   取得当前操作者的名称
     *
     * @access  public
     * @params  void
     * @return  string  编辑的名称
     */
    static public function getEditorName()
    {
        //global     $sess;
        //$user = $sess->get('phpCAS','user');
        $editor_name = 'yule';
        if (isset($_SESSION['phpCAS']) && isset($_SESSION['phpCAS']['user']))
        {
            $editor_name = $_SESSION['phpCAS']['user'];
        }

        return $editor_name;
    }

    /**
     * @brief   发布包
     *
     * @access
     * @params
     * @return
     */
    static public function delivePackage($module, $file_name = '')
    {
        $res = array(
            'result' => 0,
            'msg'    => 'Delive package has fail.',
        );

        if ($module && $file_name && $action && isset(TingExtCommonConfig::$deliverConfig[$module]))
        {
            $service = new MisDeliverService();
		    $action = 'update';
            $config = TingExtCommonConfig::$deliverConfig[$module]['online'];
            
            $file_path = $config['source_path'] . '/' . $file_name;
            $root_path = $config['dest_path'];
            $machines  = $config['machines'];
            $ch_spell='yule';

            $backfeed = $service->callTaskRemote($action, $file_path, $root_path, $machines, $ch_spell);

            if (1 == $backfeed['result'])
            {
                $res = array(
                    'result' => 0,
                    'msg'    => 'Delive package is success.',
                );
            }
        }

        return $res;
    }

    /**
     * @brief   预览
     *
     * @access
     * @params
     * @return
     */
    static public function previewPage()
    {
    }
    static public function getBillboardQueryNo($config = array(), $db = NULL, $history = '')
    {
        $res = '';
        if (count($config) && $db)
        {
            $table  = $config['table'];
            $module = $config['module_name'];
            $billboard_type = $config['billboard_type'];

            $billboard_no = 1;
            $max_sql = "SELECT MAX(billboard_no) AS billboard_no FROM {$table} WHERE billboard_type={$billboard_type}";
            //echo $max_sql;
            $max_row = $db->query($max_sql);
            if (count($max_row))
            {
                if ($max_row[0]['billboard_no'])
                {
                    $res = $max_row[0]['billboard_no'];
                }

            }
        }
        return $res;
    }

    static public function getBillboardQueryDate($config = array(), $db = NULL, $history = '')
    {
        $res = '';
        if (count($config) && $db)
        {
            $table  = $config['table'];
            $module = $config['module_name'];
            $billboard_type = $config['billboard_type'];

            $billboard_no = 1;
            $max_sql = "SELECT MAX(billboard_no) AS billboard_no, update_date FROM {$table} WHERE billboard_type={$billboard_type} GROUP BY billboard_type";
            //echo $max_sql;
            $max_row = $db->query($max_sql);
            if (count($max_row))
            {
                if ($max_row[0]['update_date'])
                {
                    $res = $max_row[0]['update_date'];
                }

            }
        }

        $time_now = time();
        $limit_hour = 12;

        if ($res && ! $history)
        {
            return $res;
        }

        if ($history)
        {
            $res = $history;
        }

        return $res;
    }

    static public function BillboardTingQuery($config = array(), $db = NULL)
    {
        $res = array();

        if (! count($config) || NULL == $db)
        {
            //print_r(func_get_args());
            return $res;
        }

        $table = $config['table'];
        $billboard_type = $config['billboard_type'];

        //判断显示数量  2012-09-19 10:32:21 add 
        if (!isset($config['showCount'])) {
            $show_count = 100;
        } else {
            $show_count = $config['showCount'];
        }//end if

        $history = isset($_GET['hist']) ? $_GET['hist'] : '';

        if ($history) {
            $update_date = TingExtUtil::getBillboardQueryDate($config, $db, $history);
            $conds  = "billboard_type='{$billboard_type}' AND update_date='{$update_date}'";
        } else {
            $update_no = TingExtUtil::getBillboardQueryNo($config, $db, $history);
            $conds  = "billboard_type='{$billboard_type}' AND
                billboard_no='{$update_no}'";

        }
        //print_r($config);
        $fields = '*';
        //$conds  = "billboard_type='{$billboard_type}' AND billboard_no=3";
        $appends = 'ORDER BY rank ASC, auto_id ASC LIMIT 2000';
        //$conds .= " AND `rank`<3000";

        $res = $db->select($table, $fields, $conds, $options = NULL, $appends);

        $songIdArr = array();
        foreach ($res as $resKey=>$resVal) {
            $songIdArr[] = $resVal['song_id'];
        }
        $songIdStr = implode(",", $songIdArr);

        //get song information
        if ($songIdStr) {
            $songInfo = $db->select('ting_song_info', 'song_id,title,author',"song_id in ($songIdStr)");

            $newSongInfo = array();
            foreach ($songInfo as $songInfoKey=> $songInfoVal) {
                $newSongInfo[$songInfoVal['song_id']]['artist_name'] = $songInfoVal['author'];
                $newSongInfo[$songInfoVal['song_id']]['song_title'] = $songInfoVal['title'];
            }

            foreach ($res as $resKey=> $resVal) {
                $resVal['rank'] > self::RANK_THRESHOLD ? $dataType ='del' : $dataType='all';
                if ($dataType == 'del') {
                    $resVal['rank'] = $resVal['rank'] -self::RANK_THRESHOLD;
                }
                if (array_key_exists($resVal['song_id'], $newSongInfo)) {
                    $res[$dataType][$resKey] = $newSongInfo[$resVal['song_id']] + $resVal;
                } else {
                    $res[$dataType][$resKey] = $resVal;//
                }
                unset ($res[$resKey]);
            }
        }

        $res = array(
            'all'=> array_slice($res['all'],0,$show_count),         
            'down'=> array_slice($res['del'],0),         
        );
        return $res;
    }

    static public function BillboardChangeWeight($config = array(), $db = NULL, $option = '')
    {
        $res = array(
            'result' => 0,
            'msg'    => 'Operate fail.',
        );
        //$res = false;


        if (count($config) && $db && $option)
        {

            //判断显示数量  2012-09-19 10:32:21 add 
            if (!isset($config['showCount'])) {
                $show_count = 100;
            } else {
                $show_count = $config['showCount'];
            }//end if

            $auto_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $get_song_id = isset($_GET['song_id']) ? $_GET['song_id'] : 0;
            //$auto_id += 0;
            $getAid = isset($_GET['aid']) ? $_GET['aid'] : 0;
            $getRank = isset($_GET['rank']) ? $_GET['rank'] : 0;

            $table = $config['table'];

            //fix bug 2011-12-14 16:04:26 
            $moveInfo = $db->query("SELECT * FROM `{$table}` WHERE `auto_id`={$auto_id}"); 
            $getRank = $moveInfo[0]['rank'];
            //end 2011-12-14 16:04:31 

            $sqlGroup = array();
            if ($option == 'up' && $getRank >1) {
                $sqlGroup['click'] = "UPDATE {$table} SET rank = rank - 1 WHERE auto_id={$auto_id}";
                $sqlGroup['click2'] = "UPDATE {$table} SET `rank_change` = `rank_change` + 1 WHERE `auto_id`={$auto_id} and `is_new`=0";
                $sqlGroup['above'] = "UPDATE {$table} SET rank = rank + 1 WHERE auto_id={$getAid}";
                $sqlGroup['above2'] = "UPDATE {$table} SET rank_change = rank_change - 1 WHERE auto_id={$getAid} and `is_new`=0";
            }

            if ($option == 'down') {
                if ($getRank == $show_count) {
                    //do nothing
                } else {
                    $sqlGroup['click'] = "UPDATE {$table} SET `rank` = `rank` + 1 WHERE auto_id={$auto_id}";
                    $sqlGroup['click2'] = "UPDATE {$table} SET `rank_change` =`rank_change` - 1 WHERE auto_id={$auto_id} and `is_new`=0";
                    $sqlGroup['down'] = "UPDATE {$table} SET `rank` =`rank` - 1 WHERE auto_id={$getAid}";
                    $sqlGroup['down2'] = "UPDATE {$table} SET `rank_change` =`rank_change` + 1 WHERE auto_id={$getAid} and `is_new`=0";
                }
            }

            $db_error = 0;
            $db->startTransaction();

            foreach ($sqlGroup as $_k => $sql)
            {           
//                echo $sql."<br/>";
                $db->query($sql);
                if (! $db_error && 0 != $db->errno())
                {
                    $db_error = 1;
                }
            }

            if ($db_error)
            {
                $db->rollback();
            }
            else
            {
                $db->commit();
                
                $res = array(
                    'result' => 1,
                    'msg'    => 'Operate success.',
                );
                //send mail
                TingNotificationManage::sendMailForBillBoardSt($config['module_name'],$option,$get_song_id);
            }
        }
        return $res;
    }

    static public function BillboardCreateList($config = array(), $db = NULL, $sid = '')
    {
        //print_r($config);
        $res = array(
            'result' => 0,
            'msg'    => 'Operate fail.',
        );

        if (count($config) && $db && $sid)
        {
            $table  = $config['table'];
            $module = $config['module_name'];
            $billboard_type = $config['billboard_type'];

            $time = time();
            $term_str = date('Y-m-d', $time);

            $billboard_no = 1;
            $max_sql = "SELECT MAX(billboard_no) AS billboard_no, update_date FROM {$table} WHERE billboard_type={$billboard_type} GROUP BY billboard_type";
            //echo $max_sql;
            $max_row = $db->query($max_sql);
            if (count($max_row))
            {
                if ($max_row[0]['update_date'] == $term_str)
                {
                    // 追加 append
                    $billboard_no = $max_row[0]['billboard_no'];
                }
                else
                {
                    // 新增 add new
                    $billboard_no = $max_row[0]['billboard_no'] + 1;
                }
            }

            $sid = explode(',', $sid);
            $sql_array = array();

            foreach ($sid as $_k => $song_id)
            {  
                $song_data = array(
                    'singer' => '',
                    'song'   => '',
                );
                $sql = "SELECT author AS singer, title AS song FROM ting_song_info WHERE song_id={$song_id}";
                $row = $db->query($sql);
                if (count($row))
                {
                    $song_data = $row[0];
                    //print_r($song_data);
                    $sql_array[] = "INSERT INTO {$table} SET song_id={$song_id}, artist_name='{$song_data['singer']}', song_title='{$song_data['song']}', is_new=0, rank_change=0, rank=0, billboard_type={$billboard_type}, billboard_no={$billboard_no}, update_date='{$term_str}', song_count=0";
                }
            }
            //print_r($sql_array);

            $db_error = 0;
            $db->startTransaction();

            foreach ($sql_array as $_k => $sql)
            {           
                $db->query($sql);
                if (! $db_error && 0 != $db->errno())
                {
                    $db_error = 1;
                }
            }

            if ($db_error)
            {
                $db->rollback();
            }
            else
            {
                $db->commit();
                
                $res = array(
                    'result' => 1,
                    'msg'    => 'Operate success.',
                );
            }
        }

        return $res;
    }


    /** 
     * @brief BillboardDelete 删除选中的数据
     * 
     * @param $config
     * @param $db
     * 
     * @return 
     */
    static public function BillboardDelete($config = array(), $db = NULL)
    {
        $res = array( 'result'=> 0, 'msg'=> 'delete fail.' );
        if (count($config) && $db)
        {

            //判断显示数量  2012-09-19 10:32:21 add 
            if (!isset($config['showCount'])) {
                $show_count = 100;
            } else {
                $show_count = $config['showCount'];
            }//end if


            $get_song_id = isset($_GET['song_id']) ? $_GET['song_id'] : 0;
            $auto_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $table = $config['table'];
            $getRankChange = 0;
            $getRank =0;
            $getBillboardNo=0;

            $sqlGroup = array();
            $deleteInfo = $db->query("SELECT * FROM `{$table}` WHERE `auto_id`={$auto_id}"); 

            if (!empty($deleteInfo) && $deleteInfo[0]['rank'] < self::RANK_THRESHOLD) {
                //获取删除的那条信息，以便确定基点
                $getRank = $deleteInfo[0]['rank'];
                $getBillboardNo = $deleteInfo[0]['billboard_no'];
                $getBillboardType = $deleteInfo[0]['billboard_type'];
                //这里是存放需要变动的数据,过滤条件billboard_type+billboard_no
                $needChangeGroup = $db->query("SELECT * FROM `{$table}` WHERE
                        `billboard_no`={$getBillboardNo} and
                        `billboard_type`={$getBillboardType} and `rank` > {$getRank} and `rank`< 3000 ORDER BY rank asc"); 
            }

            if (!empty($needChangeGroup)) {
                //is_new=1新上榜
                foreach ($needChangeGroup as $changeKey=> $changeVal) {
                    if ($changeVal['rank']> self::RANK_THRESHOLD) {
                        continue;
                    }
                    //对rank=101的数据进行特殊处理
                    if ($changeVal['rank'] == $show_count +1) {
                        if ($changeVal['rank_change'] >= 0) {
                            //上期在100名外
                            $sqlGroup[$changeKey] = "UPDATE {$table} SET `is_new`=1, `rank`=$show_count,`rank_change`=0 WHERE `auto_id`={$changeVal['auto_id']}";
                        } else {
                            //上期在100名内
                            $sqlGroup[$changeKey] = "UPDATE {$table} SET `is_new`=0, `rank`=$show_count,`rank_change`=`rank_change`+1 WHERE `auto_id`={$changeVal['auto_id']}";
                        }
                    } else {
                        if ($changeVal['is_new'] == '1') {
                            $sqlGroup[$changeKey] = "UPDATE {$table} SET `rank`=`rank`-1,`rank_change`=0 WHERE `auto_id`={$changeVal['auto_id']}";
                        } else {
                            $sqlGroup[$changeKey] = "UPDATE {$table} SET `rank`=`rank`-1,`rank_change`=`rank_change`+1 WHERE `auto_id`={$changeVal['auto_id']}";
                        }
                    }
                }//end foreach
                //delete seleced item
                $rankPreNum = self::RANK_THRESHOLD;
                $sqlGroup[] = "UPDATE {$table} SET `rank`=`rank`+ {$rankPreNum} WHERE `auto_id`={$auto_id} and `rank` < 3000";

            }//end if 
            
            $db_error = 0;
            $db->startTransaction();

            foreach ($sqlGroup as $_k => $sql)
            {           
                //echo $sql."<br/>";
                $db->query($sql);
                if (! $db_error && 0 != $db->errno())
                {
                    $db_error = 1;
                }
            }

            if ($db_error)
            {
                $db->rollback();
            }
            else
            {
                $db->commit();
                $res = array(
                    'result' => 1,
                    'msg'    => 'Operate success.',
                );
                TingNotificationManage::sendMailForBillBoardSt($config['module_name'],'del',$deleteInfo[0]['song_id']);
            }
        }
        return $res;
    }

    /** 
     * @brief BillboardRecover 恢复删除的数据
     * 
     * @param $config
     * @param $db
     * 
     * @return 
     */
    static public function BillboardRecover($config = array(), $db = NULL)
    {
        $res = array( 'result'=> 0, 'msg'=> 'recover fail.' );
        if (count($config) && $db)
        {
            $auto_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $table = $config['table'];
            $getRankChange = 0;
            $getRank =0;
            $getBillboardNo=0;

            $sqlGroup = array();
            $recoverInfo = $db->query("SELECT * FROM `{$table}` WHERE `auto_id`={$auto_id}"); 

            if (!empty($recoverInfo) && $recoverInfo[0]['rank']> self::RANK_THRESHOLD) {
                //获取恢复的那条信息，以便确定基点
                $getRank = $recoverInfo[0]['rank'] - self::RANK_THRESHOLD;
                $getBillboardNo = $recoverInfo[0]['billboard_no'];
                $getBillboardType = $recoverInfo[0]['billboard_type'];
                //这里是存放需要变动的数据,过滤条件billboard_type+billboard_no
                //将被恢复数据之后的数据下移一位
                $needChangeGroup = $db->query("SELECT * FROM `{$table}` WHERE
                        `billboard_no`={$getBillboardNo} and
                        `billboard_type`={$getBillboardType} and `rank` >= {$getRank} and `rank`< 3000 ORDER BY rank asc"); 
            }
            if (!empty($needChangeGroup)) {
                //is_new=1新上榜
                foreach ($needChangeGroup as $changeKey=> $changeVal) {
                    if ($changeVal['is_new'] == '1') {
                        $sqlGroup[$changeKey] = "UPDATE {$table} SET `rank`=`rank`+1,`rank_change`=0 WHERE `auto_id`={$changeVal['auto_id']}";
                    } else {
                        $sqlGroup[$changeKey] = "UPDATE {$table} SET `rank`=`rank`+1,`rank_change`=`rank_change`-1 WHERE `auto_id`={$changeVal['auto_id']}";
                    }
                }//end foreach
                //revover seleced item
                $sqlGroup[] = "UPDATE {$table} SET `rank`={$getRank} WHERE `auto_id`={$auto_id}";

            }//end if 
            
            $db_error = 0;
            $db->startTransaction();

            foreach ($sqlGroup as $_k => $sql)
            {           
                //echo $sql."<br/>";
                $db->query($sql);
                if (! $db_error && 0 != $db->errno())
                {
                    $db_error = 1;
                }
            }

            if ($db_error)
            {
                $db->rollback();
            }
            else
            {
                $db->commit();
                $res = array(
                    'result' => 1,
                    'msg'    => 'Operate success.',
                );
                TingNotificationManage::sendMailForBillBoardSt($config['module_name'],'recover',$recoverInfo[0]['song_id']);
            }
        }
        return $res;
    }

    /** 
     * @brief BillboardMove 移动到指定位置
     * 
     * @param $config
     * @param $db
     * 
     * @return 
     */
    static public function BillboardMove($config = array(), $db = NULL)
    {
        $res = array( 'result'=> 0, 'msg'=> 'move fail.' );
        if (count($config) && $db)
        {
            //判断显示数量  2012-09-19 10:32:21 add 
            if (!isset($config['showCount'])) {
                $show_count = 100;
            } else {
                $show_count = $config['showCount'];
            }//end if


            //获得移动到的位置
            $move_to = 40;
            if ($move_to > 100) {
                return $res;
            }

            $auto_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $table = $config['table'];
            $getRankChange = 0;
            $getRank =0;
            $getBillboardNo=0;
            $tendency ='up';//调整趋势 up or down

            $sqlGroup = array();
            $moveInfo = $db->query("SELECT * FROM `{$table}` WHERE `auto_id`={$auto_id}"); 

            //获取移动的那条信息，以便确定基点
            if (!empty($moveInfo)) {
                //这里是存放需要变动的数据,过滤条件billboard_type+billboard_no
                $getRank = $moveInfo[0]['rank'];
                $getBillboardNo = $moveInfo[0]['billboard_no'];
                $getBillboardType = $moveInfo[0]['billboard_type'];
                $getMoveChange = $move_to - $moveInfo[0]['rank'];
                $getItemIsNew = $moveInfo[0]['is_new'];
                $getRankChange = $moveInfo[0]['rank_change'];
            }

            //获取需要改动数据
            if (!empty($moveInfo) && $getMoveChange<0) {//上升
                $needChangeGroup = $db->query("SELECT * FROM `{$table}` WHERE `billboard_no`={$getBillboardNo} and `billboard_type`={$getBillboardType} and `rank` >= {$move_to} and `rank` < {$getRank} ORDER BY rank asc"); 
                $tendency = 'up';
            } elseif (!empty($moveInfo) && $getMoveChange>0) {//下降
                $needChangeGroup = $db->query("SELECT * FROM `{$table}` WHERE `billboard_no`={$getBillboardNo} and `billboard_type`={$getBillboardType} and `rank` > {$getRank} and `rank` <= {$move_to} ORDER BY rank asc"); 
                $tendency = 'down';
            } else {
                return $res;
            }

            if (!empty($needChangeGroup)) {
                //is_new=1新上榜
                //四种情况,上升且是新入榜,上升且在榜内,下降且新入榜,下降且在榜内
                foreach ($needChangeGroup as $changeKey=> $changeVal) {
                    if ($tendency == 'up' && $changeVal['is_new'] == '1') {
                        $updateCondition = ' `rank`=`rank`+1`,`rank_change=0 `';
                    } elseif ($tendency == 'up' && $changeVal['is_new'] == '0') {
                        $updateCondition = ' `rank`=`rank`+1`,`rank_change=rank_change-1 `';
                    } elseif ($tendency == 'down' && $changeVal['is_new'] == '1') {
                        $updateCondition = ' `rank`=`rank`-1`,`rank_change=0 `';
                    } elseif ($tendency == 'down' && $changeVal['is_new'] == '0') {
                        $updateCondition = ' `rank`=`rank`+1`,`rank_change=rank_change+1 `';
                    } else {
                        return $res;
                    }
                    $sqlGroup[$changeKey] = "UPDATE {$table} SET {$updateCondition} WHERE `auto_id`={$changeVal['auto_id']}";
                }//end foreach
                //move seleced item

                //当移动100名外的数据 且 变化值大于0的说明上期排名不在100名内,为新上榜
                if ($getRankChange >=0 && $getRank > 100) {
                    $getItemIsNew = 1;//cover
                }

                if ($getItemIsNew == '1') {//是否新如榜
                    $sqlGroup[] = "UPDATE {$table} SET `rank`={$move_to},`rank_change`= 0 WHERE `auto_id`={$auto_id}";

                } else {
                    $sqlGroup[] = "UPDATE {$table} SET `rank`={$move_to},`rank_change`= `rank_change` - {$getMoveChange}  WHERE `auto_id`={$auto_id}";
                }

            }//end if 

            //run sql
            $db_error = 0;
            $db->startTransaction();

            foreach ($sqlGroup as $_k => $sql)
            {           
                //echo $sql."<br/>";
                $db->query($sql);
                if (! $db_error && 0 != $db->errno())
                {
                    $db_error = 1;
                }
            }

            if ($db_error)
            {
                $db->rollback();
            }
            else
            {
                $db->commit();
                $res = array(
                    'result' => 1,
                    'msg'    => 'Operate success.',
                );
                TingNotificationManage::sendMailForBillBoardSt($config['module_name'],'move',$recoverInfo[0]['song_id']);
            }
        }
        return $res;
    }

    /** 
     * @brief 多用于Channel 中的数据需要逆序时候 逆序操作除了添加到配置文件中 还要给数据数组key添加前缀 这样FE的数据就是逆序了（PS：否则即使数据是逆序的，FE端也会按照数字key重新顺序排列） 
     *
     * @author v_sunhuai@baidu.com
     * @param prefix
     * @param array 
     * 
     * @return array
     */
    static public function addKeyPrefix($prefix, $array) {
        if (!is_string($prefix) || !is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $k => $v) {
            $result[$prefix . $k] = $v;
        }
        return $result;
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
    static public function get2DsVal($array, $key){
        $result = array();
        if (!is_array($array)) {
            return $result;
        }
        foreach ($array as $item) {
            if (!is_array($item) || !isset($item[$key])) {
                return $result;
            }
            $result[] = $item[$key];
        }
        return $result;
    }

    /**
        * @brief 获取发布状态 与special list 一致
        *
        * @returns 
     */
    static function getPublishStatus($publishNum) {
        $pubArr=array('ten','Qian','Ios','Android','Web');	
        $publish = decbin($publishNum);
        $pubStr = '';
        $lenDiff = count($pubArr)-strlen($publish);
        if($lenDiff != 0) {
            for($j=0;$j<$lenDiff;$j++) {
                $publish = '0'.$publish;
            }
        }
        for($i=0;$i<strlen($publish);$i++) {
            $pubStr .= $publish[$i]=='1'?' '.$pubArr[$i]:' ';
        }
        return $pubStr;
    }

    /**
        * @brief 创建目录
        *
        * @param $dir
        *
        * @returns 
     */
    static public function createDir($dir) {
        if (is_string($dir)) {
            if (is_dir($dir)) {
                return TRUE;
            }
            if (mkdir($dir)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
?>
