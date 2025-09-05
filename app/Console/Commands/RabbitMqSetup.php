<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqSetup extends Command
{
    protected $signature = 'app:rabbit-mq-setup';

    protected $description = 'Declara em RabbitMQ o exchange e a fila usados pela aplicação';

    public function handle()
    {
        $host = env('RABBITMQ_HOST');
        $port = env('RABBITMQ_PORT');
        $user = env('RABBITMQ_USER');
        $password = env('RABBITMQ_PASSWORD');
        $vhost = env('RABBITMQ_VHOST');

        try {
            $connection = new AMQPStreamConnection(
                $host,
                $port,
                $user,
                $password,
                $vhost,
                false,
                'AMQPLAIN',
                null,
                'en_US',
                3.0,
                3.0,
                null,
                true
            );
            $channel = $connection->channel();

            $exchange = env('RABBITMQ_EXCHANGE_NAME', 'samu360');
            $queue = env('RABBITMQ_QUEUE', 'samu360_notifications');

            $channel->exchange_declare($exchange, 'direct', false, true, false);
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, $exchange);

            $channel->close();
            $connection->close();

            $this->info('RabbitMQ setup concluído com sucesso.');
        } catch (\Exception $e) {
            $this->error('Erro no RabbitMQ setup: ' . $e->getMessage());
        }
    }
}
