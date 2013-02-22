<?php
/**
* @file TingExtHttpRequest.class.php
* @brief http请求类
* @author sunhuai(v_sunhuai@baidu.com)
* @version 1.0
* @date 2013-1-31 8:56:53
 */

class TingExtHttpRequest {

    public static function _getParam($name, $default) {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        return $default;
    }

    public static function _postParam($name, $default) {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return $default;
    }

    public static function _getFields($filterFields = array(), $requestMethod = 'GET') {
        if (empty($filterFields)) {
            return array();
        }
        $fields = array();
        if ($requestMethod == 'GET') {
            $method = $_GET;
        } else {
            $method = $_POST;
        }
        foreach ($filterFields as $column => $filterMethodStr) {
            if (isset($method[$column])) {
                if ($filterMethodStr) {
                    $filterMethodArray = explode(',', $filterMethodStr);
                    if (count($filterMethodArray) == 1) {
                        $fields[$column] = $filterMethodArray[0]($method[$column]);
                    } else {
                        foreach ($filterMethodArray as $filterMethod) {
                            $fields[$column] = $filterMethod($method[$column]);
                        }
                    }
                } else {
                    $fields[$column] = $method[$column];
                }
            }
        }
        return $fields;
    }
}
