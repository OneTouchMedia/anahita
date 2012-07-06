<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Themes
 * @subpackage Filter
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Renders message
 * 
 * @category   Anahita
 * @package    Lib_Themes
 * @subpackage Filter
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibThemesTemplateFilterMessage extends KTemplateFilterAbstract implements KTemplateFilterWrite
{
    /**
     * Writes out the system message
     *
     * @param string
     * @return KTemplateFilterAlias
     */
    public function write(&$text) 
    {
    	$string	  = $this->getTemplate()->renderHelper('message.renderQueueMessages');
        
    	$text = str_replace('<jdoc:include type="message" />', $string, $text);
    }
}