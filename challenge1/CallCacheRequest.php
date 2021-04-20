<?php

require './global.php';
require './request.php';

class CallCacheRequest {

    private $redisObj;
    private $response;
    private $method;
    
    function __construct($typeRequest) {
        $this->response = null;
        $this->method = strtolower($typeRequest);

        // You need to install Redis Library first
        $this->redisObj = new Redis();
        $this->redisObj->connect(REDIS_SERVER, REDIS_PORT);
        $this->redisObj->auth(REDIS_PASSWORD);
    }

    private function isKeyOnRedis($key) {
        $status = true;
        if (!$this->redisObj->get($key)) {
            $status = false;
        }
        return $status;
    }

    private function setRedisKey($key, $value) {
        $this->redisObj->set($key, $value);
        $this->redisObj->expire($key, 10);
    }

    public function call(string $url, array $parameters = null, array $data = null) {
        
        if (!$this->isKeyOnRedis($url)) {
            if ($this->method !== "get") {
                $this->response = SimpleJsonRequest::{$this->method}($url, $parameters, $data);
            } else {
                $this->response = SimpleJsonRequest::{$this->method}($url, $parameters);
            }
            $this->setRedisKey($url, $this->response);
        } else {
            $this->response = $this->redisObj->get($url);
        }
    
        return $this->response;
    }

}