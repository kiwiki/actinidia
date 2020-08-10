<?php

use App\Exceptions\ActinidiaException;

function error(
    $message = 'An unknown error occurred.',
    $errorCode = 'KI-E-0000',
    $data = [],
    $status = 400
) {
    throw new ActinidiaException($message, $errorCode, $data, $status);
}
