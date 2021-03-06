<?php

namespace Kijho\ChatBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository {

    /**
     * Permite obtener los nombres de los clientes y la ultima fecha en la que enviarion
     * mensajes a un administrador en especifico, ordenados desde el mensaje 
     * mas reciente al mas antiguo
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2016
     * @param string $adminId identificador del administrador
     * @return type
     */
    /*public function findClientChatNickNames($adminId) {

        $em = $this->getEntityManager();

        $innerQuery = $em->createQuery("SELECT MAX(mes.date)
            FROM ChatBundle:Message mes
            WHERE (mes.destinationId = :adminId
            AND mes.type = :clientAdmin ) 
            OR (mes.senderId = :adminId
            AND mes.type = :adminClient ) 
            GROUP BY mes.senderNickname")
                ->setMaxResults(1)
                ->setParameter('adminId', $adminId)
                ->setParameter('clientAdmin', Message::TYPE_CLIENT_TO_ADMIN)
                ->setParameter('adminClient', Message::TYPE_ADMIN_TO_CLIENT);

        $result = $innerQuery->getArrayResult();


        if (!empty($result)) {

            $dqlQuery = $innerQuery->getDQL();

            $consult = $em->createQuery("
        SELECT m.type, m.senderId, m.senderNickname, m.destinationId, m.destinationNickname, m.date
        FROM ChatBundle:Message m
        WHERE ((m.destinationId = :adminId
        AND m.type = :clientAdmin ) OR (
            m.senderId = :adminId
            AND m.type = :adminClient)
        )
        
        GROUP BY m.senderNickname
        ORDER BY m.date DESC");
            $consult->setParameter('adminId', $adminId);
            $consult->setParameter('clientAdmin', Message::TYPE_CLIENT_TO_ADMIN);
            $consult->setParameter('adminClient', Message::TYPE_ADMIN_TO_CLIENT);
            return $consult->getArrayResult();
        } else {
            return $result;
        }
    }*/
    
    /**
     * Permite obtener los nombres de los clientes y la ultima fecha en la que enviarion
     * mensajes a un administrador en especifico, ordenados desde el mensaje 
     * mas reciente al mas antiguo
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2016
     * @param string $adminId identificador del administrador
     * @return type
     */
    public function findClientChatNickNames($adminId) {
        $em = $this->getEntityManager();

            $consult = $em->createQuery("
        SELECT m.type, m.senderId, m.senderNickname, m.destinationId, m.destinationNickname
        FROM ChatBundle:Message m
        WHERE ((m.destinationId = :adminId
        AND m.type = :clientAdmin ) OR (
            m.senderId = :adminId
            AND m.type = :adminClient)
        )
        GROUP BY m.senderNickname
        ORDER BY m.date DESC");
            $consult->setParameter('adminId', $adminId);
            $consult->setParameter('clientAdmin', Message::TYPE_CLIENT_TO_ADMIN);
            $consult->setParameter('adminClient', Message::TYPE_ADMIN_TO_CLIENT);
            return $consult->getArrayResult();
    }

    /**
     * Permite cargar una conversacion completa entre dos partes
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2016
     * @param string $clientId identificador de uno de los involucrados en la conversacion
     * @param string $adminId identificador del segundo usuario involucrado en la conversacion
     * @return type
     */
    public function findConversationClientAdmin($clientId, $adminId, $steal = null, $allAdmin = null, $startDate = null, $endDate = null) {

        $em = $this->getEntityManager();

        $extraQuery = '';

        if ($startDate) {
            $extraQuery .= ' AND m.date >= :startDate ';
        }
        if ($endDate) {
            $extraQuery .= ' AND m.date <= :endDate ';
        }

        if ($steal !== null) {
            if ($steal === true) {
                $extraQuery .= ' AND m.isStealMessage = TRUE ';
            } elseif ($steal === false) {
                $extraQuery .= ' AND m.isStealMessage = FALSE ';
            }
        }

        if ($allAdmin !== null) {
            if ($allAdmin === true) {
                $extraQuery .= ' AND m.isSendToAllAdmin = TRUE ';
            } elseif ($allAdmin === false) {
                $extraQuery .= ' AND m.isSendToAllAdmin = FALSE ';
            }
        }

        $consult = $em->createQuery("
        SELECT m
        FROM ChatBundle:Message m
        WHERE 
        ((m.senderId = :client AND m.destinationId = :admin)
        OR (m.senderId = :admin AND m.destinationId = :client))
        AND (m.type = :clientToAdmin OR m.type = :adminToClient) " . $extraQuery
                . " ORDER BY m.date ASC");
        $consult->setParameter('client', $clientId);
        $consult->setParameter('admin', $adminId);
        $consult->setParameter('clientToAdmin', Message::TYPE_CLIENT_TO_ADMIN);
        $consult->setParameter('adminToClient', Message::TYPE_ADMIN_TO_CLIENT);

        if ($startDate) {
            $consult->setParameter('startDate', $startDate);
        }
        if ($endDate) {
            $consult->setParameter('endDate', $endDate);
        }
        
        return $consult->getResult();
    }

    /**
     * Permite buscar los mensajes sin leer por el cliente 
     * que los administradores le han enviado
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2016
     * @param type $nickname
     * @param type $userId
     * @return type
     */
    public function findClientUnreadMessages($nickname, $userId) {
        $em = $this->getEntityManager();

        $consult = $em->createQuery("
        SELECT m
        FROM ChatBundle:Message m
        WHERE m.destinationId = :client
        AND m.destinationNickname = :clientNickname
        AND m.readed = :unread
        AND m.type = :adminToClient
        ORDER BY m.date ASC");
        $consult->setParameter('clientNickname', $nickname);
        $consult->setParameter('client', $userId);
        $consult->setParameter('unread', false);
        $consult->setParameter('adminToClient', Message::TYPE_ADMIN_TO_CLIENT);

        return $consult->getResult();
    }

    /**
     * Permite obtener el listado de mensajes que un cliente le ha enviado a un administrador
     * o viceversa desde una fecha determinada
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/04/2016
     * @param type $clientId
     * @param type $startDate
     * @return type
     */
    public function findClientMessagesFromDate($clientId, $startDate, $automaticMessages = null) {
        $em = $this->getEntityManager();

        $extraQuery = '';

        if ($automaticMessages !== null) {
            if ($automaticMessages === true) {
                $extraQuery .= ' AND m.isAutomaticMessage = TRUE ';
            } else {
                $extraQuery .= ' AND m.isAutomaticMessage = FALSE ';
            }
        }

        $consult = $em->createQuery("
        SELECT m
        FROM ChatBundle:Message m
        WHERE 
        ((m.senderId = :client
        AND m.type = :clientToAdmin)
        OR (m.destinationId = :client
        AND m.type = :adminToClient
        ))
        AND m.date >= :startDate
        AND m.isStealMessage = FALSE " . $extraQuery .
                "GROUP BY m.date, m.message, m.senderId
        ORDER BY m.date ASC");
        $consult->setParameter('client', $clientId);
        $consult->setParameter('startDate', $startDate);
        $consult->setParameter('clientToAdmin', Message::TYPE_CLIENT_TO_ADMIN);
        $consult->setParameter('adminToClient', Message::TYPE_ADMIN_TO_CLIENT);

        return $consult->getArrayResult();
    }

}
