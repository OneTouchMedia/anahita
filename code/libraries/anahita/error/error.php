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
     * The error reson 
     * 
     * @var String
     */
    protected $_reason;
    
    /**
     * Message
     * 
     * @var String
     */
    protected $_message;
    
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
       $this->_reason  = $config->reason;
       
       unset($config['message']);
       unset($config['reason']);
       
       $this->_userinfo = $config->toArray();
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
     * Return an array represention of the error
     * 
     * @return array
     */
    public function toArray()
    {
        $data            = $this->_userinfo;
        $data['message'] = $this->getMessage();
        $data['reason']  = $this->getReason();
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