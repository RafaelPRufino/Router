<?php

namespace Punk\Fake\Server\Response;

class ResponseJson extends \Punk\Fake\Server\Response {

    public function __invoke() {        
        http_response_code($this->status);
        echo json_encode($this->body);
    }

}
