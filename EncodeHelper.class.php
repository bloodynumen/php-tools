<?php
/**
 * 提供编码帮助函数
 * @author qiuyonggang
 *
 */
class EncodeHelper extends cmsCommonService{
    public function __construct(){
        parent::__construct('EncodeHelper');
    }
    /**
     * js转义，将content中js字符转义
     * ' => \'
     * " => \"
     * \ => \\
     * \n => \n
     * \r => \r
     * 其中\n转义是将换行转义成\n字符，\r转义是将回车转义成\r字符
     * @param string $content 需要转义的文本
     * @return string 转义后的文本 
     */
    public function js_encode($content){
        $content = str_replace('\\','\\\\',$content);
        $content = str_replace('\'','\\\'',$content);
        $content = str_replace('"','\\"',$content);
        $content = str_replace("\n",'\\n',$content);
        $content = str_replace("\r",'\\r',$content);
        $content = str_replace('/', '\\/', $content);
        return $content;
    }

    /**
     *  xml转义，将content中xml字符转义
     *  < => &lt;
     *  > => &gt;
     *  & => &amp;
     *  ' => &#039;
     *  " => &quot;
     *  @param string $content 需要转义的文本
     *  @return string 转义后的文本
     */
    public function xml_encode($content){
        $content = str_replace('&','&amp;',$content);
        $content = str_replace('<','&lt;',$content);
        $content = str_replace('>','&gt;',$content);
        $content = str_replace('\'','&#039;',$content);
        $content = str_replace('"','&quot;',$content);

        return $content;
    }
    /**
     * onclick转义
     * \ => \\
     * & => &amp;
     * < => &lt;
     * > => &gt;
     * ' => \&#039
     * " => \&quot;
     * \n => \n
     * \r => \r
     * / => \/
     *  @param string $content 需要转义的文本
     *  @return string 转义后的文本
     */
    public function onclick_encode( $content ){
        $content = str_replace('\\','\\\\',$content);
        $content = str_replace('&','&amp;',$content);
        $content = str_replace('<','&lt;',$content);
        $content = str_replace('>','&gt;',$content);
        $content = str_replace('\'','\\&#039;',$content);
        $content = str_replace('"','\\&quot;',$content);
        $content = str_replace("\n",'\\n',$content);
        $content = str_replace("\r",'\\r',$content);
        $content = str_replace('/', '\\/', $content);
        return $content;
    }

    /**
     *  html转义，将content中html字符转义
     *  < => &lt;
     *  > => &gt;
     *  ' => &#039;
     *  " => &quot;
     *  @param string $content 需要转义的文本
     *  @return string 转义后的文本
     */
    public function html_encode($content){
        $content = str_replace('&','&amp;',$content);
        $content = str_replace('<','&lt;',$content);
        $content = str_replace('>','&gt;',$content);
        $content = str_replace('\'','&#039;',$content);
        $content = str_replace('"','&quot;',$content);
        return $content;
    }

    /**
     *  json+html转义，转义规则如下：
     * ' => \'
     * " => \"
     * \ => \\
     * \n => \n
     * \r => \r
     *  < => &lt;
     *  > => &gt;
     * 其中\n转义是将换行转义成\n字符，\r转义是将回车转义成\r字符
     * @param string $content 需要转义的文本
     * @return string 转义后的文本
     */
    public function js_html_encode($content){
        $content = js_encode($content);
        $content = str_replace('<','&lt;',$content);
        $content = str_replace('>','&gt;',$content);
        return $content;
    }

    /**
     *  先对文本做GBK到UTF8的转码，然后做urlencode
     *  @param string $content 需要转义的文本
     *  @return string 转义后的文本
     */
    public function utf8_url_encode( $content ){
        $content = iconv( "GBK", "UTF-8", $content );
        $content = urlencode( $content );
        return $content;
    }
    
    public function utf8_entity_url_encode( $content ){
        $content = iconv( "GBK", "UTF-8", $content );
        $content = html_entity_decode( $content );
        $content = urlencode( $content );
        return $content;
    }

    /**
     * 截断文本
     * @param string $title 需要截断的title
     * @param int $len 截断的长度
     * @param string $etc 截断后附加的省略符合
     * @return string 截断后的文本
     */
    public function get_str_by_length( $title,$len,$etc='...' ){

        if( $len==null ){
            return $title;
        }
        $matches = array();
        $ret = preg_match_all('/&#[0-9]*;/',$title,$matches);
        if( $ret>0 ){
            $special_chars = $matches[0];
            for($i=0;$i<count($special_chars);$i++){
                $title = str_replace($special_chars[$i],chr(2)."$i",$title);
            }
            //lib_log("title22=$title");
        }
        if( strlen($title)<=$len ){
            if( $ret>0 ){
                for($j=0;$j<$i;$j++){
                    $title = str_replace(chr(2).$j,$special_chars[$j],$title);
                }
            }
            return $title;
        }
        $title = substr($title,0,$len);

        //lib_log("title23332=$title");
        if( $ret>0 ){
            for($j=0;$j<$i;$j++){
                $title = str_replace(chr(2).$j,$special_chars[$j],$title);
            }
        }
        $title = $this->filter_semi_char($title);
        $title.= $etc;
        return $title;

    }
    /**
     * 去除字符串最后半个汉字的函数，如果字符串最后是半个汉字，则返回去除最后半个汉字的字符串，
     * 否则返回原始字符串
     * @param string $str 原始文本
     * @return string 结果字符串
     */
    public function filter_semi_char($str){
        if( strlen($str)==0 )
        return $str;
        $ret = true;
        $i=0;
        while($i<=strlen($str)){
            $temp_str = substr($str,$i,1);
            if( !eregi("[^\x80-\xff]","$temp_str") ){
                if( $i==strlen($str)-1 ){
                    $ret = false;
                }
                $i = $i+2;
            }
            else{
                $i++;
            }
        }
        if( $ret==false )
        $str = substr($str,0,-1);
        return $str;
    }

    /**
     * 去除<符号前，>符号后的\n,\r字符，将回车换行或者换行替换为' ',多个空格替换为单个空格 
     * @param string $str 原始的字符串
     * @return string 转化后的字符串
     */
    public function cms_strip($str){
        $str = str_replace("\t"," ",$str);
        $str = preg_replace('/[\n|\r]+</msU','<',$str);
        $str = preg_replace('/[\n|\r| ]+</msU',' <',$str);
        $str = preg_replace('/>[\n|\r]+/','>',$str);
        $str = preg_replace('/>[\n|\r| ]+/','> ',$str);
        $str = str_replace("\r\n",' ',$str);
        $str = str_replace("\n",' ',$str);
        $str = preg_replace('/ +</msU'," <",$str);
        $str = preg_replace('/> +/',"> ",$str);
        return $str;
    }

   
}
?>
