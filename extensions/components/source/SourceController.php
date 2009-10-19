<?php

require_once 'OntoWiki/Controller/Component.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_source
 */
class SourceController extends OntoWiki_Controller_Component
{
    public function editAction()
    {
        $store       = $this->_owApp->erfurt->getStore();
        $resource    = $this->_owApp->selectedResource;
        $translate   = $this->_owApp->translate;
        $allowSaving = false;
        
        // window title
        $title       = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace($resource->getIri());
        $windowTitle = sprintf($translate->_('Source of Statements about %1$s') .' ('. $translate->_('without imported statements') . ')', $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);
	    
        // check for N3 capability
        if (array_key_exists('ttl', $store->getSupportedImportFormats())) {
            $allowSaving = true;
        } else {
            require_once 'OntoWiki/Message.php';
            $this->_owApp->appendMessage(
                new OntoWiki_Message("Store adapter cannot handle TTL. Saving has been disabled.", OntoWiki_Message::WARNING)
            );
        }

        if (!$this->_owApp->selectedModel->isEditable()) {
            $allowSaving = false;
        }

        if ($allowSaving) {
            // toolbar
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save Source', 'id' => 'savesource'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }

        // form
        $this->view->formActionUrl = $this->_config->urlBase . 'model/update';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'savesource';
        $this->view->readonly      = $allowSaving ? '' : 'readonly="readonly"';
        $this->view->graphUri      = (string) $this->_owApp->selectedModel;

        // construct N3
        require_once 'Erfurt/Syntax/RdfSerializer.php';
        $exporter = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat('ttl');
        $source = $exporter->serializeResourceToString(
            (string) $this->_owApp->selectedResource,
            (string) $this->_owApp->selectedModel
        );
	        
        $this->view->source = $source;
        
        require_once 'OntoWiki/Url.php';
        $url = new OntoWiki_Url(array('route' => 'properties'), array());
        $url->setParam('r', (string) $resource, true);
        $this->view->redirectUri = urlencode((string) $url);
    }
    
    /*
    public function saveAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        $store       = $this->_owApp->erfurt->getStore();
        $source      = $this->getParam('source');
        $modelUri    = (string) $this->_owApp->selectedModel;
        $resourceUri = (string) $this->_owApp->selectedResource;
        
        if ($this->_owApp->selectedModel->isEditable()) {
            // delete all statements about resource
            $store->deleteMatchingStatements($modelUri, $resourceUri, null, null);
            
            // save new statements
            require_once 'Erfurt/Syntax/RdfParser.php';
            $store->importRdf($modelUri, $source, 'turtle', Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
        } else {
            require_once 'OntoWiki/Message.php';
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No edit privileges on graph <${modelUri}>.", OntoWiki_Message::ERROR)
            );
        }
        
        require_once 'OntoWiki/Url.php';
        // $url = new OntoWiki_Url(array('controller' => 'source', 'action' => 'edit'), array());
        $url = new OntoWiki_Url(array('route' => 'properties'), array());
        $url->setParam('r', $resourceUri, true);
        $this->_redirect((string) $url);
    }
    */
}