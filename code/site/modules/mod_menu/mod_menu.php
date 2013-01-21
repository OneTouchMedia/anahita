<?php 

$params->def('menutype', 			'mainmenu');
$params->def('class_sfx', 			'');
print ModBaseView::getInstance('mod://site/menu.html')
	->items(JSite::getMenu()->getItems('menutype', $params->get('menutype')))
	->params($params)->display();
?>