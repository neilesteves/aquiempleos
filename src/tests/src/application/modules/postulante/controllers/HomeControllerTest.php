<?php

class Postulante_HomeControllerTest
  extends Zend_Test_PHPUnit_ControllerTestCase
{

    protected $_object;

    protected function setUp()
    {
        if ( $this->getRequest()->getControllerName()=='error' ) {
            
        }
    }

    protected function tearDown()
    {
    }

    public function testInit()
    {
    }

    public function testIndexAction()
    {
        $this->dispatch("/");
        $this->assertController('home');
    }

    /**
     * @todo Implement testQueEsAptitusAction().
     */
    public function testQueEsAptitusAction()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testLoginAction()
    {
    }
    /**
     * @todo Implement testLogoutAction().
     */
    public function testLogoutAction()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
