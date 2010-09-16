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
    const SCMS_DOCUMENT         = 'http://ns.aksw.org/scms/document';
    const SCMS_ANNOTATE         = 'http://ns.aksw.org/scms/annotate';
    const SCMS_CALLBACKENDPOINT = 'http://ns.aksw.org/scms/callbackEndpoint';
    
    protected $_nodes = array();
    
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
        try {
            $wrapperName = isset($this->_privateConfig->wrapper->name) ? $this->_privateConfig->wrapper->name : 'scms';
            $scmsWrapper = Erfurt_Wrapper_Registry::getInstance()->getWrapperInstance($wrapperName);
        } catch (Erfurt_Wrapper_Exception $e) {
            throw new OntoWiki_Controller_Exception("SCMS wrapper not installed or not active.");
            exit;
        }
        
        /*
         * Reaching here, all preconditions are met
         *
         * TODO:
         * 1. Store node
         * 2. Set annotation properties in wrapper
         * 3. Run wrapper
         * 4. Construct response
         * 5. Send Response to callback endpoint
         */
        
    }
    
    /**
     * Takes tasks from the worker queue and calls nlp box for annotation service.
     */
    public function runAction()
    {
        
    }
    
    // ------------------------------------------------------------------------
    // --- Private methods ----------------------------------------------------
    // ------------------------------------------------------------------------
    
    protected function _getRequestName($index)
    {
    }
    
    protected function _getCallbackUri($index)
    {
    }
    
    protected function _getNode($index)
    {
    }
    
    protected function _addNode($nodeStatements)
    {
        $keys = array_keys($nodeStatements);
        if (count($keys) > 0) {
            $nodeUri = $key[0];
            
            if (!isset($this->_nodes[$nodeUri])) {
                $this->_nodes[$nodeUri] = $nodeStatements[$nodeUri];
            }
        }
    }
    
    protected function _analyzeStatements($index)
    {
        foreach ($index as $requestUri => $requestData) {
            if (isset($requestData[SCMS_DOCUMENT])) {
                foreach ($requestData[SCMS_DOCUMENT] as $document) {
                    $this->_addNode($document);
                }
            }
        }
    }
}
