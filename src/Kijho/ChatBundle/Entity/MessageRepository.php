<?php

namespace Kijho\ChatBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository {

    /**
     * Permite obtener los nombres de los clientes y la ultima fecha en la que enviarion
     * mensajes a un administrador en especifico, ordenados desde el mensaje 
     * mas reciente al mas antiguo
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2015
     * @param string $adminId identificador del administrador
     * @return type
     */
    public function findClientChatNickNames($adminId) {

        $em = $this->getEntityManager();
        
        $innerQuery = $em->createQuery("SELECT MAX(mes.date)
            FROM ChatBundle:Message mes
            WHERE mes.destinationId = :adminId
            AND mes.type = :clientAdmin 
            GROUP BY mes.senderNickname")
                ->setMaxResults(1)
                ->getDQL();
        
        $consult = $em->createQuery("
        SELECT m.senderId, m.senderNickname, m.date
        FROM ChatBundle:Message m
        WHERE m.destinationId = :adminId
        AND m.type = :clientAdmin 
        AND m.date IN (" . $innerQuery . ")
        GROUP BY m.senderNickname
        ORDER BY m.date DESC");
        $consult->setParameter('adminId', $adminId);
        $consult->setParameter('clientAdmin', Message::TYPE_CLIENT_TO_ADMIN);

        return $consult->getArrayResult();
    }
    
    /**
     * Permite cargar una conversacion completa entre dos partes
     * @author Cesar Giraldo <cnaranjo@kijho.com> 07/03/2015
     * @param string $clientId identificador de uno de los involucrados en la conversacion
     * @param string $adminId identificador del segundo usuario involucrado en la conversacion
     * @return type
     */
    public function findConversationClientAdmin($clientId, $adminId) {

        $em = $this->getEntityManager();
        
        $consult = $em->createQuery("
        SELECT m
        FROM ChatBundle:Message m
        WHERE 
        (m.senderId = :client AND m.destinationId = :admin)
        OR (m.senderId = :admin AND m.destinationId = :client)
        AND (m.type = :clientToAdmin OR m.type = :adminToClient)
        ORDER BY m.date ASC");
        $consult->setParameter('client', $clientId);
        $consult->setParameter('admin', $adminId);
        $consult->setParameter('clientToAdmin', Message::TYPE_CLIENT_TO_ADMIN);
        $consult->setParameter('adminToClient', Message::TYPE_ADMIN_TO_CLIENT);

        return $consult->getResult();
    }

}