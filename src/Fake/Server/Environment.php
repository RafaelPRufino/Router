<?php

namespace Punk\Fake\Server;

class Environment {

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    /**
     * @var array
     */
    protected $formDataMediaTypes = array('application/x-www-form-urlencoded');

    /**
     * @var array
     */
    protected function env() {
        return $_SERVER;
    }

    /**
     * Get HTTP method
     * @return string
     */
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Is this a GET request?
     * @return bool
     */
    public function isGet() {
        return self::getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * @return bool
     */
    public function isPost() {
        return self::getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * @return bool
     */
    public function isPut() {
        return self::getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a DELETE request?
     * @return bool
     */
    public function isDelete() {
        return self::getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * @return bool
     */
    public function isHead() {
        return self::getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this a OPTIONS request?
     * @return bool
     */
    public function isOptions() {
        return self::getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is this an AJAX request?
     * @return bool
     */
    public function isAjax() {
        if (self::params('isajax')) {
            return true;
        } elseif (isset($_SERVER['X_REQUESTED_WITH']) && $_SERVER['X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this an XHR request?
     * @return bool
     */
    public function isXhr() {
        return self::isAjax();
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string           $key
     * @return array|mixed|null
     */
    public function params($key = null) {
        $union = array_merge(self::get(), self::post());
        if ($key) {
            if (isset($union[$key])) {
                return $union[$key];
            } else {
                return null;
            }
        } else {
            return $union;
        }
    }

    /**
     * Fetch GET
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string           $key
     * @return array|mixed|null
     */
    public function get() {
       return $_GET;
    }

     /**
     * Fetch POST
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string           $key
     * @return array|mixed|null
     */
    public function post() {
       return $_POST;
    }

    /**
     * Get Headers
     *
     * This method returns a key-value array of headers sent in the HTTP request, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string $key
     * @param  mixed  $default The default value returned if the requested header is not available
     * @return mixed
     */
    public function headers($key = null, $default = null) {
        if ($key) {
            $key = strtoupper($key);
            $key = str_replace('-', '_', $key);
            $key = preg_replace('@^HTTP_@', '', $key);
            if (isset($_SERVER[$key])) {
                return $_SERVER[$key];
            } else {
                return $default;
            }
        } else {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                $headers[$key] = $value;
            }

            return $headers;
        }
    }

    /**
     * Get Headers
     *
     * This method returns a key-value array of headers sent in the HTTP request, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string $key
     * @param  mixed  $default The default value returned if the requested header is not available
     * @return mixed
     */
    public function requestHeaders($key = null, $default = null) {
        if ($key) {
            $headers = apache_request_headers();
            if (isset($headers[$key])) {
                return $headers[$key];
            } else {
                return $default;
            }
        } else {
            $headers = array();
            foreach (apache_request_headers() as $key => $value) {
               $headers[$key] = $value;
            }
            return $headers;
        }
    }

    /**
     * Get Body
     * @return string
     */
    public function getBody() {
        return file_get_contents("php://input");
    } 
    
    /**
     * Get PHP Inputs
     * @return mixed
     * */
    public function getBodyJson() {
        return json_decode($this->getBody(), true);
    }

    /**
     * Get Content Type
     * @return string
     */
    public function getContentType() {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            return $_SERVER['CONTENT_TYPE'];
        } else {
            return null;
        }
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     * @return string|null
     */
    public function getMediaType() {
        $contentType = self::getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        } else {
            return null;
        }
    }

    /**
     * Get Media Type Params
     * @return array
     */
    public function getMediaTypeParams() {
        $contentType = self::getContentType();
        $contentTypeParams = array();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get Content Charset
     * @return string|null
     */
    public function getContentCharset() {
        $mediaTypeParams = self::getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        } else {
            return null;
        }
    }

    /**
     * Get Content-Length
     * @return int
     */
    public function getContentLength() {
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            return (int) $_SERVER['CONTENT_LENGTH'];
        } else {
            return 0;
        }
    }

    /**
     * Get Host
     * @return string
     */
    public function getHost() {
        if (isset($_SERVER['HOST'])) {
            if (strpos($_SERVER['HOST'], ':') !== false) {
                $hostParts = explode(':', $_SERVER['HOST']);

                return $hostParts[0];
            }

            return $_SERVER['HOST'];
        } else {
            return $_SERVER['SERVER_NAME'];
        }
    }

    /**
     * Get Host with Port
     * @return string
     */
    public function getHostWithPort() {
        return sprintf('%s:%s', self::getHost(), self::getPort());
    }

    /**
     * Get Port
     * @return int
     */
    public function getPort() {
        return (int) $_SERVER['SERVER_PORT'];
    }

    /**
     * Get Script Name (physical path)
     * @return string
     */
    public function getScriptName() {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * LEGACY: Get Root URI
     * @return string
     */
    public function getRootUri() {
        return self::getScriptName();
    }

    /**
     * Get Path (physical path + virtual path)
     * @return string
     */
    public function getPath() {
        return self::getScriptName() . self::getPathInfo();
    }

    /**
     * Get Path Info (virtual path)
     * @return string
     */
    public function getPathInfo() {
        return $_SERVER['PATH_INFO'] ?? '';
    }

    /**
     * LEGACY: Get Resource URI
     * @return string
     */
    public function getResourceUri() {
        $resourceUri = trim(self::getPathInfo());

        if (trim(substr($resourceUri, -1)) === '/' && strlen($resourceUri) > 1) {
            $resourceUri = trim(substr($resourceUri, 0, -1));
        }
        return $resourceUri;
    }

    /**
     * Get Scheme (https or http)
     * @return string
     */
    public function getScheme() {
        return $_SERVER['SERVER_PROTOCOL'] === "HTTP/1.1" ? 'http' : 'http';
    }

    /**
     * Get URL (scheme + host [ + port if non-standard ])
     * @return string
     */
    public function getUrl() {
        $url = self::getScheme() . '://' . self::getHost();
        if ((self::getScheme() === 'https' && self::getPort() !== 443) || (self::getScheme() === 'http' && self::getPort() !== 80)) {
            $url .= sprintf(':%s', self::getPort());
        }

        return $url;
    }

    /**
     * Get IP
     * @return string
     */
    public function getIp() {
        if (isset($_SERVER['X_FORWARDED_FOR'])) {
            return $_SERVER['X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['CLIENT_IP'])) {
            return $_SERVER['CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get Referrer
     * @return string|null
     */
    public function getReferrer() {
        if (isset($_SERVER['REFERER'])) {
            return $_SERVER['REFERER'];
        } else {
            return null;
        }
    }

    /**
     * Get Referer (for those who can't spell)
     * @return string|null
     */
    public function getReferer() {
        return self::getReferrer();
    }

    /**
     * Get User Agent
     * @return string|null
     */
    public function getUserAgent() {
        if (isset($_SERVER['USER_AGENT'])) {
            return $_SERVER['USER_AGENT'];
        } else {
            return null;
        }
    }

    /**
     * Retorna o Environment
     * @return \Punk\Fake\Server\Environment
     * */
    public static function instance(): self {
        return new static ();
    }

}
