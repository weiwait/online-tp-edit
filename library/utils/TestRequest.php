<?php
namespace utils;
class TestRequest {

	public static function buildRequest($host, $path, $method, $param=array()) 
    {
        $method = trim($method); 
        $method = strtoupper($method);

        if("POST" == $method)
        {
            $paramStr = "";
            $paramStr = http_build_query($param); 
            $requestString = $method." ".$path." HTTP/1.1\r\n" .
                            "Host: ".$host."\r\n".
                            "Content-Type: application/x-www-form-urlencoded\r\n".
                            "Content-Length: ".strlen($paramStr)."\r\n".
                            "Connection: close\r\n\r\n";
            $requestString .= $paramStr;
        }
        else
        {
            $requestString = $method." ".$path." HTTP/1.1\r\n" .
                            "Host: ".$host."\r\n".
                            "Connection: close\r\n\r\n";
        
        }
        return $requestString;
	}
    
    public static function sendRequest($host, $port, $requestString)
    {
        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        $result = "";
        if (!$fp) 
        {
            $result = "$errstr ($errno)";
        } 
        else 
        {
            fwrite($fp, $requestString);
            while (!feof($fp)) 
            {
                $result .= fgets($fp, 128);
            }
            fclose($fp);
        }
        return $result;
    }

}
