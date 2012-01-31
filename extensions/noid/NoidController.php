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
 * UDFR noid indetifier creator.
 * 
 * @package     application
 * @subpackage  mvc
 * @author      Abhishek Salve <abhishek.salve@ucop.edu>
 * @discription new u1f and u1r action for NOID 
 */
class NoidController extends Zend_Controller_Action
{
	public function u1fAction(){
		exec('noid u1f.mint 1', $retval);
		if ($retval) {
			$noid = $retval[0];
			$noid = trim($noid); 
			echo $noid;
			//return $retval[0];
		} else echo "No result found";
		
		$this->_helper->layout()->disableLayout();
	}
	
	public function u1rAction(){
		exec('noid u1r.mint 1', $retval);
		if ($retval) {
			$noid = $retval[0];
			$noid = trim($noid); 
			echo $noid;
			//return $retval[0];
		} else echo "No result found";
		
		$this->_helper->layout()->disableLayout();
	}
	
	public function u1fotherAction() {
		$fp = fsockopen("udfr-dev.cdlib.org", 8089, $errno, $errstr, 30);

		if (!$fp) {
			echo "$errstr ($errno)<br />\n";
		} else {
			$out = "GET http://udfr-dev.cdlib.org/noid/u1f HTTP/1.0\r\n";
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
	
	public function u1rotherAction() {
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
	