
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
	
	public function testAction() {
		$fp = fsockopen("udfr-dev.cdlib.org", 8089, $errno, $errstr, 30);

		if (!$fp) {
			echo "$errstr ($errno)<br />\n";
		} else {
			$out = "GET http://udfr-dev.cdlib.org/noid/u1r HTTP/1.0\r\n";
			$out .= "Host: udfr-dev.cdlib.org\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);
			//echo fgets($fp, 128);
			$noid = array();
			while (!feof($fp)) {
				$noid = fgets($fp, 128); 				
			}
			fclose($fp);
		}
		$noid = trim($noid); 
		echo $noid; 
		$this->_helper->layout()->disableLayout();
	}
}
?>
	