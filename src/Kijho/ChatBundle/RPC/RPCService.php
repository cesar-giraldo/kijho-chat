<?php

namespace Kijho\ChatBundle\RPC;

use Ratchet\ConnectionInterface;
use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class RPCService implements RpcInterface
{
    /**
     * Adds the params together
     *
     * Note: $conn isnt used here, but contains the connection of the person making this request.
     *
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param array $params
     * @return int
     */
    public function updateConnectionData(ConnectionInterface $connection, WampRequest $request, $params)
    {
        $connection->nickname = 'Guest '.$connection->resourceId;
        
        if (isset($params['nickname']) && !empty($params['nickname'])) {
            $nickName = trim ($params['nickname']);
            $connection->nickname = $nickName;
        }
        
        $connection->userType = 'Client';
        if (isset($params['user_type']) && !empty($params['user_type'])) {
            $userType = trim ($params['user_type']);
            $connection->userType = $userType;
        }
        
        return array("result" => array_sum($params));
    }

    /**
     * Name of RPC, use for pubsub router (see step3)
     * 
     * @return string
     */
    public function getName()
    {
        return 'rpc.service';
    }
}