<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Restful Domain Controller. Performs Rest operation on a domain entity
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibBaseControllerService extends LibBaseControllerResource
{     
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
            'behaviors'     => array(
                'identifiable',
                'validatable',
                'committable',
                'loggable'),
        ));
              
        parent::_initialize($config);
    }
    
    
    /**
     * Add Action
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionAdd(KCommandContext $context)
    {
        $context['status'] = KHttpResponse::CREATED;
        $entity = $this->getRepository()->getEntity()->setData($context['data']);
        $this->setItem($entity);
        return $this->getItem();
    }
    
    /**
     * Edit Action
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionEdit(KCommandContext $context)
    {
        $context['status'] = KHttpResponse::RESET_CONTENT;
        return $this->getItem()->setData($context['data']);
    }
    
    /**
     * Return the state get item
     * 
     * @return mixed
     */
    protected function _actionRead(KCommandContext $context)
    {
        return $this->getItem();
    }
    
    /**
     * Delete Action
     *
     * @param  KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionDelete(KCommandContext $context)
    {
        $context['status'] = KHttpResponse::CREATED;
        $entity = $this->getItem()->delete();
        return $entity;
    }        
}