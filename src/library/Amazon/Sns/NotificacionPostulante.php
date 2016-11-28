<?php

class Amazon_Sns_NotificacionPostulante extends Amazon_Sns_SnsClientBase
{
    
    protected $_max_bytes_messages;
    
    public function __construct()
    {
        parent::__construct();
        $this->_max_bytes_messages = 262144;
        
    }
   
    public function sendNotification($subject = '', $mensaje = '', $arn = '', $withException = false)
    {        
        if (!empty($mensaje) && !empty($arn)) {
            
            if (strlen($mensaje) > $this->_max_bytes_messages) {
                return false;
            }
            
            try {                
                $client = $this->getClient();
                $result = $client->publish(array(
                    'TargetArn' => $arn,
                    'Subject' => $subject,
                    'Message' => base64_encode((string)$mensaje),
                    'MessageAttributes' => array(
                        'AWS.SNS.MOBILE.MPNS.Type' => array(
                            'DataType' => 'String'
                        )
                    )
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
    
        
}
