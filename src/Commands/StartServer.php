<?php

namespace SwooleChat\Commands;

use SwooleChat\Server\Handler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartServer extends Command
{
    private $handler;

    public function __construct(Handler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    protected function configure()
    {
        $this->setName('websocket:start')
            ->setDescription('Start the WebSockets server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO: Add arguments to parametrize this.
        $server = new \swoole_websocket_server("0.0.0.0", 9501);

        $server->on('open', function (\swoole_websocket_server $server, $request) {
            $this->handler->open($server, $request);
        });

        $server->on('message', function (\swoole_websocket_server $server, $frame) {
            $this->handler->message($server, $frame);
        });

        $server->on('close', function (\swoole_websocket_server $server, $fd) {
            $this->handler->close($server, $fd);
        });

        $server->start();
    }
}