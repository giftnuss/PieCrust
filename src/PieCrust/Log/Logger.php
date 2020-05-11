<?php

namespace PieCrust\Log;

use Logger as Log4Php;
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    protected $_logger;

    public function __construct($logger = null)
    {
        if($logger === null) {
            $logger = new DefaultLogger();
        }
        $this->_logger = $logger;
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
