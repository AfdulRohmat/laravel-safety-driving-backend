<?php

namespace App\Http\Responses;

class CommonResponse
{
    public $statusCode;
    public $message;
    public $data;
    public $error;

    public function __construct($statusCode, $message, $data = null, $error = null)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
    }

    public function toArray()
    {
        return [
            'statusCode' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
