<?php


    public function mb_substr_replace ($string, $replacement, $start, $length = 0) 
    {
        if (is_array($string)) 
        {
            foreach ($string as $i => $val)
            {
                $repl = is_array ($replacement) ? $replacement[$i] : $replacement;
                $st   = is_array ($start) ? $start[$i] : $start;
                $len  = is_array ($length) ? $length[$i] : $length;

                $string[$i] = mb_substr_replace ($val, $repl, $st, $len);
            }

            return $string;
        }

        $result  = mb_substr ($string, 0, $start, 'UTF-8');
        $result .= $replacement;

        if ($length > 0) {
            $result .= mb_substr ($string, ($start+$length+1), mb_strlen($string, 'UTF-8'), 'UTF-8');
        }

        return $result;
    }
    
