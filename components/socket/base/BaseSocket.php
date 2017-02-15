<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 06.02.2017
 * Time: 21:50
 */

namespace app\components\socket\base;

use Ratchet\ConnectionInterface,
    Ratchet\MessageComponentInterface,
    SplObjectStorage;

class BaseSocket implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "\nnew client";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;

        echo "\nConnection " . $from->resourceId . "\nMessage" . $msg . " to other" . $numRecv ;

        $from -> send("Thanks for a data");

        foreach($this->clients as $client){
            if ($from !== $client){
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "Connection " . $conn->resourceId . " has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "\nAn error has occurred " . $e->getMessage();

        $conn->close();
    }
}