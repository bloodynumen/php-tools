<?php
/**
 * @file TingSystemLog.class.php
 * @brief 通用的日志系统
 * @author sunhuai(v_sunhuai@baidu.com)
 * @version 
 * @date 2012-12-28 11:01:22
 */

class TingSystemLog {
    private $parentDir = '';
    private $logDir = '';
    private $logFile = '';
    private $handle = '';

    public function __construct($fileDir) {
        $this->parentDir = ROOT_PATH . '/data/ting/systemLog';
        $this->logDir = $this->parentDir . '/' . $fileDir;
        $this->logFile = $this->logDir . '/' . date('Y-m-d') . '.txt';
        $this->createDir($this->parentDir);
        $this->createDir($this->logDir);
        $this->handle = fopen($this->logFile, 'ab');
    }

    public function createDir($dir) {
        if (is_dir($dir)) {
            return TRUE;
        }
        if (mkdir($dir)) {
            return TRUE;
        }
        return FALSE;
    }

    public function writeLog($words = '', $opt=array()) {
        $words = date('Y-m-d H:i:s') . "\t" . $words;
        if (!empty($opt)) {
            $words .= "\t" . json_encode($opt);
        }
        $words .= "\r\n";
        return $this->_write($words);
    }

    private function _write($words) {
        if (!$this->handle) {
            return FALSE;
        }
        return fwrite($this->handle, $words);
    }

    public function __destruct() {
        $this->handle ? fclose($this->handle) : '';
    }

}
