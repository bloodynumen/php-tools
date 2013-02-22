<?php
/**
* @file TingExtFileDataStorage.class.php
* @brief 以文件形式存储数据 数据格式:数字索引的二维数组json序列化后的字符串
* @author sunhuai(v_sunhuai@baidu.com)
* @version 
* @date 2013-1-6 11:24:41
 */

class TingExtFileDataStorage {
    private $masterDir = NULL;
    private $storeDir = NULL;
    private $backupDir = NULL;
    private $dataFile = NULL;
    private $data = array();
    private $interval = '';

    public function __construct($storeDir, $file) {
        $this->masterDir = ROOT_PATH . '/data/ting/fileDataStorage';
        $this->storeDir = $this->masterDir . '/' . $storeDir;
        $this->backupDir = $this->storeDir . '/' . 'backup';
        $this->dataFile = $this->storeDir . '/' . $file;
        if (!TingExtUtil::createDir($this->masterDir)) {
            exit("master dir could not created");
        }
        if (!TingExtUtil::createDir($this->storeDir)) {
            exit("store dir could not created");
        }
        if (!TingExtUtil::createDir($this->backupDir)) {
            exit("backup dir could not created");
        }
        if (file_exists($this->dataFile)) {
            $content = file_get_contents($this->dataFile);
            if ($content) {
                $this->data = json_decode($content, TRUE);
                if (empty($this->data)) $this->setIndex();
                unset($content);
            }
        } else {
            $this->setIndex();
        }
    }

    /**
        * @brief 禁止从0开始
        *
        * @returns 
     */
    private function setIndex(){
        $this->data[0] = NULL;
        unset($this->data[0]);
    }

    public function getData() {
        return $this->data;
    }

    public function add($item = array(), $isStore = TRUE) {
        if ($item) {
            $this->data[] = $item;
            if ($isStore) {
                return $this->save();
            }
        }
        return FALSE;
    }

    /**
        * @brief 根据数字索引获取数据
        *
        * @param $index
        *
        * @returns 
     */
    public function getByIndex($index) {
        return isset($this->data[$index]) ? $this->data[$index] : array();
    }

    /**
        * @brief 根据数组的key和value 获取数据
        *
        * @param $kv array('key' => 'value')
        *
        * @returns 
     */

    public function find($kv = array()) {
        $rows = array();
        if ($kv) {
            foreach ($this->data as $index => $item) {
                $equal = TRUE;
                foreach ($kv as $k => $v) {
                    if (!isset($item[$k]) || ($item[$k] != $v)) {
                        $equal = FALSE;
                        break;
                    }
                }
                if ($equal) {
                    $rows[$index] = $item;
                }
            }
        }
        return $rows;
    }

    public function updateByIndex($index, $update = array(), $isStore = TRUE) {
        $this->data[$index] = array_merge($this->data[$index], $update);
        if ($isStore) {
            return $this->save();
        }
        return TRUE;
    }

    public function deleteByIndex($index, $isStore = TRUE) {
        unset($this->data[$index]);
        if ($isStore) {
            return $this->save();
        }
        return TRUE;
    }

    public function save() {
        if ($this->interval) {
            $this->backup($this->interval);
        }
        if (file_put_contents($this->dataFile, json_encode($this->data))){
            return TRUE;
        }
        return FALSE;
    }

    /**
        * @brief 设置备份
        *
        * @param $interval 时间间隔 修改时间大于此间隔时才会进行备份
        *
        * @returns 
     */
    public function setBackup($interval = 'day') {
        $this->interval = $interval;
    }

    private function backup($interval) {
        if (!file_exists($this->dataFile)) {
            return FALSE;
        }
        $dateFormat = '';
        switch ($interval) {
            case 'second' :
                $dateFormat = 'YmdHis';
                break;
            case 'minute' :
                $dateFormat = 'YmdHi';
                break;
            case 'hour':
                $dateFormat = 'YmdH';
                break;
            case 'day':
                $dateFormat = 'Ymd';
                break;
            case 'month':
                $dateFormat = 'Ym';
                break;
            default :
                break;
        }
        $filectime = filectime($this->dataFile);
        if (date($dateFormat) > date($dateFormat, $filectime)) {
            $pathParts = pathinfo($this->dataFile);
            $ext = $pathParts['extension'];
            $filename = $pathParts['filename'];
            $backupFile = $this->backupDir . '/' . $filename . '-' . date($dateFormat) . '.' . $ext;
            if (!file_exists($backupFile)) {
                return copy($this->dataFile, $backupFile);
            }
        }
        return FALSE;
    }


}
