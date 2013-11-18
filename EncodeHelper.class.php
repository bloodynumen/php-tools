<?php
/**
 * �ṩ�����������
 * @author qiuyonggang
 *
 */
class EncodeHelper extends cmsCommonService{
    public function __construct(){
        parent::__construct('EncodeHelper');
    }
    /**
     * jsת�壬��content��js�ַ�ת��
     * ' => \'
     * " => \"
     * \ => \\
     * \n => \n
     * \r => \r
     * ����\nת���ǽ�����ת���\n�ַ���\rת���ǽ��س�ת���\r�ַ�
     * @param string $content ��Ҫת����ı�
     * @return string ת�����ı� 
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
     *  xmlת�壬��content��xml�ַ�ת��
     *  < => &lt;
     *  > => &gt;
     *  & => &amp;
     *  ' => &#039;
     *  " => &quot;
     *  @param string $content ��Ҫת����ı�
     *  @return string ת�����ı�
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
     * onclickת��
     * \ => \\
     * & => &amp;
     * < => &lt;
     * > => &gt;
     * ' => \&#039
     * " => \&quot;
     * \n => \n
     * \r => \r
     * / => \/
     *  @param string $content ��Ҫת����ı�
     *  @return string ת�����ı�
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
     *  htmlת�壬��content��html�ַ�ת��
     *  < => &lt;
     *  > => &gt;
     *  ' => &#039;
     *  " => &quot;
     *  @param string $content ��Ҫת����ı�
     *  @return string ת�����ı�
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
     *  json+htmlת�壬ת��������£�
     * ' => \'
     * " => \"
     * \ => \\
     * \n => \n
     * \r => \r
     *  < => &lt;
     *  > => &gt;
     * ����\nת���ǽ�����ת���\n�ַ���\rת���ǽ��س�ת���\r�ַ�
     * @param string $content ��Ҫת����ı�
     * @return string ת�����ı�
     */
    public function js_html_encode($content){
        $content = js_encode($content);
        $content = str_replace('<','&lt;',$content);
        $content = str_replace('>','&gt;',$content);
        return $content;
    }

    /**
     *  �ȶ��ı���GBK��UTF8��ת�룬Ȼ����urlencode
     *  @param string $content ��Ҫת����ı�
     *  @return string ת�����ı�
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
     * �ض��ı�
     * @param string $title ��Ҫ�ضϵ�title
     * @param int $len �ضϵĳ���
     * @param string $etc �ضϺ󸽼ӵ�ʡ�Է���
     * @return string �ضϺ���ı�
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
     * ȥ���ַ�����������ֵĺ���������ַ�������ǰ�����֣��򷵻�ȥ����������ֵ��ַ�����
     * ���򷵻�ԭʼ�ַ���
     * @param string $str ԭʼ�ı�
     * @return string ����ַ���
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
     * ȥ��<����ǰ��>���ź��\n,\r�ַ������س����л��߻����滻Ϊ' ',����ո��滻Ϊ�����ո� 
     * @param string $str ԭʼ���ַ���
     * @return string ת������ַ���
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
