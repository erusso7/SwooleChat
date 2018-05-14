<?php

namespace SwooleChat\Server;

interface Handler
{
    public function open(\swoole_websocket_server $server, $request);
    public function message(\swoole_websocket_server $server, $frame);
    public function close(\swoole_websocket_server $server, $fd);
}