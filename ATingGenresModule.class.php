<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
* @file ATingGenresModule.class.php
* @brief 流派模块管理基类
* @author sunhuai(v_sunhuai@baidu.com)
* @version 
* @date 2013-1-30 13:54:32
 */

class ATingGenresModule extends ATingExt {
    protected $_table = '`ting_mis_genres_admin`';
    protected $_moduleColumn = '`attr`';
    protected $_moduleKey = '';
    protected $allModuleData = array();
    protected $_identifier = '';

    /**
     * @brief 自定义初始化方法
     *
     * @returns 
     */
    public function init() {
        $this->_identifier = $this->_getParam('identifier', '');
        if (!$this->_identifier) {
            $this->_identifier = $this->_postParam('identifier', '');
        }
        if (!$this->_moduleKey) {
            $this->_moduleKey = $this->_postParam('module_key', '');
        }
        if (!$this->_identifier) {
            exit('must post the identifier');
        }
        if (!$this->_moduleKey) {
            exit('must define the moduleKey');
        }
    }

    protected function _getModuleData() {
        $allModuleData = $this->_getAllModulesData();
        $this->_allModuleData = $allModuleData;
        if (isset($allModuleData[$this->_moduleKey])) {
            return $allModuleData[$this->_moduleKey];
        }
        return array();
    }

    protected function _getAllModulesData() {
        $sql = '';
        $sql .= 'SELECT ' . $this->_moduleColumn;
        $sql .= ' FROM ' . $this->_table;
        $sql .= ' WHERE `identifier` = "' . $this->_identifier . '"';
        $rows = $this->q($sql);
        if ($rows) {
            return $this->base64_unserialize($rows[0]['attr']);
        }
        return array();
    }

    protected function _makeQuery($params, $op = 'i') {
        $newData = $this->_pack($params, $op);
        if (is_array($newData)) {
            return $this->_saveModule($newData);
        } 
        return FALSE;
    }

    protected function _pack ($params, $op = 'i') {
        if (!$params) {
            return FALSE;
        }
        $oldData = $this->_getModuleData();
        $newData = array();
        switch ($op) {
            case 'i':
                if (empty($oldData)) {
                    $newData[0] = '';
                    unset($newData[0]);
                } else {
                    $newData = $oldData;
                }
                $newData[] = $params;
                break;
            case 'mi':
                if (empty($oldData)) {
                    $newData[0] = '';
                    unset($newData[0]);
                } else {
                    $newData = $oldData;
                }
                foreach ($params as $param) {
                    $newData[] = $param;
                }
                break;
            case 'd':
                if (is_array($params)) {
                    foreach ($params as $id) {
                        unset($oldData[$id]);
                    }
                } else {
                    unset($oldData[$params]);
                }
                $newData = $oldData;
                break;
            case 'u':
                $id = $params['id'];
                unset($params['id']);
                $newData = $oldData;
                $newData[$id] = array_merge($oldData[$id], $params);
                break;
            default :
                return FALSE;
                break;
        }
        krsort($newData);
        return $newData;
    }

    protected function _saveModule($newData) {
        $this->_allModuleData[$this->_moduleKey] = $newData;
        $row = array();
        $row['attr'] = $this->base64_serialize($this->_allModuleData);
        if (strlen($row['attr']) > 65535) {
            return FALSE;
        }
        $row['status'] = 2;
        $conds = '`identifier` = "' . $this->_identifier . '"';
        return $this->_u($row, $conds);
    }

    public function getArtistIdByTingUid($tingUid) {
        $sql = '';
        $sql .= 'SELECT `artist_id` FROM `ting_artist_base`';
        $sql .= ' WHERE `ting_uid` = ' . $tingUid;
        $sql .= ' LIMIT 1';
        $result = $this->q($sql);
        return $result[0]['artist_id'];
    }

    /**
        * @brief ajax 删除
        *
        * @returns 
     */
    public function del() {
        $ids = $this->_getParam('id');
        if (strpos($ids,',') !== FALSE) {
            $ids = explode(",", $ids);
        }
        if ($ids) {
            $q = $this->_makeQuery($ids,'d');
            if ($q) {
                return array( 'result'=> 1, 'msg'=> 'success',);
            }
        }
        return array( 'result'=> 0, 'msg'=> 'fail delete' );
    }

    public function up() {
        $movedArray = $this->_getModuleData();
        $id = $this->_getParam('id');
        $newData = $this->arrayMove($movedArray, $id);
        if ($newData && $this->_saveModule($newData)) {
            return array( 'result'=> 1, 'msg'=> 'success' );
        }
        return array( 'result'=> 0, 'msg'=> 'failed' );
    }

    public function down() {
        $movedArray = $this->_getModuleData();
        $id = $this->_getParam('id');
        $newData = $this->arrayMove($movedArray, $id, FALSE);
        if ($newData && $this->_saveModule($newData)) {
            return array( 'result'=> 1, 'msg'=> 'success' );
        }
        return array( 'result'=> 0, 'msg'=> 'failed' );
    }

    private function arrayMove($movedArray, $id, $up = TRUE){
        if (!$movedArray || !$id) {
            return FALSE;
        }
        foreach ($movedArray as $key => &$item) {
            if ($id == $key) {
                if (!$up) {
                    $middle = current($movedArray);
                }
                else {
                    prev($movedArray);
                    $middle = prev($movedArray);
                    if (!$middle) {
                        $last = end($movedArray);
                        if ($item == $last) {
                            $middle = prev($movedArray);
                        }
                    }
                }
                if ($middle !== FALSE) {
                    $swapKey = key($movedArray);
                    list($movedArray[$key],$movedArray[$swapKey]) = array($middle,$item);
                }
                break;
            }
        }
        return $movedArray;
    }

}
