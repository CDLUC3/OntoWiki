<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Main controller for SCMS component.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @author Norman Heino <norman.heino@gmail.com>
 */
class ScmsController extends OntoWiki_Controller_Component
{
    protected $_endpoint = array();
    protected $_nodes = array();
    protected $_queue = null;
    
    /**
     * Expects an annotation request described using the scms vocabulary.
     * It stores the node and creates an scms task which is then added to 
     * a worker queue.
     */
    public function requestAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        /*
         * Check parameters
         */
        if (!isset($this->_request->data)) {
            throw new OntoWiki_Controller_Exception("Parameter 'data' required.");
            exit;
        }
        
        /*
         * Parse Turtle statements
         */
        $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('nt');
        try {
            $statements = $parser->parse($this->_request->data, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
        } catch (Erfurt_Syntax_RdfParserException $e) {
            throw new OntoWiki_Controller_Exception("Unable to parse data.");
            exit;
        }
        
        /*
         * Instantiate toolbox wrapper
         */
        if (!$this->_wrapper()) {
            throw new OntoWiki_Controller_Exception("SCMS wrapper not installed or not active.");
            exit;
        }
        
        try {
            require_once 'ScmsTask.php';
            $task = new ScsmTask($statement);
        } catch (Exception $e) {
            
        }
        
        array_push($this->_queue(), $task);
    }
    
    /**
     * Takes tasks from the worker queue and calls nlp box for annotation service.
     */
    public function runAction()
    {
        foreach ($this->_queue() as $task) {
            $wrapper = $this->_wrapper();
            $wrapper->setProperties($task->annotateProperties());
            $node    = $task->node;
            $nodeUri = array_keys($node)[0];
            
            if ($wrapper->isHandled($nodeUri, $this->_graph())) {
                $wrapperResult     = $wrapper->run($nodeUri, $this->_graph(), true);
                $wrapperStatements = $wrapperResult['add'];
                
                require_once 'libraries/arc/ARC2.php';
                $serializer     = ARC2::getTurtleSerializer();
                $turtleResponse = $serializer->getSerializedIndex($wrapperStatements);
                
                $client = new Zend_Http_Client();
                $client->setUri($task->callbackEndpoint)
                       ->setPost(Zend_Http_Client::POST)
                       ->setPrameterPost(
                           $this->_privateConfig->parameter->name => $turtleResponse
                       );
                $client->request();
            }
        }
    }
    
    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------
    
    protected function _graph()
    {
        if (isset($this->_privateConfig->graph->uri)) {
            return $this->_privateConfig->graph->uri;
        }
        
        return OntoWiki::getInstance()->selectedModel;
    }
    
    protected function _queue()
    {
        if (null === $this->_queue) {
            if (!isset(OntoWiki::getInstance()->session->scmsTasks)) {
                OntoWiki::getInstance()->session->scmsTasks = array();
            }
            $this->_queue = OntoWiki::getInstance()->session->scmsTasks;
        }
        
        return $this->_queue;
    }
    
    protected function _wrapper()
    {
        try {
            $wrapperName = isset($this->_privateConfig->wrapper->name) ? $this->_privateConfig->wrapper->name : 'scms';
            $scmsWrapper = Erfurt_Wrapper_Registry::getInstance()->getWrapperInstance($wrapperName);
        } catch (Erfurt_Wrapper_Exception $e) {
            return null;
        }
        
        return $wrapperName;
    }
}
