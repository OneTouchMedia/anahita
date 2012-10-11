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
 * Abstract html view
 *
 * @category   Anahita
 * @package    Lib_Theme
 * @subpackage View 
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibThemeViewHtml extends LibBaseViewTemplate
{
    /**
     * Template Parameters
     * 
     * KConfig
     */
    protected $_params;
    
    /** 
     * Constructor.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
    public function __construct(KConfig $config)
    {
        parent::__construct($config);
        
        $this->_params  = $config->params;        
               
        $this->getTemplate()->getFilter('alias')
            ->append(array('@render(\''=>'$this->renderHelper(\'render.'))
            ->append(array('base://'=>$this->getBaseUrl().'/'), KTemplateFilter::MODE_WRITE);
    }
        
    /**
    * Initializes the default configuration for the object
    *
    * Called from {@link __construct()} as a first step of object instantiation.
    *
    * @param KConfig $config An optional KConfig object with configuration options.
    *
    * @return void
    */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'params'            => array(),        
            'media_url'         => 'base://media',
            'template_filters'  => array('shorttag','html','alias')
        ));
                
        parent::_initialize($config);
    }
            
    /**
     * Displays the template
     * 
     * @return string
     */
    public function display()
    {
        if ( $this->getLayout() != 'raw' ) {
            $this->output = $this->getTemplate()->loadTemplate($this->getLayout(), array('output'=>$this->content))->render();            
        }
        else {
            $this->output = $this->content;   
        }
        
        return $this->output;
    }
    
    /**
     * Get template parameters
     * 
     * @return KConfig
     */
    public function getParams()
    {
        return $this->_params;
    }  
}