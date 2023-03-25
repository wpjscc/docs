<?php

namespace Wpjscc\Docs\Classes;

class ChatgptTranslate
{
    public $token;
    public $proxy;


    public function __construct($token, $proxy = null)
    {
        $this->token = $token;
        $this->proxy = $proxy;
    }

    public function getTranslateContent($content)
    {
        try {
            $result = \React\Async\await($this->translate($content));
            // promise successfully fulfilled with $result
            echo 'Result: ' . $result;
        } catch (\Throwable $e) {
            // promise rejected with $e
            echo 'Error: ' . $e->getMessage();
        }
        return $result;
    }

    function translate($text) {

        $client = null;
        if ($this->proxy) {
            $proxy = new \Clue\React\HttpProxy\ProxyConnector($this->proxy);
            $connector = new \React\Socket\Connector(array(
                'tcp' => $proxy,
                'dns' => false
            ));
            $client = new \React\Http\Browser($connector);
        }
        $deferred = new \React\Promise\Deferred();
        $eof = <<<EOF
            Translate to Chinese. Use this format:

            English: {English text as JSON quoted string}
            Chinese: {中文翻译，同时引用}
            
            English: "$text"
            
            Chinese:

        EOF;
        $es = new \Clue\React\EventSource\EventSource([
                "POST",
                'https://api.openai.com/v1/chat/completions',
                [
                    'Authorization' => 'Bearer '.$this->token,
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                json_encode([
                    'model' => 'gpt-3.5-turbo-0301',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $eof,
                        ],
                    ],
                    'stream' => true
                ])
            ], 
            $client
        );
        
        
        $es->on('open', function () {
            echo 'open';
        });
        $replay = '';
        $es->on('message', function (\Clue\React\EventSource\MessageEvent $message) use (&$replay) {
            $json = json_decode($message->data, true);
            if ($json) {
                $content =  $json['choices'][0]['delta']['content'] ?? '';
                $replay .= $content;
                echo $content."\n";
            } else {
                echo $message->data;
            }
        });
        
        
        $es->on('error', function ($e) use ($es, $deferred, &$replay) {
            $es->readyState = \Clue\React\EventSource\EventSource::CLOSED;
            $deferred->resolve(trim($replay, '"'));
            echo $e->getMessage();
        });
    
        return $deferred->promise();
    }
}