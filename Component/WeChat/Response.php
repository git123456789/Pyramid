<?php

/**
 * @file
 *
 * WeChat Response
 */

namespace Pyramid\Component\WeChat;

use Exception;

class Response {

    /**
     * 数据主体
     */
    public $content;
    
    /**
     * 请求实例
     */
    public $request;
    
    /**
     * 是否密文模式
     */
    public $encrypted;

    /**
     * WeChat实例
     */
    public $wechat;

    /**
     * 析构函数
     *
     * @param mixed   $content
     */
    public function __construct($content = null) {
        $this->setContent($content);
    }
    
    /**
     * 设置响应正文
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    /**
     * 获取响应正文
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * 设置是否加密
     */
    public function setEncrypt($value = false) {
        $this->encrypted = (bool) $value;
        return $this;
    }
    
    /**
     * 发送content
     */
    public function send($callback = null) {
        if ($callback && is_callable($callback)) {
            $callback($this);
        }
        if (is_array($this->content) || is_object($this->content)) {
            $output = Utility::buildXML((array) $this->content);
        } else {
            $output = (string) $this->content;
        }
        if ($this->encrypted) {
            try {
                $prpcrypt = new Prpcrypt($this->wechat->getConfig('aeskey'));
                $output = $prpcrypt->encrypt($output, $this->wechat->getConfig('appid'));
                $output = $this->encrypt($output);
            } catch (Exception $e) {}
        }
        echo $output;

        return $this;
    }

    /**
     * 生成加密后的返回字串
     */
    public function encrypt($string) {
        $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature>"
                 ."<TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
        $timestamp = time();
        $nonce     = substr(md5($timestamp), rand(0,6), rand(6,16));
        $signature = $this->wechat->getSHA1($timestamp, $nonce, $string);

        return sprintf($format, $string, $signature, $timestamp, $nonce);
    }

    /**
     * 魔术方法
     */
    public function __toString() {
        return var_export($this->content, true);
    }
    
    /**
     * 魔术方法
     */
    public function __call($method, $args) {

    }

    /**
     * 自身迭代
     */
    public static function create($content = null) {
        return new static($content);
    }

}