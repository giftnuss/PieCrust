<?php

namespace PieCrust\Log;

use \Logger as Log4Php;
use Psr\Log\AbstractLogger;

class DefaultLogger extends AbstractLogger
{
    protected $_logger;

    public function __construct()
    {
        $this->_logger = Log4Php::getRootLogger();
    }

    public function warning ($message, array $context = array())
    {
        $this->_logger->warn($message);
    }

     public function log($level, $message, array $context = array())
     {
         $this->_logger->$level($message);
     }

     public function setLevel($level)
     {
         $this->_logger->setLevel($level);
     }
}
