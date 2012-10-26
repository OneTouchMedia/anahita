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
 * Error objects allows to detail out a error. It used with conjuction with
 * AnErrorSet.
 *
 * @category   Anahita
 * @package    Anahita_Exception
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnError extends KObject implements KObjectHandlable
{
    /**
     * Generic error codes
     */
    const INVALID_FORMAT = 'InvalidFormat';
    const INVALID_LENGTH = 'InvalidLength';
    const NOT_UNIQUE     = 'NotUnique';    
    const MISSING_VALUE  = 'MissingValue';
    const OUT_OF_SCOPE   = 'OutOfScope';
    
    /**
     * The error type 
     * 
     * @var String
     */
    protected $_code;
    
    /**
     * Message
     * 
     * @var String
     */
    protected $_message;
    
    /**
     * A data key
     * 
     * @string 
     */
    protected $_data;
    
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
    public function __construct($config = array()) 
    {
       $config = new KConfig($config);
       
       $this->_message = $config->message; 
       $this->_code    = $config->code;
       $this->_data    = $config->data;
       
       unset($config['message']);
       unset($config['code']);
       unset($config['data']);
       
       $this->_userinfo = $config->toArray();
    }
    
    /**
     * Return the data
     * 
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Return the message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Return the reason
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
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
     * Return an array represention of the error
     * 
     * @return array
     */
    public function toArray()
    {
        $data            = $this->_userinfo;
        $data['message'] = $this->getMessage();
        $data['code']    = $this->getCode();
        $data['data']    = $this->getData();
        $data = array_reverse($data);
        return $data;
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
}