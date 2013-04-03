<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2011 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Commitable behavior provides API to interace with domain context and storing
 * the last save result 
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibBaseControllerBehaviorCommittable extends KControllerBehaviorAbstract
{
    /**
     * Failed entities in the last commit
     * 
     * @var KObjectSet
     */
    protected $_failed_commits;
    
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
            'priority'   => KCommand::PRIORITY_HIGHEST,
          ));
        
        parent::_initialize($config);
    }
        
    /**
     * Executes a commit after each action. This prevents having too many
     * manuall commit
     * 
     * @param string          $name    The command name
     * @param KCommandContext $context The command context
     * 
     * @return boolean     Can return both true or false.  
     */
    public function execute($name, KCommandContext $context)
    {
        $parts = explode('.', $name);
        
        //after an action save
        if ( $parts[0] == 'after' && $parts[1] != 'cancel') 
        {
            //if there are not any commitable
            //skip
            if ( count($this->getRepository()->getSpace()->getCommitables()) == 0 ) { 
                return;
            }
            
            //do a commit
            $result = $this->commit();
            
            $type    = $result === false ? 'error' : 'success';            
            $message = $this->_makeStatusMessage($context->action, $type);
            
            if ( $result === true ) 
            {
                //succesfull commit
                if ( empty($context->status) ) {
                    $context->status = $this->getResponseStatusCode($context->action);
                }
            }

            //no need to set the context as we want to redirect back
            //previous place
            //seting contex error causes an exceptio be thrown
                        
            //set the redirect message of the contrller
            if ( !empty($message) )
            {
//                 $this->_mixer
//                     ->setFlash('commit_message', array('type'=>$type, 'message'=>$message));
            }
            
            return $result;
        }
    }
    
    /**
     * Commits all the entities in the space    
     * 
     * @return boolean
     */
    public function commit()
    {
        return $this->getRepository()->getSpace()->commitEntities($this->_failed_commits);
    }

    /**
     * Get a response status for an action
     *
     * @param string $action The action name to get a response code for
     *
     * @return int
     */
    protected function getResponseStatusCode($action)
    {
        switch($action)
        {
            case 'add'         : return KHttpResponse::CREATED; break;
            case 'delete'      : return KHttpResponse::NO_CONTENT; break;
            case 'edit'        : return KHttpResponse::RESET_CONTENT; break;
            default            : return KHttpResponse::OK; break;
        }
    }
        
    /**
     * Render a message for an action
     *
     * @param string $action The action name whose message is being built
     * @param string $type   The type of the message. The type can be success, error or info
     * 
     * @return string Return the built message
     */
    protected function _makeStatusMessage($action, $type = 'success')
    {
        $messages    = array();
        $messages[]  = strtoupper('COM-'.$this->_mixer->getIdentifier()->package.'-PROMPT-'.$this->_mixer->getIdentifier()->name.'-'.$action.'-'.$type);
        $messages[]  = strtoupper('LIB-AN-MESSAGE-'.$this->_mixer->getIdentifier()->name.'-'.$action.'-'.$type);
        $messages[]  = strtoupper('LIB-AN-MESSAGE-'.$action.'-'.$type);
        $messages[]  = 'LIB-AN-PROMPT-COMMIT-'.strtoupper($type);
        $message = translate($messages);
        return $message;
    }
    
    /**
     * Return an array of commit errors
     * 
     * @return array
     */
    public function getCommitErrors()
    {
        $errors = array();
        
        if ( $this->_failed_commits ) 
        {
            foreach($this->_failed_commits as $entity) 
            {
                $errors[(string)$entity->getIdentifier()] = array_values($entity->getErrors()->toArray());                
            }    
        }
        
        return $errors;
    }
    
    /**
     * Return a set of entities that failed the commits
     * 
     * @return KObjectSet
     */    
    public function getFailedCommits()
    {
        return $this->_failed_commits;   
    }
    
    /**
     * Return the object handle
     * 
     * @return string
     */
    public function getHandle()
    {
        return KMixinAbstract::getHandle();
    }
}