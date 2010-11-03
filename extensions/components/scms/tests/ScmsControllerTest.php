<?php 

require_once '../../../../application/tests/test_base.php';

class ScmsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setup()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function tearDown()
    {
    }
    
    public function testMissingDataPrameterShouldThrowException()
    {
        // $this->setExpectedException('OntoWiki_Controller_Exception');
        $this->request->setMethod('post');
        $this->dispatch('/');
        
        $r = $this->getResponse();
        var_dump($r->getBody());
        
        $this->assertController('index');
        $this->assertAction('news');
    }
}

