<?php
declare(strict_types=1);

namespace EoneoPay\Utils\Exceptions;

class InvalidXmlException extends RuntimeException
{
    /**
     * Get Error code.
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return self::DEFAULT_ERROR_CODE_RUNTIME;
    }

    /**
     * Get Error sub-code.
     *
     * @return int
     */
    public function getErrorSubCode(): int
    {
        return self::DEFAULT_ERROR_SUB_CODE;
    }
}
