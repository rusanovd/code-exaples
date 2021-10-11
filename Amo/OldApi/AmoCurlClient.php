<?php

declare(strict_types=1);

namespace More\Amo\OldApi;

class AmoCurlClient
{
    private $ch;
    private $httpGet = '';
    private $head = '';
    private $isPost = false;
    private $postParams = null;
    private $httpHeader = [];
    private $cookie = [];
    private $proxy = '';
    private $proxyUserData = '';
    private $verbose = 0;
    private $referer = '';
    private $autoReferer = 0;
    private $writeHeader = '';
    private $agent = 'Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0';
    private $url = '';
    private $followLocation = 1;
    private $returnTransfer = 1;
    private $sslVerifyPeer = 0;
    private $sslVerifyHost = 2;
    private $sslCert = '';
    private $sslKey = '';
    private $caInfo = '';
    private $cookieFile = '';
    private $timeout = 0;
    private $connectTime = 0;
    private $encoding = 'deflate';
    private $interface = '';

    public function __construct()
    {
        $this->ch = curl_init();
        $this->setHttpHeader([
            'X-Requested-With: XMLHttpRequest',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3',
            'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7',
        ]);
    }

    public function get($url)
    {
        $this->url = $url;

        return $this->exec();
    }

    public function post($url, $postParams = null)
    {
        $this->url = $url;
        $this->isPost = true;
        $this->postParams = $postParams;

        return $this->exec();
    }

    private function setCookie()
    {
        preg_match_all('/Set-Cookie: (.*?)=(.*?);/i', $this->head, $matches, PREG_SET_ORDER);

        $count = count($matches);
        for ($i = 0; $i < $count; $i++) {
            if ($matches[$i][2] == 'deleted') {
                $this->deleteCookie($matches[$i][1]);
            } else {
                $this->cookie[$matches[$i][1]] = $matches[$i][2];
            }
        }
    }

    private function addCookie($cookie)
    {
        foreach ($cookie as $name => $value) {
            $this->cookie[$name] = $value;
        }
    }

    private function deleteCookie($name)
    {
        if (isset($this->cookie[$name])) {
            unset($this->cookie[$name]);
        }
    }

    private function getCookie()
    {
        return $this->cookie;
    }

    private function clearCookie()
    {
        $this->cookie = [];
    }

    private function setHttpHeader($httpHeader)
    {
        $this->httpHeader = $httpHeader;
    }

    private function clearHttpHeader()
    {
        $this->httpHeader = [];
    }

    private function setHead($head)
    {
        $this->head = $head;
    }

    private function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    private function setInterface($interface)
    {
        $this->interface = $interface;
    }

    private function setWriteHeader($writeHeader)
    {
        $this->writeHeader = $writeHeader;
    }

    private function setFollowLocation($followLocation)
    {
        $this->followLocation = $followLocation;
    }

    private function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = $returnTransfer;
    }

    private function setSslVerifyPeer($sslVerifypeer)
    {
        $this->sslVerifyPeer = $sslVerifypeer;
    }

    private function setSslVerifyHost($sslVerifyHost)
    {
        $this->sslVerifyHost = $sslVerifyHost;
    }

    private function setSslCert($sslCert)
    {
        $this->sslCert = $sslCert;
    }

    private function setSslKey($sslKey)
    {
        $this->sslKey = $sslKey;
    }

    private function setCaInfo($caInfo)
    {
        $this->caInfo = $caInfo;
    }

    private function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function setConnectTime($connectTime)
    {
        $this->connectTime = $connectTime;
    }

    public function setCookieFile($cookieFile)
    {
        $this->cookieFile = $cookieFile;
    }

    private function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    private function setProxyAuth($proxyUserData)
    {
        $this->proxyUserData = $proxyUserData;
    }

    private function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    private function getError()
    {
        return curl_errno($this->ch);
    }

    private function getLocation()
    {
        $result = '';

        if (preg_match("/Location: (.*?)\r\n/is", $this->head, $matches)) {
            $result = end($matches);
        }

        return $result;
    }

    public function getHttpCode()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    private function getSpeedDownload()
    {
        return curl_getinfo($this->ch, CURLINFO_SPEED_DOWNLOAD);
    }

    private function getContentType()
    {
        return curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
    }

    private function getUrl()
    {
        return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
    }

    private function joinCookie()
    {
        $result = [];
        foreach ($this->cookie as $key => $value) {
            $result[] = "$key=$value";
        }

        return join('; ', $result);
    }

    private function exec()
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, $this->autoReferer);
        curl_setopt($this->ch, CURLOPT_ENCODING, $this->encoding);
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_POST, $this->isPost);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->returnTransfer);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connectTime);
        curl_setopt($this->ch, CURLOPT_VERBOSE, $this->verbose);

        if ($this->referer) {
            curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
        }

        if ($this->interface) {
            curl_setopt($this->ch, CURLOPT_INTERFACE, $this->interface);
        }

        if ($this->httpGet) {
            curl_setopt($this->ch, CURLOPT_HTTPGET, $this->httpGet);
        }

        if ($this->writeHeader != '') {
            curl_setopt($this->ch, CURLOPT_WRITEHEADER, $this->writeHeader);
        }

        if ($this->isPost) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postParams);
        }

        if ($this->proxy) {
            curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy);
        }

        if ($this->proxyUserData) {
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxyUserData);
        }

        if ($this->cookie) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $this->joinCookie());
        }

        if (count($this->httpHeader)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);
        }

        if ($this->sslCert) {
            curl_setopt($this->ch, CURLOPT_SSLCERT, $this->sslCert);
        }

        if ($this->sslKey) {
            curl_setopt($this->ch, CURLOPT_SSLKEY, $this->sslKey);
        }

        if ($this->caInfo) {
            curl_setopt($this->ch, CURLOPT_CAINFO, $this->caInfo);
        }

        if ($this->cookieFile) {
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }

        $response = curl_exec($this->ch);
        $this->setHead(substr($response, 0, curl_getinfo($this->ch, CURLINFO_HEADER_SIZE)));
        $response = substr($response, curl_getinfo($this->ch, CURLINFO_HEADER_SIZE));
        $this->setCookie();

        $this->postParams = null;
        $this->isPost = false;

        return $response;
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}
