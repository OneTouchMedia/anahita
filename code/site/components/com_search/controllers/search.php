<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Search
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Search controller searches the node or searchable entities and display the
 * result.
 * 
 * The search controller searches name and body of the nodes that are searchable (specicifed by app delegate)
 * for the requested keyword. Once the result is returned, it pass the search result
 * to each app to render
 * 
 * 
 * @category   Anahita
 * @package    Com_Search
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComSearchControllerSearch extends ComBaseControllerResource
{
    /**
     * Search and return the result 
     * 
     * @param KCommandContext $context Controller command chain context
     * 
     * @return string The result to render
     */
    protected function _actionGet(KCommandContext $context)
    {        
        $this->getToolbar('menubar')->setTitle(JText::_('COM-SEARCH-HEADER'));
    	
    	$this->setView('searches');
        
        $this->_state->insert('q')->insert('type');
        
    	JFactory::getLanguage()->load('com_actors');
    	
    	if ( $this->type )
        {
            $identifier = 'repos://site/'.$this->type;
            
            if ( strpos($identifier,'.') === false ) {
                $identifier = $identifier.'.'.$this->type;
            }
          
            $description = $this->getService(KInflector::singularize($identifier))->getDescription();                      
        }
                 
        $repos = $this->getService('repos://site/base.node');
        $query = $repos->getQuery();
        
        if ( !empty($description) ) {
            $query->instanceOf((string)$description->getEntityIdentifier());
        }
        
        $keywords  = urldecode($this->q);
        
        if ( $keywords )
        {
            if ( strpos($keywords,'#') === 0 ) {
                $keywords  = explode(',', substr($keywords,1));
            }
            elseif ( strpos($keywords,' OR ') ) {
                $keywords  = explode(' OR ',$keywords);
                $operation = 'OR';
            } else {
                $keywords = explode(' ', $keywords);
                $operation = 'AND';   
            }
            
            //always search tags
            $tag_ids   = $this->getService('repos://site/tags.association')
                    ->getQuery()
                    ->link('tag')
                    ->where('name','IN',$keywords)                    
                    ->where('tag.type','=','com:tags.domain.entity.text')
                    ->fetchValues('taggable.id');
                    
            if ( empty($tag_ids) && empty($operation) ) {
                $query->where('false');
            }
            
            if ( !empty($tag_ids) ) {
                $query->clause()->id($tag_ids);
            }
            
            if ( !empty($operation) ) {
                $clause = $query->clause('OR'); 
                foreach($keywords as $keyword) {
                    $clause->where('CONCAT(IF(name IS NULL,"",name), IF(body IS NULL,"",body)) LIKE "%'.$keyword.'%"',$operation);
                }
            }
            
            //this is becuase we still have the board records in some systems. 
            $query->where('type', '<>', 'ComMediumDomainEntityMedium,ComTopicsDomainEntityBoard,com:topics.domain.entity.board');
            $query->limit(20);
            $entities = $repos->fetchSet($query);
            $this->_state->setList($entities);
            $this->keywords = $keywords;
        }
        
        return $this->getView()->display();        
    }
}