<?php

class App_View_Helper_ModalBoxes extends Zend_View_Helper_Abstract
{

    public function ModalBoxes($layout)
    {
        $box  = '';
        $auth = Zend_Auth::getInstance()->getIdentity();
        $box .= $this->view->partial('boxes/all/_msg-publicar.phtml');
        $box .= $this->view->partial('boxes/all/_msg-soyEmpresa.phtml');
        $box .= $this->view->partial('boxes/all/_preload.phtml');
        $box .= $this->view->partial('boxes/all/_box_paises.phml');

        /*
         * --------------------------------------------------------------------
         * Modulos de Postulante y Empresa
         * --------------------------------------------------------------------
         */
       
        if (!isset($auth['usuario'])) {
            if (isset($layout->loginFormNew) && $layout->loginFormNew) {
                $box .= $this->view->partial('boxes/all/_box_login.phtml',
                    array(
                    'loginFormNew' => $layout->loginFormNew,
                    'layout' => $layout
                ));
            }

            if (MODULE == "postulante") {
                if (isset($layout->formRegistroRapido) && $layout->formRegistroRapido) {
                    $box .= $this->view->partial('boxes/all/_box_register.phtml',
                        array(
                        'formRegistroRapido' => $layout->formRegistroRapido,
                        'layout' => $layout
                    ));
                }
            }
        }

        /*
         * --------------------------------------------------------------------
         * Modulos de Postulante
         * --------------------------------------------------------------------
         */

        if (isset($layout->formCuestionario) && $layout->formCuestionario) {
            // var_dump($layout->formCuestionario);Exit;
            $box .= $this->view->partial('boxes/postulante/_new_box_answer_questions.phtml',
                array(
                'formCuestionario' => $layout->formCuestionario,
                'empresa' => $layout->empresa
            ));
        }

        if (isset($layout->modalDespostular) && $layout->modalDespostular) {
            $box .= $this->view->partial('boxes/postulante/_new_box_despostular.phtml');
        }

        if (isset($layout->layoutPostulante) && $layout->layoutPostulante) {
            $box .= $this->view->partial('boxes/postulante/_new_box_information_modal.phtml',
                array(
                'information' => $layout->information
            ));
        }

        if (MODULE == 'postulante' && CONTROLLER == 'perfil-destacado' && ACTION
            == 'paso2') {
            //  $box .= $this->view->partial('boxes/postulante/_new_box_information_pe.phtml');
        }

        if (isset($layout->modalInfoUpdatePerfil) && $layout->modalInfoUpdatePerfil) {
            $box .= $this->view->partial('boxes/postulante/_new_box_not_enough_information.phtml',
                array(
                'newCompleteRecord' => $layout->newCompleteRecord,
                'layout' => $layout
            ));
        }

        if (isset($layout->newrecuperarClaveForm) && $layout->newrecuperarClaveForm) {
            $box .= $this->view->partial('boxes/postulante/_new_box_recuperaclave.phtml',
                array(
                'newrecuperarClaveForm' => $layout->newrecuperarClaveForm,
                'return' => '/auth/new-recuperar-clave/'
            ));
        }

        if (isset($layout->compartirPorMail) && $layout->compartirPorMail) {
            $box .= $this->view->partial('boxes/postulante/_new_box_share_job.phtml',
                array(
                'compartirAviso' => $layout->compartirPorMail
            ));
        }

        /*
         * --------------------------------------------------------------------
         * Modulos de Empresa
         * --------------------------------------------------------------------
         */

        if (MODULE === 'empresa' && CONTROLLER == 'home' && ACTION == 'index') {
            $box .= $this->view->partial('boxes/empresa/new_box_contact_modal.phtml');
        }


        return $box;
    }
}
