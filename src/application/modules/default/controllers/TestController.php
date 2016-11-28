<?php

class Default_TestController extends App_Controller_Action
{
    public function test2Action()
    {
        // @codingStandardsIgnoreStart
        $userName3 = $this->_getParam('nombre', ''); 
        Zend_Debug::dump($userName3);
        exit();
        // @codingStandardsIgnoreEnd        
    }

    public function indexAction()
    {
        $rubros = array(
            'ING' => 'Ingeniería',
            'DES' => 'Diseño',
            'MKT' => 'Marketing'
        );
        $baseForm = new Application_Form_Trabajo();
        $baseForm->getElement('rubro')->addMultiOptions($rubros);
        
        $manager = new App_Form_Manager($baseForm, 'manager');
        $forms = array($manager->getForm(0), $manager->getForm(1));
        
        if ($this->getRequest()->isPost()) {
            $manager->isValid($_POST);
            $forms = $manager->getForms();
            foreach ($forms as $form) {
                print_r($form->getValues());
            }
        }
        
        $this->view->assign('manager', $manager);
        $this->view->assign('forms', $forms);
    }
    
    public function testAction()
    {
        $post = array(
            'form_1_nombre_completo' => 'Juan Odicio',
            'form_1_edad' => '26',
            'form_1_tags' => array('PHP', 'Python'),
        
            'form_2_nombre_completo' => 'Victor Celi',
            'form_2_edad' => '30',
            'form_2_tags' => array('Linux', 'Python'),
        
            'form_NEW_nombre_completo' => 'Magno Alarcón',
            'form_NEW_edad' => '30',
            'form_NEW_tags' => array('Drupal', 'PHP', 'Python')
        );
        
        $form = new Zend_Form();
        $manager = new App_Form_Manager($form, 'form');
        
        $newData = $manager->parse($post);
        print_r($newData);
        
        die('end');
    }

    public function uploadAction()
    {
        $form = new Application_Form_Paso2();
        if ($this->getRequest()->isPost()) {
            print_r($form->getValues());
        }
        $this->view->form = $form;
    }
    
    public function init()
    {
        parent::init();
        Zend_Layout::getMvcInstance()->setLayout('simple');
    }
}
