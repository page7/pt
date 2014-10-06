<?php
/**
 * Http
 +-----------------------------------------
 * @author page7 <zhounan0120@gmail.com>
 * @category
 * @version $Id$
 */
class http
{
    public $type = 'https';

    public $host = '';

    public $port = 80;

    public $user_agent = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)';

    public $accept = '*/*';

    public $accept_encoding = 'gzip';

    public $accept_language = 'zh-cn';

    public $referer = '';

    public $username = '';

    public $password = '';

    protected $cookie = array();


    // __construct
    public function __construct($host, $port=80)
    {
        $this -> host = $host;
        $this -> port = $port;
    }


    // get request
    public function get($path, $params=array(), $timeout=0)
    {
        return $this -> request('GET', $path, $params, $timeout, true);
    }

    // post request
    public function post($path, $params=array(), $timeout=0)
    {
        return $this -> request('POST', $path, $params, $timeout, true);
    }

    // file upload
    public function file($path, $params=array(), $timeout=0)
    {
        $boundary = "----------".substr(md5(rand(0,32000)),0,30);
        $params_string = '';

        foreach ($params as $k => $v)
        {
            if (is_file($v))
            {
                $filename = basename($v);
                $params_string .= "--{$boundary}\r\n";
                $params_string .= "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$filename}\"\r\n";
                $params_string .= "Content-Type: application/octet-stream\r\n\r\n";
                $params_string .= file_get_contents($v)."\r\n";
            }
            else
            {
                $params_string .= "--{$boundary}\r\n";
                $params_string .= "Content-Disposition: form-data; name=\"{$k}\"\r\n\r\n{$v}\r\n";
            }
        }

        $params_string .= "--{$boundary}--\r\n";

        return $this -> request('FILE', $path, $params_string, $timeout, true, $boundary);
    }


    // request
    protected function request($method='GET', $path='', $params=array(), $timeout=0, $redirect_reset=false, $boundary='')
    {
        $params_string = is_array($params) ? http_build_query($params) : $params;

        if($method == 'GET' && $params_string)
            $path = $path . '?' . $params_string;

        $_method = $method == 'FILE' ? 'POST' : $method;

        $headers = array();
        $headers[] = "{$_method} {$this->type}://{$this->host}{$path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
        $headers[] = "Host: {$this -> host}";
        $headers[] = "User-Agent: {$this -> user_agent}";
        $headers[] = "Accept: {$this -> accept}";

        // Gzip encoding
        if ($this -> accept_encoding)
        {
            $headers[] = "Accept-encoding: {$this->accept_encoding}";
        }

        $headers[] = "Accept-Language: {$this->accept_language}";

        // Referer
        if ($this -> referer)
        {
            $headers[] = "Referer: {$this->referer}";
        }

        // Set cookie
        if ($this -> cookie)
        {
            $cookie = 'Cookie: ';
            foreach ($this->cookie as $key => $value)
            {
                if(is_null($value))
                    $cookie .= "{$key}; ";
                else
                    $cookie .= "{$key}={$value}; ";
            }
            $headers[] = $cookie;
        }

        // Basic authentication
        if ($this -> username && $this -> password)
        {
            $headers[] = 'Authorization: BASIC '.base64_encode($this -> username.':'.$this -> password);
        }

        // If this is a POST, set the content type and length
        if ($method == 'POST')
        {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Content-Length: '.strlen($params_string);
        }
        else if ($method == 'FILE')
        {
            $headers[] = 'Content-Type: multipart/form-data, boundary='.$boundary;
            $headers[] = 'Content-Length: '.strlen($params_string);
        }

        $header = implode("\r\n", $headers)."\r\n";

        $context = array(
            'http' => array(
                'method' => $_method,
                'header' => $header,
            )
        );

        // If a POST, set post data content
        if ($_method == 'POST')
        {
            $context['http']['content'] = $params_string;
        }

        // Timeout
        if($timeout > 0)
        {
            $context['http']['timeout'] = $timeout; // Seconds
        }

        try
        {
            $stream_context = stream_context_create($context);
            $response = file_get_contents('http://'.$this->host.$path, false, $stream_context);
            return $this -> response($path, $response, $http_response_header, $redirect_reset);
        }
        catch(Exception $e)
        {
            _exception::append($e);
            return false;
        }
    }


    // response
    protected function response($path, $content, $header=array(), $redirect_reset=false)
    {
        static $redirect = 0;

        // debug var_dump($header);

        // reset redirect number
        if ($redirect_reset)
            $redirect = 0;

        // too many redirect
        if($redirect > 10)
            return false;

        $status = array_filter(explode(' ', $header[0]));
        if (!is_numeric($status[1]))
            return false;

        $status_code = $status[1];

        $headers = array();
        foreach($header as $k => $v)
        {
            if (false === strpos($v, ':'))
                continue;

            $hline = explode(':', $v, 2);
            $key = trim($hline[0]);
            if (!isset($headers[$key]))
            {
                $headers[$key] = trim($hline[1]);
            }
            else
            {
                if (!is_array($headers[$key]))
                    $headers[$key] = array($headers[$key]);

                $headers[$key][] = trim($hline[1]);
            }
        }

        // refresh cookie
        if (isset($headers['Set-Cookie']))
        {
            if(!is_array($headers['Set-Cookie']))
                $headers['Set-Cookie'] = array($headers['Set-Cookie']);

            foreach($headers['Set-Cookie'] as $set_cookie)
            {
                $cookies = explode(';', $set_cookie);
                foreach($cookies as $v)
                {
                    if (strpos($v, '='))
                    {
                        list($key, $value) = explode('=', trim($v), 2);
                        $this -> cookie[$key] = $value;
                    }
                    else
                    {
                        $this -> cookie[trim($v)] = null;
                    }
                }
            }
        }

        // referer
        $this -> referer = $this -> host . $path;

        // gzip
        if (isset($headers['content-encoding']) && $headers['content-encoding'] == 'gzip')
        {
            $content = substr($content, 10); // See http://www.php.net/manual/en/function.gzencode.php
            $content = gzinflate($content);
        }

        // redirect
        if( $status_code == '301' || $status_code == '302' || isset($headers['Location']) && trim( $headers['Location'] ) != '' )
        {
            $redirect ++;
            return $this -> redirect($headers['Location']);
        }

        return array('status'=>$status_code, 'content'=>$content);
    }

    // redirect
    protected function redirect($url, $to)
    {
        $new_url = '';

        if(stripos($to, 'http')===0)
            $new_url = $to;

        $purl = parse_url($url);

        if(strpos($to, '/')===0)
        {
            $new_url = $purl['scheme'].'://'.$purl['host'].$to;
        }
        else
        {
            if(isset($purl['path']))
            {
                if(substr($purl['path'], -1) != '/')
                    $path = dirname($purl['path']).'/';
                else
                    $path = $purl['path'];
            }
            else
            {
                $path = '/';
            }

            if(stripos($to, './') === 0)
            {
                $to = substr($to, 2);
            }

            while(strpos($to, '../') === 0)
            {
                $path = trim(dirname($path), '\\') . '/';
                $to = substr($to, 3);
            }

            $new_url = $purl['scheme'].'://'.$purl['host'].$path.$to;
        }

        $to = parse_url($new_url);

        return $this -> request('GET', ($to['path'] ? $to['path'] : '/'));
    }


    // set cookie
    public function set_cookie($cookie = array())
    {
        $this -> cookie = $cookie;
    }


    // get cookie
    public function get_cookie()
    {
        return $this -> cookie;
    }

}

?>