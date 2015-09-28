<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class HttpCheck extends BaseMetricPlugin implements MetricPluginInterface
{
    private $url;
    private $method  = 'GET';
    private $headers = [];
    private $timeout = 30.0;

    private $statusToCheck = '200';
    private $headersToCheck;
    private $bodyToCheck;

    private $responseTime = -1;

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->url = $url;
        } else {
            $this->url = '';
        }
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        if (in_array(strtoupper($method), ['GET', 'POST', 'DELETE', 'PUT'])) {
            $this->method = strtoupper($method);
        } else {
            $this->method = '';
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $header
     * @param string $val
     */
    public function addHeader($header, $val)
    {
        $this->headers[ $header ] = $val;
    }

    /**
     * @param string $header
     */
    public function removeHeader($header)
    {
        unset($this->headers[ $header ]);
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getStatusToCheck()
    {
        return $this->statusToCheck;
    }

    /**
     * @param string $statusToCheck
     */
    public function setStatusToCheck($statusToCheck)
    {
        $this->statusToCheck = $statusToCheck;
    }

    /**
     * @return mixed
     */
    public function getHeadersToCheck()
    {
        return $this->headersToCheck;
    }

    /**
     * @param mixed $headersToCheck
     */
    public function setHeadersToCheck($headersToCheck)
    {
        $this->headersToCheck = $headersToCheck;
    }

    /**
     * @return mixed
     */
    public function getBodyToCheck()
    {
        return $this->bodyToCheck;
    }

    /**
     * @param mixed $bodyToCheck
     */
    public function setBodyToCheck($bodyToCheck)
    {
        $this->bodyToCheck = $bodyToCheck;
    }

    private function checkStatusCode($responseStatusCode)
    {
        return $this->statusToCheck==$responseStatusCode;
    }

    private function checkResponseHeaders($responseHeaders)
    {
        if (is_array($this->headersToCheck)===false || count($this->headersToCheck)==0) {
            return true;
        }
        foreach ($this->headersToCheck as $header => $exceptedValue) {
            if (isset($responseHeaders[$header])===false
                || in_array($exceptedValue, $responseHeaders[$header])===false) {
                return false;
            }
        }
        return true;
    }

    private function checkResponseBody($responseBody)
    {
        return !$this->bodyToCheck || $this->bodyToCheck==$responseBody;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function checkResponse($response)
    {
        return $this->checkStatusCode($response->getStatusCode())
        && $this->checkResponseHeaders($response->getHeaders())
        && $this->checkResponseBody($response->getBody()->getContents());
    }

    /**
     * @return Metric[]|null|bool
     */
    public function getMetrics()
    {
        if (!$this->url) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Url is not defined to check!');
            }
            return false;
        }
        if (!$this->method) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Method is not defined to check!');
            }
            return false;
        }

        try {
            $client   = $this->diObj->getGuzzleHttpClient();
            $response = $client->request($this->method, $this->url, [
                'http_errors' => false,
                'connect_timeout' => $this->timeout,
                'timeout' => $this->timeout,
                'on_stats' => function (TransferStats $stats) {
                    $this->responseTime = $stats->getTransferTime();
                }
            ]);
            if ($this->checkResponse($response)===false) {
                return [$this->createNewMetric('HttpCheckFail', 'Count', 1)];
            } else {
                return [$this->createNewMetric('HttpCheck', 'Seconds', $this->responseTime)];
            }
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Guzzle Http client thrown exception! Msg: ' . $e->getMessage());
            }
            return [$this->createNewMetric('HttpCheckFail', 'Count', 1)];
        }
    }
}