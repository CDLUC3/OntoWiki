<?php
require_once 'OntoWiki/Plugin.php';
require_once OntoWiki::getInstance()->extensionManager->getExtensionPath().'resourcecreationuri/classes/ResourceUriGenerator.php';

/**
 * Copyright © 2012 The Regents of the University of California
 *
 * The Unified Digital Format Registry (UDFR) is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/**
 * Plugin that tries to make nice uris if new resources are created.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class ResourcecreationuriPlugin extends OntoWiki_Plugin
{
    
    /**
     * @var Statements Array for statements to delete
     */
    private $deleteData     = array();
    
    /**
     * @var Statements Array for statements to insert
     */
    private $insertData     = array();
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $deleteModel    = null;
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $insertModel    = null;
    
    /**
     * Try to generate nice uri if new resource uri is found
     * @param   $event triggered Erfurt_Event
     * @return  null
     */
    public function onUpdateServiceAction($event)
    {
        // set values from event
        $this->insertModel  = $event->insertModel;
        $this->insertData   = $event->insertData;
        $this->deleteModel  = $event->deleteModel;
        $this->deleteData   = $event->deleteData;
        
        $flag = false;
        
        // SPARQL/Update can be DELETE only
        // $insertModel is null in this case
        if ($this->insertModel instanceof Erfurt_Rdf_Model) {
            $subjectArray   = array_keys($this->insertData);
            $subjectUri     = current($subjectArray);
            $pattern        = '/^'
                            // URI Component
                            . addcslashes($this->insertModel->getBaseUri() . $this->_privateConfig->newResourceUri,'./')
                            // MD5 Component
                            . '\/([A-Z]|[0-9]){32,32}'
                            . '/i';

            // $nameParts = $this->loadNamingSchema();
            
            $gen = new ResourceUriGenerator($this->insertModel,$this->_pluginRoot . 'plugin.ini');

            if ( count($event->insertData) == 1 && preg_match($pattern,$subjectUri) ) {
                $newUri = $gen->generateUri($subjectUri, ResourceUriGenerator::FORMAT_RDFPHP, $this->insertData);
				
                $temp   = array();
                foreach ($this->insertData[$subjectUri] as $p => $o) {
                    $temp[$newUri][$p] = $o;
                }
				// UDFR - Abhi - add 1 more triple into newly created instance 
				//				 if model is neither "Ontowiki System Config" and nor "udfr profile"
				$baseUri = $gen->getCurrentModel();
				$owApp = OntoWiki::getInstance();

				if ($baseUri != $owApp->config->ontowiki->model && $baseUri != $owApp->config->profile->model) {
					$len = strlen($newUri)-strlen($baseUri);
					$noid = substr($newUri, strlen($baseUri), $len);
					$noidArray = array();
					$noidArray['type'] = 'literal';
					$noidArray['value'] = $noid;
					$temp[$newUri]['http://www.udfr.org/onto/udfrIdentifier'][0] = $noidArray;					
				}
                $this->insertData = $temp;
                $flag = true;
            } else {
                //do nothing
            }
        }
        
        //writeback on event
        $event->insertModel = $this->insertModel;
        $event->insertData  = $this->insertData;
        $event->deleteModel = $this->deleteModel;
        $event->deleteData  = $this->deleteData;
        
        if ($flag) {
            $event->changes = array(
                'original' => $subjectUri, 
                'changed'  => $newUri, 
            );
        }
    }
}
