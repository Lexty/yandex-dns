<?php

namespace Lexty\YandexDns;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class Dns
 *
 * @package Lexty\YandexDns
 *
 * @link https://tech.yandex.ru/pdd/doc/concepts/api-dns-docpage/
 */
class Dns
{
    const TYPE_A = 'a';
    const TYPE_AAAA = 'aaaa';
    const TYPE_CNAME = 'cname';
    const TYPE_MX = 'mx';
    const TYPE_NS = 'ns';
    const TYPE_SRV = 'srv';
    const TYPE_TXT = 'txt';
    const TYPE_SOA = 'soa';

    const FIELD_CONTENT = 'content';
    const FIELD_SUBDOMAIN = 'subdomain';
    const FIELD_TTL = 'ttl';
    const FIELD_PRIORITY = 'priority';
    const FIELD_WEIGHT = 'weight';
    const FIELD_PORT = 'port';
    const FIELD_TARGET = 'target';
    const FIELD_ADMIN_MAIL = 'admin_mail';
    const FIELD_REFRESH = 'refresh';
    const FIELD_RETRY = 'retry';
    const FIELD_EXPIRE = 'expire';
    const FIELD_NEG_CACHE = 'neg_cache';
    const FIELD_RECORD_ID = 'record_id';
    const FIELD_TYPE = 'type';

    /**
     * @param string $domainName
     * @param string $pddToken
     * @param string $oAuthToken
     */
    public function __construct($domainName, $pddToken = '', $oAuthToken = '')
    {
        $this->setDomainName($domainName);
        $this->setPddToken($pddToken);
        $this->setOAuthToken($oAuthToken);
        $this->access = $oAuthToken ? $this->registrarAccess : $this->adminAccess;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @return string
     */
    public function getPddToken()
    {
        return $this->pddToken;
    }

    /**
     * @param string $pddToken
     */
    public function setPddToken($pddToken)
    {
        $this->pddToken = $pddToken;
    }

    /**
     * @return string
     */
    public function getOAuthToken()
    {
        return $this->oAuthToken;
    }

    /**
     * @param string $oAuthToken
     */
    public function setOAuthToken($oAuthToken)
    {
        $this->oAuthToken = $oAuthToken;
    }

    /**
     * @return array
     */
    public function records()
    {
        return $this->send('list');
    }

    /**
     * @param string $type
     * @param array $params
     * @return array
     */
    public function add($type, array $params = [])
    {
        $constant = static::class . '::TYPE_' . strtoupper($type);
        if (!defined($constant)) {
            throw new \InvalidArgumentException(sprintf('Unknown record type: %s.', $type));
        }
        $params[self::FIELD_TYPE] = constant($constant);
        return $this->send('add', $params);
    }

    /**
     * @param int $recordId
     * @param array $params
     * @return array
     */
    public function edit($recordId, array $params = [])
    {
        $params[self::FIELD_RECORD_ID] = $recordId;
        return $this->send('edit', $params);
    }

    /**
     * @param int $recordId
     * @return array
     */
    public function remove($recordId)
    {
        return $this->send('del', [self::FIELD_RECORD_ID => $recordId]);
    }

    private $serviceScheme = 'https';
    private $serviceDomain = 'pddimp.yandex.ru';
    private $apiVersion = 'api2';
    private $adminAccess = 'admin';
    private $registrarAccess = 'registrar';
    private $access;

    private $domainName;
    private $pddToken;
    private $oAuthToken;

    private $service = 'dns';

    private function send($action, array $params = [])
    {
        $method = 'list' === $action ? 'GET' : 'POST';
        $params['domain'] = $this->getDomainName();
        $client = $this->createClient();
        $response = $client->send($this->createRequest($method, $this->getServiceUrl($action), $params));
        $content = $response->getBody()->getContents();
        return json_decode($content, true);
    }

    private function createRequest($method, $uri, array $params = [])
    {
        if (!empty($params)) {
            $uri .= '?' . http_build_query($params);
        }
        $request = (new Request($method, $uri))->withHeader('PddToken', $this->getPddToken());
        if ($this->getOAuthToken()) {
            $request = $request->withHeader('Authorization', $this->getOAuthToken());
        }
        return $request;
    }

    private function createClient()
    {
        return new Client();
    }

    private function getServiceUrl($action = '')
    {
        return $this->serviceScheme . '://' . $this->serviceDomain . '/' . $this->apiVersion . '/' . $this->access . '/' . $this->service . '/' . $action;
    }
}