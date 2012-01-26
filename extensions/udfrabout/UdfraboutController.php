<?php

/**
 * Copyright © 2012 The Regents of the University of California
 *
 * The Unified Digital Format Registry (UDFR) is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class UdfraboutController extends OntoWiki_Controller_Base
{
	/**
     * Displays the UDFR Terms of use
     */
    public function termsofuseAction()
    {
		$this->view->url = $this->_config->staticUrlBase;
    	$this->view->placeholder('main.window.title')->set('Terms of Use - Unified Digital Format Registry (UDFR)');
		OntoWiki_Navigation::disableNavigation();
	}
	
	/**
     * Displays the UDFR Privacy policy
     */
	public function privacypolicyAction()
    {
		$this->view->url = $this->_config->staticUrlBase;
    	$this->view->placeholder('main.window.title')->set('Privacy policy - Unified Digital Format Registry (UDFR)');
		OntoWiki_Navigation::disableNavigation();
	}
}
?>