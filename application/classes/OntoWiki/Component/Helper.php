<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_Component
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: Helper.php 4095 2009-08-19 23:00:19Z christian.wuerker $
 */

require_once 'OntoWiki/Application.php';

/**
 * Component helper base class.
 *
 * Component helpers are small objects that are instantiated when a component 
 * is found by the component manager. Component helpers allow for certain tasks 
 * to be executed on every request (even those the component doesn't handle). 
 * Example usages are registering a menu entry or a navigation tab.
 *
 * @category   OntoWiki
 * @package    OntoWiki_Component
 * @license  http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version  $Id: Helper.php 4095 2009-08-19 23:00:19Z christian.wuerker $
 * @author   Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Component_Helper
{
    /**
     * OntoWiki Application config
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     * The component private config
     * @var Zend_Config
     */
    protected $_privateConfig;
    
    /**
     * OntoWiki Application
     * @var OntoWiki_Application
     */
    protected $_owApp;
    
    /**
     * Constructor
     *
     * @param OntoWiki_Component_Manager $componentManager
     */
    public function __construct($componentManager)
    {
        $componentName           = strtolower(str_replace('Helper', '', get_class($this)));
        $this->_owApp            = OntoWiki_Application::getInstance();
        $this->_config           = $this->_owApp->config;
        $this->_componentManager = $componentManager;
        $this->_privateConfig    = $this->_componentManager->getComponentPrivateConfig($componentName);
        
        // custom initialisation
        $this->init();
    }
    
    /**
     * Overwritten in subclasses
     */
    public function init()
    {
    }
}
