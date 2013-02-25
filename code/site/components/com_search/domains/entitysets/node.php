<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Actors
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Request edge represents a follow request between two actors. 
 *
 * @category   Anahita
 * @package    Com_Actors
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComSearchDomainEntitysetNode extends AnDomainEntitysetDefault
{
	/**
	 * Tracks the scopes count
	 * 
 	 * @var array
	 */
	protected $_scopes_count;
		
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
			'repository' => 'repos://site/base.node'
		));
	
		parent::_initialize($config);
	}
	
	/**
	 * Return the scope count for a type
	 * 
	 * @param string $scope The scope to 
	 * 
	 * @return array
	 */
	public function getScopeCount($scope)
	{
		if ( !isset($this->_scopes_count) ) 
		{			
			$query = clone $this->_query;
			$query->columns(array('node.type','count(*) AS count','node.parent_type'))
					->scope(null)
					->group(array('node.type','node.parent_type'));


			$rows  = $query->fetchRows();
			foreach($rows as $row) 
			{
				$identifier = $this->getIdentifier(array_pop(explode(',', $row['type'])));
				if ( $identifier->name == 'comment' ) {
					$identifier = $this->getIdentifier($row['parent_type']);
				}
				$key = $identifier->package.'.'.$identifier->name;				
				if ( !isset($this->_scopes_count[$key]) ) {
					$this->_scopes_count[$key] = 0;
				}
				$this->_scopes_count[$key] = $this->_scopes_count[$key] + $row['count'];								
			}
		}
		return isset($this->_scopes_count[$scope->getKey()]) ? $this->_scopes_count[$scope->getKey()] : null;
	}
}

?>