<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Components
 * @subpackage Domain_Query
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Component query
 *
 * @category   Anahita
 * @package    Com_Components
 * @subpackage Domain_Query
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComComponentsDomainQueryComponent extends LibComponentsDomainQueryComponent
{
	/**
	 * Provides option to return assignable/nonassigable components
	 * 
	 * @return void
	 */
	protected function _beforeQueryBuild(KCommandContext $context)
	{
		parent::_beforeQueryBuild();
		
		if ( $this->getOperation() & AnDomain::OPERATION_FETCH ) {
		    $this->option($this->getService('com://admin/components.domain.set.assignablecomponent')->option);
		}		
	}
}