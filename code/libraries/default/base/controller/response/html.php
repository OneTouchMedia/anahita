<?php

/** 
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller_Response
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2011 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Html Response
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller_Response
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
 class LibBaseControllerResponseHtml extends LibBaseControllerResponseAbstract
 {          
    /**
    * Send the headers. If a request if pure HTML, and the location
    * tag is set the
    *  
    * (non-PHPdoc)
    * @see LibBaseControllerResponseAbstract::sendHeaders()
    */
    public function sendHeaders()
    {
        if ( isset($this->_headers['Location']) 
             && !$this->isRedirect()
             && KRequest::type() != 'AJAX'
                 ) {
            $this->setStatus(KHttpResponse::SEE_OTHER);
        }
        
        return parent::sendHeaders();
    }
    
    /**
     * Calls this method if ajax
     * 
     * @param call if ajax $callback
     * 
     * @return LibBaseControllerResponseAbstract
     */
    public function ifAjax($callback)
    {
        if ( $this->isAjax() ) {
            $callback($this);
        }
        return $this;
    }
    
    /**
     * Easy way to see if a respons is AJAX.
     * 
     * This will later be moved to the request object
     * 
     * @return boolean
     */
    public function isAjax()
    {
        return KRequest::type() == 'AJAX';
    }
 }