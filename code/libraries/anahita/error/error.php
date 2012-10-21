<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Anahita_Exception
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Error objects allows to detail out a error and can be passed as message inside of 
 * an exception object
 *
 * @category   Anahita
 * @package    Anahita_Exception
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnError extends KException implements KObjectHandlable
{
    /**
     * The error reson 
     * 
     * @var String
     */
    protected $_reason;      
    
    /**
     * Extra information regarding the error
     * 
     * @var array
     */
    protected $_userinfo = array();
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
    public function __construct($message = null, $code = KHttpResponse::INTERNAL_SERVER_ERROR, Exception $previous = null) 
    {
       $config = $message;
       
       if ( !is_array($config) ) {
            $config['message'] = $message;
       }
       
       $config = new KConfig($config);
       
       $config->append(array(
            'code' => $code
       ));
       
       parent::__construct($config->message, $config->code, $previous);
        
       $this->_reason = $config->reason;
       
       unset($config['message']);
       unset($config['code']);
       unset($config['reason']);
       
       $this->_userinfo = $config->toArray();
    }  
    
    /**
     * Return the reason
     * 
     * @return string
     */
    public function getReason()
    {
        return $this->_reason;
    }
    
    /**
     * Return the user info
     * 
     * @return array
     */
    public function getUserInfo()
    {
        return $this->_userinfo;
    }
    
    /**
     * Gets the userinfo data
     * 
     * @param string $key Arbituary key data
     * 
     * @return void
     */
    public function __get($key)
    {    
        $result = null;
        
        if ( isset($this->_userinfo[$key]) ) {
            $result = $this->_userinfo[$key];
        }
        
        return $result;
    }
    
    /**
     * Get a handle for this object     
     * 
     * @return string
     */
    public function getHandle()
    {
        return spl_object_hash( $this );
    }
}