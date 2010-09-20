<?php

class ScmsTask
{
    const SCMS_DOCUMENT         = 'http://ns.aksw.org/scms/document';
    const SCMS_ANNOTATE         = 'http://ns.aksw.org/scms/annotate';
    const SCMS_CALLBACKENDPOINT = 'http://ns.aksw.org/scms/callbackEndpoint';
    
    protected $_name;
    protected $_node;
    protected $_callbackEndpoint;
    protected $_annotateProperties = array();
    
    public function __construct($name, $node, $callbackEndpoint, $annotateProperties)
    {
        $_name = (string) $name;
        $_node = (array) $node;
        $_callbackEndpoint = (string) $callbackEndpoint;
        $_annotateProperties = (array) $annotateProperties;
    }
    
    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * Initializes a task with a RDF/PHP index structure describing the request.
     * @return ScmsTaks
     */
    public static function initWithRequestIndex($index)
    {
        $result = $this->_analyzeStatements($index);
        $task = new self(
            $result->name, 
            $result->node, 
            $result->callbackEndpoint, 
            $result->annotateProperties
        );
        
        return $task;
    }
    
    public function name()
    {
        return $this->_name;
    }
    
    public function node()
    {
        return $this->_node;
    }
    
    public function callbackEndpoint()
    {
        return $this->_callbackEndpoint;
    }
    
    public function annotateProperties()
    {
        return $this->_annotateProperties;
    }
    
    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------
    
    protected function _analyzeStatements($index)
    {
        $result = new stdClass();
        
        foreach ($index as $requestUri => $requestData) {
            // add request name
            $result->name = $requestUri;
            
            // add callback URI
            if (isset($requestData[self::SCMS_CALLBACKENDPOINT])) {
                foreach ($requestData[self::SCMS_CALLBACKENDPOINT] as $endpoint) {
                    $result->endpoint = $endpoint;
                }
            }
            
            // add node
            if (isset($requestData[self::SCMS_DOCUMENT])) {
                foreach ($requestData[self::SCMS_DOCUMENT] as $document) {
                    $keys = array_keys($document);
                    if (count($keys) > 0) {
                        $nodeUri = $key[0];
                        
                        $result->node = array(
                            $nodeUri => $nodeStatements[$nodeUri]
                        );
                    }
                }
            }
            
            // add properties to be annotated
            if (isset($requestData[self::SCMS_ANNOTATE])) {
                foreach ($requestData[self::SCMS_ANNOTATE] as $property) {
                    array_push($result->annotateProperties, $property);
                }
            }
        }
    }
}

