<?php

namespace App\Lib\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ValidateException extends Exception implements HttpExceptionInterface
{
    const TYPE_EMPTY_FIELD = 'EMPTY_FIELD';

    const TYPE_DUPLICATE_UNIQUE = 'DUPLICATE_UNIQUE';

    const TYPE_COMPARE_EQUAL = 'COMPARE_EQUAL';

    const TYPE_SYSTEM_RECORD = 'SYSTEM_RECORD';

    public function __construct(string $type, $message = "", $code = 0)
    {
        parent::__construct($message, $code, null);
        $this->type = $type;
    }

    /**
     * @var string
     */
    protected $type;

    public function getStatusCode()
    {
        return '200';
    }

    public function getHeaders()
    {
        return [
            'Api-Validate-Type' => $this->type,
        ];
    }
}
