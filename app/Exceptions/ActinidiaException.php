<?php

namespace App\Exceptions;

use Exception;

class ActinidiaException extends Exception
{

    private string $errorCode;
    private int $status;
    private array $data;

    public function __construct(
        $message = 'An unknown error occurred.',
        $errorCode = 'KI-E-0000',
        $data = [],
        $status = 400
    )
    {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->status = $status;
        $this->data = $data;
    }

    public function render()
    {
        $error = [
            'code' => $this->errorCode,
            'message' => $this->message,
        ];

        if ($this->data) {
            $error['data'] = $this->data;
        }

        return response($response = ['error' => $error], $this->status);
    }

}
