
<?php
// UDFR - Abhi - new u1f and u1r action for NOID
/**
 * UDFR noid indetifier creator.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Abhishek Salve <abhishek.salve@ucop.edu>
 * @copyright  
 * @license    
 * @version    
 */
class NoidController extends Zend_Controller_Action
{
	public function u1fAction(){
		exec('noid u1f.mint 1', $retval);
		if ($retval) {
			echo $retval[0];
			return $retval[0];
		} else echo "No result found";
		
		$this->_helper->layout()->disableLayout();
	}
	
	public function u1rAction(){
		exec('noid u1r.mint 1', $retval);
		if ($retval) {
			echo $retval[0];
			return $retval[0];
		} else echo "No result found";
		
		$this->_helper->layout()->disableLayout();
	}
}
?>
	