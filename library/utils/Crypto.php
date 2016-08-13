<?php
declare(encoding = 'UTF-8');
namespace utils;
class Crypto
{
    /**
     * @desc 加密一个字符串
     *
     * @param string string 需要解密的数据
     * @param string key
     * @param int    expiry
     */
    public static function encode($string, $key, $expiry = 0) {
        return self::authcode($string, self::ENCODE, $key, $expiry);
    }
    /**
     * @desc 解码一个字符串, 成功返回解码后的字符串，校验失败返回false
     *
     * @param string string 需要解密的数据
     * @param string key
     */
    public static function decode($string, $key) {
        return self::authcode($string, self::DECODE, $key);
    }
    /**
     * @desc sid 可逆加密算法
     *
     * @param $string    string  - 待加密（解密）的字符
     * @param $operation string  - 方法 DECODE | ENCODE
     * @param $key       string  - 秘钥
     * @param $expiry    numeric - 失效时间
     *
     * @return string            - 加密（解密）的结果
     */
    private static function authcode($string, $operation, $key, $expiry = 0)
    {
        $ckey_length = 4;
        $key        = md5($key);
        $keya       = md5(substr($key, 0, 16));
        $keyb       = md5(substr($key, 16, 16));
        $keyc       = $ckey_length ? ($operation == self::DECODE ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey   = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string     = $operation == self::DECODE ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string.$keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box    = range(0, 255);
        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for($j = $i = 0; $i < 256; $i++) {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a       = ($a + 1) % 256;
            $j       = ($j + $box[$a]) % 256;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        $res = '';
        if($operation == self::DECODE) {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                $res = substr($result, 26);
            } else {
                $res = '';
            }
        } else {
            $res = $keyc.str_replace('=', '', base64_encode($result));
        }
        return $res;
    }
    const ENCODE = 'ENCODE';
    const DECODE = 'DECODE';
}
