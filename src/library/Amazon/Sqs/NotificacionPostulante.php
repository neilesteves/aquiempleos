<?php

class Amazon_Sqs_NotificacionPostulante extends Amazon_Sqs_SqsClientBase
{
    
    private $queuesName;
    private $queueUrlPostulante;
    
    public function __construct()
    {
        parent::__construct();
        $this->queuesName = $this->configColas->postulacion->notificacion;
        $this->queueUrlPostulante = $this->queueUrl.$this->queuesName;
        
    }
    
    public function setQueueUrl($url)
    {
        $this->queueUrlPostulante = $url;    
    }
    
    public function setQueueName($nombreQueue)
    {
        $this->queuesName = $nombreQueue;
    }
           
    public function addCola($mensaje = '', $withException = false)
    {        
        if (!empty($mensaje)) {
            try {                
                $client = $this->getClient();
                $result = $client->sendMessage(array(
                    'QueueUrl' => $this->queueUrlPostulante,
                    'MessageBody' => base64_encode((string)$mensaje)
                ));
                return $result->get('MessageId');
            } catch (Exception $ex) {
                if ($withException === true) {
                    throw new Exception('Error SQS: '.$ex->getMessage());            
                } else {
                    return false;
                }
            }
        }
        return false;
    }
    
    public function getMensaje($withException = false)
    {
        try {                
            $client = $this->getClient();            
            $resultReceived = $client->receiveMessage(array(
                'QueueUrl' => $this->queueUrlPostulante
            ));            
            $mensaje = ($mensajes = $resultReceived->get('Messages')) ? $mensajes[0] : false;
            return $mensaje;
            
        } catch (Exception $ex) {
            if ($withException === true) {
                throw new Exception('Error SQS: '.$ex->getMessage());            
            } else {
                return false;
            }
        }
    }
    
    
    public function deleteMensaje($receiptHandle)
    {        
        if ($receiptHandle) {
            try {
                $client = $this->getClient();
                $client->deleteMessage(array(
                    'QueueUrl' => $this->queueUrlPostulante,
                    'ReceiptHandle' => $receiptHandle
                ));
                return true;
            } catch (Exception $ex) {
                return false;
            }            
        }        
        return false;
        
    }
        
    
}
