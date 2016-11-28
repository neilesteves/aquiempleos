<?php


/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */
class App_Service_UpdateTypeReferred
{
    /*
     * @var Application_Modelo_Referenciado
     */

    private $_referenceModel = null;

    public function __construct()
    {
        $this->_referenceModel = new Application_Model_Referenciado;
    }

    public function update($email, $adId)
    {
        if (App_Service_Validate_User::isRegister($email)) {
            $this->_referenceModel->registrado($email, $adId);
        }
    }

    /**
     * @todo refactorizar el codigo
     * @param type $adId
     */
    public function updateAll($adId)
    {
        $postulacion = new Application_Model_Postulacion();
        $referrals = $this->_referenceModel->obtenerReferidos(
            $adId, array('id', 'email'));
        foreach ($referrals as $referred) {
            $this->update($referred['email'], $adId);
        }

        $referenciados = $this->_referenceModel->obtenerReferenciados(
            $adId, array('id', 'email')
        );

        foreach ($referenciados as $referenciado) {
            if ($postulacion->getPostulacionByIdAvisoandEmail($adId,
                    $referenciado['email'])) {
                $this->_referenceModel->postulo($referenciado['email'], $adId);
            }
        }
    }

}