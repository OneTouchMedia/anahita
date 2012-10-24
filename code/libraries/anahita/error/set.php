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
 * Error set contains a set of error that has occured
 *
 * @category   Anahita
 * @package    Anahita_Exception
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnErrorSet extends KException implements Iterator
{   
    /**
     * Iteratable set of AnError object
     * 
     * @var Iterator
     */ 
    protected $_errors = array();
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
    public function __construct($errors = null, $code = KHttpResponse::INTERNAL_SERVER_ERROR, Exception $previous = null) 
    {
        if ( $errors instanceof AnError ) {
            $errors = new ArrayObject(array($errors));
        }
                  
        if ( !$errors instanceof Traversable ) {        
            throw new KException('$errors must implements Traversable'); 
        }
       
        foreach($errors as $error) 
        {
            if ( $error instanceof AnError )
                $this->_errors[] = $error;                 
        }
       
        $message = KHttpResponse::getMessage($code);
               
        parent::__construct($message, $code, $previous);               
    }
    
    /**
     * Rewind the Iterator to the first element
     *
     * @return  \KObjectArray
     */
    public function rewind()
    {
        reset($this->_errors);
        return $this;
    }

    /**
     * Checks if current position is valid
     *
     * @return  boolean
     */
    public function valid()
    {
        return !is_null(key($this->_errors));
    }

    /**
     * Return the key of the current element
     *
     * @return  mixed
     */
    public function key()
    {
        return key($this->_errors);
    }

    /**
     * Return the current element
     *
     * @return  mixed
     */
    public function current()
    {        
        return current($this->_errors);
    }

    /**
     * Move forward to next element
     *
     * @return  void
     */
    public function next()
    {
        return next($this->_errors);
    }    
}