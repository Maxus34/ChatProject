<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 06.02.2017
 * Time: 21:44
 */

namespace app\commands;

use app\components\socket\ChatSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use yii\console\Controller;

class SocketController extends Controller
{
    protected $server;

    public function actionStart(){
        echo "Trying to start server";

        $this->server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatSocket()
                )
            ),
            8000,
            '192.168.33.20'
        );

        $this->server->run();
    }
}