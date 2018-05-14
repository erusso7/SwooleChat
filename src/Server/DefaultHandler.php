<?php

namespace SwooleChat\Server;


class DefaultHandler implements Handler
{
    private const CLIENTS_KEY = 'clients';
    static $instance;
    private $cache;

    private function __construct()
    {
        //TODO: Inject this dependency and extract methods.
        $this->cache = new \Redis();
        if ($this->cache->connect('redis')) {
            echo 'Connected to Redis' . PHP_EOL;
            $this->cache->delete(self::CLIENTS_KEY);
        }
    }

    private function __clone()
    {
    }

    public static function init()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function open(\swoole_websocket_server $server, $request)
    {
        echo 'New Client: ' . $request->fd . PHP_EOL;
        $this->addClient($request->fd);

        $this->broadcast($server, 'welcome', $request->fd, 'Welcome Client_.' . $request->fd);
    }

    public function message(\swoole_websocket_server $server, $frame)
    {
        $msg = json_decode($frame->data, true)['msg'];
        echo 'Broadcasting: ' . $msg . PHP_EOL;
        $this->broadcast($server, 'msg', $frame->fd, $msg);
    }

    public function close(\swoole_websocket_server $server, $fd)
    {
        echo 'Connection ' . $fd . ' closed.' . PHP_EOL;
        $this->deleteClient($fd);
        $this->broadcast($server, 'msg', $fd, 'Client ' . $fd . ' has gone!');
    }

    private function broadcast($server, $type, $sender, $msg)
    {
        $data = [
            'status' => 'success',
            'type' => $type,
            'from' => $sender,
            'data' => $msg
        ];

        foreach ($this->clients() as $cliendId => $active) {
            if ($active) {
                $server->push($cliendId, json_encode($data));
            }
        }
    }

    private function clients(): array
    {
        if ($this->cache->exists(self::CLIENTS_KEY)) {
            return unserialize($this->cache->get(self::CLIENTS_KEY));
        }
        $empty = [];
        $this->cache->set(self::CLIENTS_KEY, serialize($empty));
        return $empty;
    }

    private function addClient($fd): void
    {
        $connections = $this->clients();
        $connections[$fd] = true;
        $this->setClients($connections);
    }

    private function deleteClient($fd): void
    {
        $connections = $this->clients();
        $connections[$fd] = false;
        $this->setClients($connections);
    }

    private function setClients(array $connections)
    {
        $this->cache->set(self::CLIENTS_KEY, serialize($connections));
    }

}