<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Anahita_Service
 * @subpackage Locator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Module Locator. If a module is not found, it first look at the default classes 
 *
 * @category   Anahita
 * @package    Anahita_Service
 * @subpackage Locator
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnServiceLocatorComponent extends KServiceLocatorComponent 
{
	/** 
	 * The type
	 * 
	 * @var string
	 */
	protected $_type = 'com';
	
	/**
	 * Get the classname based on an identifier
	 *
	 * @param 	mixed  		 An identifier object - koowa:[path].name
	 * @return string|false  Return object on success, returns FALSE on failure
	 */
	public function findClass(KServiceIdentifier $identifier)
	{	        
	    $path      = KInflector::camelize(implode('_', $identifier->path));
        $classname = 'Com'.ucfirst($identifier->package).$path.ucfirst($identifier->name);
        
      	//Manually load the class to set the basepath
		if (!$this->getService('koowa:loader')->loadClass($classname, $identifier->basepath))
		{
		    //the default can be in either in the default folder
		    //be a registered default class
		    $classname = AnServiceClass::findDefaultClass($identifier);
		    //hack
		    if ( !$classname || $classname == 'AnDomainBehaviorDefault') 
		    {
		        //Create the fallback path and make an exception for views
		        $classpath = $identifier->path;
		        $classtype = !empty($classpath) ? array_shift($classpath) : '';
		        $paths   = array();               
		        $paths[] = ucfirst($classtype).KInflector::camelize(implode('_', $classpath));
		        if ($classtype == 'view' ) {
		            $paths[] = ucfirst($classtype);
		        }
		        
                $paths   = array_unique($paths);
                               		        		        
		        $namespaces = array();
		        $namespaces[] = 'Com'.ucfirst($identifier->package);
		        $namespaces[] = 'Lib'.ucfirst($identifier->package);
		        $namespaces[] = 'ComBase';
		        $namespaces[] = 'LibBase';
		        $namespaces[] = 'ComDefault';		        
		        $namespaces[] = 'An';
		        $namespaces[] = 'K';
		        $namespaces = array_unique($namespaces);
		        $classes    = array();
		        foreach($namespaces as $namespace) 
		        {
		            foreach($paths as $path)
		            {
		                $names = array();
		                $names[] = ucfirst($identifier->name);
		                $names[] = empty($path) ? ucfirst($identifier->name).'Default' : 'Default';
		                foreach($names as $name)
		                {
		                    $class     = $namespace.$path.$name;
		                    $classes[] = $class;
		                    if ( $this->getService('koowa:loader')->loadClass($class, $identifier->basepath) ) {
		                        $classname = $class;
		                        break 3;
		                    }
		                }		                		                		                
		            }
		            		            
		            
		        }			        
// 		        print $identifier;var_dump($classes);print'<br />';	        
		    }
		}
		
		return $classname;
	}
}