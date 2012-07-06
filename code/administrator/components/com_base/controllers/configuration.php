<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Configuration Controller (Resourceless)
 *
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseControllerConfiguration extends ComBaseControllerView
{        
    /**
     * Method to set a view object attached to the controller
     *
     * @param mixed $view An object that implements KObjectIdentifiable, an object that 
     * implements KIndentifierInterface or valid identifier string
     *                  
     * @throws KDatabaseRowsetException If the identifier is not a view identifier
     * 
     * @return KControllerAbstract
     */
    public function setView($view)
    {
        parent::setView($view);
        
        if( !($this->_view instanceof LibBaseViewAbstract) ) 
        {
            $defaults[] = 'ComBaseView'.ucfirst($this->view).ucfirst($this->_view->name);
            $defaults[] = 'ComBaseView'.ucfirst($this->_view->name);
            
            //allows to select confiugration view
            register_default(array('identifier'=>$this->_view, 'default'=>$defaults));                        
        }
    }    
}