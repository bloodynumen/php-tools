<?php
/**
* @file TingSystemLogFactory.class.php
* @brief 通用日志的工厂类
* @author sunhuai(v_sunhuai@baidu.com)
* @version 
* @date 2012-12-28 11:21:30
 */


class TingSystemLogFactory {
    /**
     * Array of libs instances
     * @var array
     */
    public static $_instance = array();

    /**
        * @brief 获取单例
        *
        * @param $fileDir 日志文件的目录(无/) 总目录为/data/ting/systemLog
        *
        * @returns 
     */
    public static function getInstance($fileDir) {
        if(!array_key_exists(self::$_instance, $fileDir)) {
            self::$_instance[$fileDir] = new TingSystemLog($fileDir);
        }
        return self::$_instance[$fileDir];
    }
}
