<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Application
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * JSON view
 *
 * @category   Anahita
 * @package    Lib_Application
 * @subpackage View 
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibApplicationViewJson extends LibBaseViewJson
{
    /**
     * Content
     * 
     * var string|KException
     */
    public $content;
    
    /**
     * Just return the content
     * 
     * @return string
     */
    public function display()
    {
        $this->output = $this->content;
        
        //if content is an exception then
        //handle               
        if ( $this->content instanceof Exception )
        {
            $errors[] = array(
                'message' => $this->content->getMessage(),
                'code'    => $this->content->getCode()
            );
                        
            if ( $this->content instanceof AnErrorSet ) {
                $errors  = $this->_renderErrorSet($this->content);
            }
            
            //Encode data
            $this->output = json_encode(array(
                'errors'   => $errors
            ));
        }
        
        return $this->output;
    }
    
    /**
     * Render error set
     * 
     * @param AnErrorSet $errors A set of errors to render
     * 
     * @return array
     */
    protected function _renderErrorSet($errors)
    {
        $data = array();
        
        foreach($errors as $error) {
            $data[] = $error->toArray();
        }
        
        return $data;
    }
}