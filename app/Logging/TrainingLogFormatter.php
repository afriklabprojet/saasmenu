<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class TrainingLogFormatter
{
    /**
     * Personnaliser le logger pour la formation
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: 🎓 %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
                true
            ));
        }
    }
}
