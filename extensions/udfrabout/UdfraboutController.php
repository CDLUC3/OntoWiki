<?php
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
	
	public function privacypolicyAction()
    {
		$this->view->url = $this->_config->staticUrlBase;
    	$this->view->placeholder('main.window.title')->set('Privacy policy - Unified Digital Format Registry (UDFR)');
		OntoWiki_Navigation::disableNavigation();
	}
}
?>