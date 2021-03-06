<?php

namespace Kijho\ChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chat Settings
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 31/03/2016
 * @ORM\Table(name="chat_settings")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ChatSettings {


    /**
     * @ORM\Id
     * @ORM\Column(name="cset_id", type="integer") 
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Direccion en donde seran enviados los correos con los mensajes de
     * los clientes cuando no hay administradores online
     * @ORM\Column(name="cset_email_offline", type="string", nullable=true)
     */
    protected $emailOfflineMessages;
    
    /**
     * Mensajes automatico que se le envia a un cliente que acaba de enviarle un mensaje al administrador
     * @ORM\Column(name="cset_automatic_message", type="text", nullable=true)
     */
    protected $automaticMessage;
    
    /**
     * Boolean que permite establecer si el sistema debe desplegar los mensajes
     * por defecto a la hora de responder a un cliente
     * @ORM\Column(name="cset_enable_custom_responses", type="boolean", nullable=true)
     */
    protected $enableCustomResponses;
    
    /**
     * Mensajes por defecto creados por el administrador
     * @ORM\Column(name="cset_custom_responses", type="text", nullable=true)
     */
    protected $customMessages;
    
    function getId() {
        return $this->id;
    }

    function getEmailOfflineMessages() {
        return $this->emailOfflineMessages;
    }

    function setEmailOfflineMessages($emailOfflineMessages) {
        $this->emailOfflineMessages = $emailOfflineMessages;
    }
    
    function getEnableCustomResponses() {
        return $this->enableCustomResponses;
    }

    function setEnableCustomResponses($enableCustomResponses) {
        $this->enableCustomResponses = $enableCustomResponses;
    }
    
    function getCustomMessages() {
        return $this->customMessages;
    }

    function setCustomMessages($customMessages) {
        $this->customMessages = $customMessages;
    }
    
    function getAutomaticMessage() {
        return $this->automaticMessage;
    }

    function setAutomaticMessage($automaticMessage) {
        $this->automaticMessage = $automaticMessage;
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getEnableCustomResponses() === null) {
            $this->setEnableCustomResponses(FALSE);
        }
    }
}
