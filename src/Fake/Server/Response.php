<?php

namespace Punk\Fake\Server;

class Response {

    protected $body;
    protected int $status;

    public function __construct($body = 'php://temp', $status = 200, array $headers = []) {
        $this->status = $status;
        $this->body = $body;
    }

    public function __invoke() {
        http_response_code($this->status);
        echo $this->body;
    }

}
