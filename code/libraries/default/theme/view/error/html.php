<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Themes
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Error View
 *
 * @category   Anahita
 * @package    Lib_Theme
 * @subpackage View 
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibThemeViewErrorHtml extends LibThemeViewHtml
{
    /**
     * The error to display
     * 
     * @var KException
     */
    protected $_error;
    
    /**
     * Displays the error page
     * 
     * @return string
     */
    public function display()
    {        
        if ( !$this->_error ) {
            throw new KViewException('Error must be set');
        }
        
        $layout  = $this->_error->getCode();
        
        if ( !$this->getTemplate()->findPath('errors/'.$layout.'.php') ) {
            $layout = 'default';
        }
        
        $output = $this->getTemplate()->loadTemplate('errors/'.$layout, array('error'=>$this->_error))->render();
        
        if ( JDEBUG ) {
            $output .= $this->_renderBacktrace();
            //$output .= '<pre>'.$this->document->renderBacktrace().'</pre>';    
        }
        
        //set the content and set the layout to error
        $this->content($output)->setLayout('error');        
        
        $this->setLayout('error');
        
        return parent::display();
    }
    
    /**
     * Set the error to display
     * 
     * @return void
     */
    public function setError(Exception $error)
    {
        $this->_error = $error;   
    }
    
    /**
     * Render backtrace
     * 
     * @return string
     */
    protected function _renderBacktrace()
    {
        return '<pre>d</pre>';
    }
}