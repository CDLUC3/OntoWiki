<?php

/**
 * Copyright © 2012 The Regents of the University of California
 *
 * The Unified Digital Format Registry (UDFR) is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * OntoWiki module â€“ application
 *
 * Provides the OntoWiki application menu and a search field
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_application
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ApplicationModule extends OntoWiki_Module
{   
    public function init(){
        /*$this->view->headScript()->appendScript('
        $(document).ready(function(){
            $("#applicationsearch input").keyup(function(e) {
                if(e.keyCode == 13) {
                    alert($(this).val());
                }
            });
        });
        ');*/
    }

    /**
     * Returns the title of the module
     *
     * @return string
     */
    public function getTitle()
    {
        $title = 'OntoWiki';
        
        if (!($this->_owApp->user instanceof Erfurt_Auth_Identity)) {
            return $title;
        }
                
        if ($this->_owApp->user->isOpenId() || $this->_owApp->user->isWebId()) {
            if ($this->_owApp->user->getLabel() !== '') {
                $userName = $this->_owApp->user->getLabel();
                $userName = OntoWiki_Utils::shorten($userName, 25);
            } else {
                $userName = OntoWiki_Utils::getUriLocalPart($this->_owApp->user->getUri());
                $userName = OntoWiki_Utils::shorten($userName, 25);
            }
        } else {
            if ($this->_owApp->user->getUsername() !== '') {
                $userName = $this->_owApp->user->getUsername();
                $userName = OntoWiki_Utils::shorten($userName, 25);
            } else {
                $userName = OntoWiki_Utils::getUriLocalPart($this->_owApp->user->getUri());
                $userName = OntoWiki_Utils::shorten($userName, 25);
            }
        }
        
        if (isset($userName) && $userName !== 'Anonymous') {
            $title .= ' (' . $userName . ')';
        }
        
        return $title;
    }

    /**
     * Maybe we should disable the app module in some case?
     *
     * @return string
     */
    public function shouldShow()
    {
        if ( ($this->_privateConfig->hideForAnonymousOnNoModels) &&
                 ($this->_owApp->user->isAnonymousUser()) ) {
            // show only if there are models (visible or hidden)
            if ($this->_store->getAvailableModels(true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu()
    {
        return OntoWiki_Menu_Registry::getInstance()->getMenu('application');
    }
    
    /**
     * Returns the content for the model list.
     */
    public function getContents()
    {
    	$this->view->url  = $this->_config->staticUrlBase; // for small logo of udfr
        $data = array(
            'actionUrl'        => $this->_config->urlBase . 'application/search/',
            'modelSelected'    => isset($this->_owApp->selectedModel), 
            'searchtextinput' => $this->_request->getParam('searchtext-input')
        );
        
        /* UDFR - Abhi 
         * Hide Virtuoso logo 
         * if (null !== ($logo = $this->_owApp->erfurt->getStore()->getLogoUri())) {
            $data['logo']     = $logo;
            $data['logo_alt'] = 'Store Logo';
        }*/
        
        if ($this->_owApp->selectedModel) {
            $data['showSearch'] = true;
        } else {
            $data['showSearch'] = false;
        }
        
        $content = $this->render('application', $data);
        
        return $content;
    }
    
    public function allowCaching()
    {
        // no caching
        return false;
    }
}


