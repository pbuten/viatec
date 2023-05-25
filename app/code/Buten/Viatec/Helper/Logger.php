<?php

namespace Buten\Viatec\Helper;

use Zend_Log;
use Zend_Log_Exception;

class Logger
{
    /**
     * log file
     */
    const LOG_PATH = '/var/log/viatec.log';

    /**
     * @var Zend_Log
     */
    private Zend_Log $_logger;

    /**
     * @param Zend_Log $_logger
     * @throws Zend_Log_Exception
     */
    public function __construct(Zend_Log $_logger)
    {
        $writer        = new \Zend_Log_Writer_Stream(BP . self::LOG_PATH);
        $this->_logger = $_logger;
        $this->_logger->addWriter($writer);
    }

    /**
     * @param string $message
     * @param array $extras
     * @return void
     */
    public function log(string $message, array $extras): void
    {
        $this->_logger->info($message, $extras);
    }
}
