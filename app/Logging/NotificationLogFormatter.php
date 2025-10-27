<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Illuminate\Log\Logger as IlluminateLogger;

class NotificationLogFormatter
{
    /**
     * Personnaliser le logger pour les notifications
     */
    public function __invoke($logger)
    {
        // Support both Illuminate\Log\Logger and Monolog\Logger
        $monologLogger = $logger instanceof IlluminateLogger
            ? $logger->getLogger()
            : $logger;

        foreach ($monologLogger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: 🔔 %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
                true
            ));
        }
    }
}
