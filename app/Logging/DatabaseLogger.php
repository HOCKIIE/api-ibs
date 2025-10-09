<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Illuminate\Support\Facades\DB;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Handler\AbstractProcessingHandler;


class DatabaseLogger extends AbstractProcessingHandler
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('database');

        // push handler
        $logger->pushHandler(new DatabaseLogger());

        // push processor เพื่อเก็บ file + line
        $logger->pushProcessor(new IntrospectionProcessor());

        return $logger;
    }
    public function __construct($level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }
    protected function write(LogRecord $record): void
    {
        DB::table('error_logs')->insert([
            'message' => $record->message,
            'level' => $record->level->value,
            'context' => $record->context ? json_encode($record->context): null,
            'extra' => json_encode($record['context']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
