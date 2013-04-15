<?php
/***************************************************************************
 * 
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
/**
* @file ATingExt.class.php
* @brief 对Ating 进行了简单的扩展 简化了一些CURD 初始化方法等
* @author sunhuai(v_sunhuai@baidu.com)
* @version 
* @date 2012-10-25 14:48:43
 */
 
class ATingExt extends ATing {

    //操作的table 名称
    protected $_table = '';

    /**
        * @brief 继承基类的初始化方法 并定义一个init
        *
        * @returns 
     */
    public function __construct() {
        parent::__construct();
        $this->init();
    }

    public function tingQuery($condition) {
    
    }

    /**
        * @brief 自定义初始化方法
        *
        * @returns 
     */
    public function init() {
    }

    public function _i($row, $options = NULL, $onUup = NULL) {
        return $this->i($this->_table, $row, $options, $onUup);
    }

    public function _u($row, $conds = NULL, $options = NULL, $appends = NULL)
    {
        return $this->u($this->_table, $row, $conds, $options, $appends);
    }

    public function _selectCount($conds = NULL) {
        return $this->selectCount($this->_table, $conds);
    }

    public function _select($columnList = array(), $where = array(), $orderBy = '', $offset, $num) {
        $sql = '';
        $columns = '*';
        $conditionList = array();
        if (!is_array($columnList) || !is_array($where)) {
            return FALSE;
        }
        if (!empty($columnList)) {
            $columns = join(',', $columnList);
        }
        $sql .= 'SELECT ' . $columns . ' FROM ' . $this->_table;
        if (!empty($where)) {
            foreach ($where as $columnStr => $value) {
                $columnAttr = explode(" ", trim($columnStr));
                $column = $columnAttr[0];
                if (count($columnAttr) == 1) {
                    if (is_string($value)) {
                        $conditionList[] = $column . ' = ' . "'" . $value . "'";
                    } else {
                        $conditionList[] = $column . ' = ' . $value;
                    }
                } else {
                    $operator = ' ' . $columnAttr[1] . ' ';
                    if ($columnAttr[1] != 'in') {
                        if (is_string($value)) {
                            $conditionList[] = $column . $operator . "'" . $value . "'";
                        } else {
                            $conditionList[] = $column . $operator . $value;
                        }
                    } else {
                        $inCondition = array();
                        foreach ($value as $inVal) {
                            if (is_string($inVal)) {
                                $inCondition[] = "'" . $inVal . "'";
                            } else {
                                $inCondition[] = $inVal;
                            }
                        }
                        $conditionList[] = $column . $operator . "(" . join(',', $inCondition) . ")";
                    }
                }
            }
            $sql .= ' WHERE ' . join(' AND ', $conditionList);
        }
        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        if (isset($offset, $num)) {
            $sql .= ' LIMIT ' . $offset . ',' . $num;
        }
        echo $sql;
        exit();
        return $this->q($sql);
    }

    public function _d($conds = NULL, $options = NULL, $appends = NULL) {
        return $this->d($this->_table, $conds, $options, $appends);
    }


    /**
        * @brief 简化GET获取参数
        *
        * @param $name
        * @param $default
        *
        * @returns 
     */
    public function _getParam($name, $default) {
        return TingExtHttpRequest::_getParam($name, $default);
    }

    /**
        * @brief 简化POST获取参数
        *
        * @param $name
        * @param $default
        *
        * @returns 
     */
    public function _postParam($name, $default) {
        return TingExtHttpRequest::_postParam($name, $default);
    }

    public function _getFields($filterFields = array(), $requestMethod = 'GET') {
        return TingExtHttpRequest::_getFields($filterFields, $requestMethod);
    }
}
