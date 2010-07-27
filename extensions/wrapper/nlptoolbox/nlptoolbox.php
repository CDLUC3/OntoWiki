<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Connects to a configured NLP toolbox and adds triples for name entities.
 *
 * @category OntoWiki
 * @package Extension
 * @subpackage Wrapper
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class NlptoolboxWrapper extends Erfurt_Wrapper
{
    /**
     * Seconds timeout for HTTP request to NLP toolbox.
     * @var int
     */
    const HTTP_TIMEOUT = 10;
    
    /**
     * Entities extracted
     * @var array
     */
    protected $_entities = array();
    
    /**
     * Text property values for the current resource.
     * @var array
     */
    protected $_values = array();
    
    /**
     * The current resource.
     * @var string
     */
    protected $_uri = null;
    
    // ------------------------------------------------------------------------
    // --- Public methods -----------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * @see Erfurt_Wrapper
     */
    public function getDescription()
    {
        return 'Wrapper to connect to a NLP toolbox and extract named entities out of a given text.';
    }
    
    /**
     * @see Erfurt_Wrapper
     */
    public function getName()
    {
        return 'NLP Toolbox Wrapper';
    }
    
    /**
     * @see Erfurt_Wrapper
     */
    public function isHandled($uri, $graphUri)
    {
        $this->_uri = $uri;
        
        $select = '';
        $where  = array();
        foreach ($this->_getTextProperties() as $key => $propertyUri) {
            $select .= $this->_valueVar($key, ' ?');
            array_push($where, sprintf('<%s> <%s> %s .', $uri, $propertyUri, $this->_valueVar($key, '?')));
        }
        
        $handledQuery = 'SELECT ' . $select . ' WHERE {
            OPTIONAL { ' . implode('} OPTIONAL {', $where) . '}
        }';
        
        $query = Erfurt_Sparql_SimpleQuery::initWithString($handledQuery);
        $query->setFrom(array($graphUri));
        $store = Erfurt_App::getInstance()->getStore();
        
        $flag = false;
        if ($result = $store->sparqlQuery($query)) {
            foreach ($result as $row) {
                foreach ($this->_getTextProperties() as $key => $uri) {
                    if (!empty($row[$this->_valueVar($key)])) {
                        $this->_setValue($uri, $row[$this->_valueVar($key)]);
                        $flag = true;
                    }
                }
            }
        }
        
        return $flag;
    }
    
    /**
     * @see Erfurt_Wrapper
     */
    public function isAvailable($uri, $graphUri)
    {
        $flag = false;
        $this->isHandled($uri, $graphUri);
        if ($uri === $this->_uri) {
            foreach ($this->_getTextProperties() as $key => $propertyUri) {
                if ($this->_value($key, $propertyUri)) {
                    try {
                        $currentResult    = $this->_getNlpResult($this->_value($key, $propertyUri));
                        $currentResources = json_decode($currentResult, true);
                    } catch (Exception $e) {
                        // TODO: handle error
                        return false;
                    }
                                        
                    $this->_addEntities($currentResources);
                    
                    // flag will be true if at least one entity is returned
                    $flag = !empty($currentResources) | $flag;
                }
            }
            
            return $flag;
        }
        
        return false;
    }
    
    public function run($uri, $graphUri)
    {
        if ($uri === $this->_uri) {
            $response = array(
                'status_codes' => array() 
            );
            
            if (!empty($this->_entities)) {
                $objects           = array();
                $objectTriples     = array();
                $objectTripleCount = 0;
                
                foreach ($this->_entities as $entityUri => $entitySpec) {
                    array_push($objects, array('type' => 'uri', 'value' => $entityUri));
                    
                    // triples about the objects (e.g. label)
                    if (isset($entitySpec['label'])) {
                        $subjectLabel = $entitySpec['label'];
                        // TODO: lang, datatype
                        $labelTriple  = array('type' => 'literal', 'value' => $subjectLabel);
                    }
                    
                    $objectTriples[$entityUri] = array(
                        $this->_config->properties->subjectLabel => array($labelTriple)
                    );
                    $objectTripleCount++;
                }
                
                try {
                    $store = Erfurt_App::getInstance()->getStore();
                    $store->addMultipleStatements($graphUri, $objectTriples);
                } catch (Exception $e) {
                    // on error, report 0 triples added
                    $objectTripleCount = 0;
                }
                
                $triples = array(
                    $uri => array(
                        $this->_config->properties->subject => $objects
                    )
                );
                
                $tripleCount = count($this->_entities);
                
                $response['add']          = $triples;
                $response['status_codes'] = array(Erfurt_Wrapper::RESULT_HAS_ADD);
                $response['added_count']  = $objectTripleCount;
                $response['status_desc']  = sprintf('%d named entities were extracted', $tripleCount);
            }
            
            return $response;
        }
        
        return false;
    }
    
    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------
    
    /*
     * examle:  [{"uri":"http://plazes.com/plazes/106049_wasserpark","label":"Wasserpark - Plazes","score":1.0,"input":"Wasserpark"}] 
     */
    protected function _addEntities($nlpJsonResponse)
    {
        foreach ($nlpJsonResponse as $entityResponse) {
            if (isset($entityResponse['uri'])) {
                $entityUri = $entityResponse['uri'];
            }
            
            $this->_entities[$entityUri] = $entityResponse;
        }
    }
    
    protected function _getTextProperties()
    {
        if (isset($this->_config->properties) && isset($this->_config->properties->fulltext)) {
            if ($this->_config->properties->fulltext instanceof Zend_Config) {
                return $this->_config->properties->fulltext->toArray();
            } else {
                return array($this->_config->properties->fulltext);
            }            
        }
        
        return array();
    }
    
    protected function _getNlpResult($text)
    {
        $serviceUri = (string)$this->_config->service->uri;
        $client = new Zend_Http_Client($serviceUri, array('timeout' => self::HTTP_TIMEOUT));
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders('Accept', 'application/json');
        $client->setParameterGet(array(
            $this->_config->service->parameter->text => $text
        ));
        $response = $client->request();
        
        if (!$response->isSuccessful()) {
            $msg = sprintf('NLP Toolbox Wrapper: %s (%s)', $response->getMessage(), $response->getStatus());
            throw new Erfurt_Wrapper_Exception($msg);
        }
        
        return $response->getBody();
    }
    
    protected function _setValue($propertyUri, $value)
    {
        if (!isset($this->_values[$propertyUri])) {
            $this->_values[$propertyUri] = array();
        }
        
        $this->_values[$propertyUri][] = $value;
    }
    
    protected function _value($key, $propertyUri)
    {
        if (isset($this->_values[$propertyUri][$key])) {
            return $this->_values[$propertyUri][$key];
        }
        
        return null;
    }
    
    protected function _valueVar($key, $prefix = '')
    {
        return sprintf('%s%s_VALUE', (string)$prefix, (string)$key);
    }
}