<?php


/**
 * @since Version 1.6
 */
function anahita_1() {
    #dbexec(dbparse(file_get_contents(dirname(__FILE__).'/data.sql')));
}

/**
 * @since Version 1.6
 */
function anahita_2() {
    dbexec("UPDATE jos_anahita_nodes SET access = CONCAT_WS(',',story_subject_id,story_target_id) WHERE type LIKE 'AnSeEntityNode,AnSeEntityMedium,ComStoriesDomainEntityStory,com.stories.domain.entity.story' AND name LIKE 'private_message'");
    dbexec("UPDATE jos_anahita_nodes AS story, jos_anahita_nodes AS actor SET story.component = actor.component WHERE story.name LIKE 'story_add' AND story.component LIKE 'com_stories' AND actor.id = story.story_target_id;");
}

/**
 * @since Version 1.6
 */
function anahita_3() {
    dbexec("UPDATE jos_anahita_nodes SET permissions = REPLACE(REPLACE(permissions,'com_people:publish:stories','com_stories:publish:stories'),'com_groups:publish:stories','com_stories:publish:stories')");
}
/**
 * @since Version 1.6
 */
function anahita_4() {
    dbexec("ALTER TABLE jos_anahita_nodes ADD is_default TINYINT(1) NULL AFTER `enabled`");
    dbexec("CREATE INDEX type_default ON jos_anahita_nodes(type,is_default)");
}

/**
 * @since Version 1.6
 */
function anahita_5() {
    dbexec("ALTER TABLE jos_anahita_nodes CHANGE ordering ordering INT(11) SIGNED NULL");
}

/**
 * @since Version 1.6
 */
function anahita_6() {
    dbexec("UPDATE jos_anahita_edges SET `component` = 'com_people' WHERE `component` = 'com_socialengine'");
}

/**
 * @since Version 1.6
 */
function anahita_7() {
    //dbexec("UPDATE jos_components SET `link` = CONCAT('option=',`option`) WHERE `option` IN ('com_people','com_dashboard')");
}

/**
 * @since Version 1.6
 */
function anahita_8() {
    //remove the mod_roknavmenu 
    dbexec('DELETE menu, module FROM jos_modules_menu AS menu,jos_modules AS module WHERE module.id = menu.moduleid AND module.module = "mod_roknavmenu" ');
    //remove the files. easier 
    $remove[] = JPATH_ROOT . '/language/en-GB/en-GB.mod_roknavmenu.ini';
    $remove[] = JPATH_ROOT . '/modules/mod_roknavmenu';
    jimport('joomla.filesystem.file');
    foreach ($remove as $item) {
        if (is_file($item)) {
            JFile :: delete($item);
        }
        elseif (is_dir($item)) {
            JFolder :: delete($item);
        }
    }

    $module = dbfetch("SELECT id,params FROM jos_modules WHERE module = 'mod_mainmenu' AND params LIKE '%menutype=mainmenu%'", KDatabase :: FETCH_ARRAY);

    if ($module) {
        $param = new JParameter($module['params']);
        $param->set('class_sfx', ' nav');
        $param = $param->toString();
        dbexec("UPDATE jos_modules SET published=1,showtitle=0,position='navigation', params = '$param' WHERE id = {$module['id']}");
    } else {
        dbexec("INSERT INTO jos_modules VALUES(NULL, 'Main Menu', '', 0, 'navigation', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 0, 0, 'menutype=mainmenu\nmenu_style=list\nstartLevel=0\nendLevel=0\nshowAllChildren=1\nwindow_open=\nshow_whitespace=0\ncache=1\ntag_id=\nclass_sfx= nav\nmoduleclass_sfx=\nmaxdepth=10\nmenu_images=0\nmenu_images_align=0\nmenu_images_link=0\nexpand_menu=0\nactivate_parent=0\nfull_active_id=0\nindent_image=0\nindent_image1=\nindent_image2=\nindent_image3=\nindent_image4=\nindent_image5=\nindent_image6=\nspacer=\nend_spacer=\n\n', 0, 0, '')");
    }

    $module = dbfetch("SELECT id,params FROM jos_modules WHERE module = 'mod_mainmenu' AND params LIKE '%menutype=mainmenu%'", KDatabase :: FETCH_ARRAY);

    if ($module) {
        if (!dbexists("SELECT * From jos_modules_menu WHERE moduleid={$module['id']} AND menuid = 0")) {
            dbexec("INSERT INTO jos_modules_menu VALUES({$module['id']},0)");
        }
    }
}

/**
 * @since Version 2.0
 */
function anahita_9() {
    if (!function_exists('dbreplace_func')) {
        function dbreplace_func($column, $array) {
            $statements = array ();
            foreach ($array as $key => $value) {
                $column = "REPLACE($column,'$key','$value')";
            }
            return $column;
        }
    }

    function replace_comment_identifier($type) {
        return "REPLACE($type,'com:base.domain.entity.comment',CONCAT('com:', REPLACE(component,'com_',''),'.domain.entity.comment'))";
    }

    $base = array (
        'lib.anahita.se.entity.person' => 'com:people.domain.entity.person',
        'lib.anahita.se.entity.actor' => 'com:actors.domain.entity.actor',
        'lib.anahita.se.entity.block' => 'com:actors.domain.entity.block',
        'lib.anahita.se.entity.follow' => 'com:actors.domain.entity.follow',
        'lib.anahita.se.entity.administrator' => 'com:actors.domain.entity.administrator',
        'com.' => 'com:',
        'lib.anahita.se.entity.' => 'com:base.domain.entity.'
    );

    $type = dbreplace_func('type', array_merge(array (
        'AnSeEntityComment,' => 'ComBaseDomainEntityComment,',
        'AnSeEntityNode,' => '',
        'AnSeEntityGroup,' => '',
        'AnSeEntityMedium' => 'ComMediumDomainEntityMedium',
        'AnSeEntityPerson' => 'ComPeopleDomainEntityPerson',
        'AnSeEntityActor' => 'ComActorsDomainEntityActor',
        
    ), $base));

    $update = array (
        "type =" . replace_comment_identifier($type
    ));

    foreach (array (
            'owner_type',
            'parent_type',
            'story_object_type'
        ) as $col) {
        $update[] = "$col = " . replace_comment_identifier(dbreplace_func($col, $base));
    }

    dbexec("UPDATE jos_anahita_nodes SET " . implode(',', $update));

    $type = dbreplace_func('type', array_merge(array (
        'AnSeEntityVote,' => '',
        'AnSeEntityEdge,' => '',
        'AnSeEntityAdministrator' => 'ComActorsDomainEntityAdministrator',
        'AnSeEntityBlock' => 'ComActorsDomainEntityBlock',
        'AnSeEntityFollow' => 'ComActorsDomainEntityFollow',
        'AnSeEntity' => 'ComBaseDomainEntity',
        'AnSeEntityActor' => 'ComActorsDomainEntityActor',
        
    ), $base));

    $update = array (
        "type =" . replace_comment_identifier($type
    ));

    foreach (array (
            'node_a_type',
            'node_b_type'
        ) as $col) {
        $update[] = "$col = " . replace_comment_identifier(dbreplace_func($col, $base));
    }

    dbexec("UPDATE jos_anahita_edges SET " . implode(',', $update));

    dbexec("UPDATE jos_anahita_nodes SET `name` = " . dbreplace_func('`name`', array (
        'lib.anahita.se.entity.person' => 'com:people.domain.entity.person',
        'site::' => '',
        'com.' => 'com:'
    )) .
    ' WHERE type LIKE "ComAppsDomainEntityActortype,com:apps.domain.entity.actortype"');

    //delete koowa plugin
    dbexec('DELETE FROM jos_plugins WHERE element LIKE "koowa" AND folder LIKE "system"');
    dbexec('ALTER TABLE jos_anahita_edges DROP COLUMN component');
    dbexec('DELETE FROM jos_anahita_edges WHERE type LIKE "ComBaseDomainEntityOwnership%"');
    dbexec('UPDATE jos_plugins SET name = "System - Anahita" WHERE element LIKE "anahita"');
}

/**
 * @since Version 2.0
 */
function anahita_10() {
    dbexec("INSERT INTO jos_menu_types VALUES(NULL,'viewer','Viewer Menu','The menu that will show in the viewer module');");
    $row = dbfetch("select id, name from jos_menu where id = 64", KDatabase :: FETCH_ARRAY);
    $parent_id = 3;
    $move_ids = array (
        5
    );
    $delete_ids = array ();
    if ($row) {
        $ids = dbfetch("select id from jos_menu where parent = 64", KDatabase :: FETCH_FIELD_LIST);
        if ($row['name'] == 'You' && count($ids) == 7) {
            $parent_id = 64;
            $move_ids = $ids;
            $delete_ids = array (
                139,
                150,
                149
            );
        }
    }
    $delete_ids[] = $parent_id;
    dbexec("UPDATE jos_menu SET menutype = 'viewer', parent = 0 WHERE id IN (" . implode(',', $move_ids) . ");");
    dbexec("DELETE FROM jos_menu WHERE id IN (" . implode(',', $delete_ids) . ");");
    dbexec("INSERT INTO `jos_modules` VALUES(NULL, 'Viewer', '', 0, 'viewer', 0, '0000-00-00 00:00:00', 1, 'mod_viewer', 0, 0, 0, 'menutype=viewer\n', 0, 0, '')");
    dbexec("INSERT INTO jos_modules_menu VALUES((SELECT id FROM jos_modules WHERE module = 'mod_viewer'),0);");
}

/**
 * adds notifications ids
 */
function anahita_11() {
    dbexec('ALTER TABLE jos_anahita_nodes
                ADD notification_ids     TEXT NULL AFTER administrating_ids,
                ADD new_notification_ids TEXT NULL AFTER notification_ids');
}

/**
 * Sets the shared_owner_ids, shared_owner_count 
 */
function anahita_12() 
{
    dbexec("UPDATE jos_anahita_nodes SET shared_owner_ids = NULL,shared_owner_count = NULL WHERE type like 'ComMediumDomainEntityMedium%'");
    //delete all the duplicate comment. how do we know it's duplicate
    //whenever a comment story is created the owner should be the same as the target
    //if the target is not the same as owner then it's a duplicate    
    dbexec("DELETE story FROM jos_anahita_nodes story WHERE story.name LIKE '%_comment%' AND story.owner_id <> story.story_target_id");
    dbexec("UPDATE jos_anahita_nodes SET name = 'actor_add' WHERE name = 'group_add' AND type LIKE 'ComMediumDomainEntityMedium,ComStoriesDomainEntityStory,com:stories.domain.entity.story'");
            
    $sql = "from jos_anahita_edges as n 
left join jos_anahita_edges as s on n.node_a_id = s.node_a_id and n.node_b_id = s.node_b_id and s.type like '%ComBaseDomainEntitySubscription%' 
where n.type like 'ComNotificationsDomainEntitySetting%'
and (n.meta like '%\"value\":\"2\"%')";
    $actorIds = dbfetch("select n.node_b_id ".$sql, KDatabase::FETCH_FIELD_LIST);
    //delete the subscriptions that there's a notifications that's set to none    
    dbexec("delete s,n ".$sql);
    
    $sql = "from jos_anahita_edges as n 
left join jos_anahita_edges as s on n.node_a_id = s.node_a_id and n.node_b_id = s.node_b_id and s.type like '%ComBaseDomainEntitySubscription%' 
where n.type like 'ComNotificationsDomainEntitySetting%'
and (n.meta like '%\"value\":\"0\"%' OR n.meta like '%\"value\":\"1\"%') and s.id is null";

    $actorIds = array_merge($actorIds, dbfetch("select n.node_b_id ".$sql, KDatabase::FETCH_FIELD_LIST));
    
    //for any notification that's set to all or subscribable create a subscription edge
    dbexec("INSERT INTO jos_anahita_edges (type,node_a_id,node_a_type,node_b_id,node_b_type,created_on,created_by,modified_on,modified_by) select 'ComBaseDomainEntitySubscription,com:base.domain.entity.subscription',n.node_a_id, n.node_a_type, n.node_b_id, n.node_b_type, n.created_on,n.created_by,n.modified_on,n.modified_by ".$sql);

    $sql = "from jos_anahita_edges as follow 
left join jos_anahita_edges as s on follow.node_a_id = s.node_a_id and follow.node_b_id = s.node_b_id and s.type like 'ComBaseDomainEntitySubscription%'
where
follow.type like 'ComActorsDomainEntityFollow%' and 
follow.node_b_type NOT LIKE 'com:people.domain.entity.person' and s.id IS NULL and follow.created_on > '2012-05-30';";

    $actorIds = array_merge($actorIds, dbfetch("select follow.node_b_id ".$sql, KDatabase::FETCH_FIELD_LIST));
    //after may for any follow create a subscription
    dbexec("INSERT INTO jos_anahita_edges (type,node_a_id,node_a_type,node_b_id,node_b_type,created_on,created_by,modified_on,modified_by) select 'ComBaseDomainEntitySubscription,com:base.domain.entity.subscription',follow.node_a_id, follow.node_a_type, follow.node_b_id, follow.node_b_type, follow.created_on,follow.created_by,follow.modified_on,follow.modified_by ".$sql);
      
    //now reset the subscription stats
    try {
        global $kfactory_legacy;
        if ( !$kfactory_legacy )
        {
            $repos    = KService::get('repos://site/actors.actor');
            $actorIds = array_unique($actorIds);
            $actors = $repos->getQuery()->disableChain()->id($actorIds)->fetchSet()->toArray();
            $repos->getBehavior('subscribable')->resetStats($actors);
            $repos->getSpace()->commit();
        }
    }catch(Exception $e) {}
}

/**
 * Adds follow request 
 */
function anahita_13() 
{
    dbexec('ALTER TABLE jos_anahita_nodes ' .
            'ADD allow_follow_request TINYINT(1) UNSIGNED  NULL AFTER blocker_ids,' .            
            'ADD follow_requester_ids MEDIUMTEXT NULL AFTER allow_follow_request');
            
    dbexec("UPDATE jos_anahita_nodes SET actor_gender =  LOWER(CASE LOWER(actor_gender)
WHEN '' THEN NULL
WHEN '0' THEN 'Male'
WHEN '1' THEN 'Female'
WHEN '2' THEN 'Other'
ELSE IF(actor_gender IS NULL, NULL, actor_gender)
END)  WHERE type LIKE \"ComActorsDomainEntityActor,ComPeopleDomainEntityPerson,com:people.domain.entity.person\"");
}

/**
 * Fix the menu items
 */
function anahita_14()
{
    dbexec('UPDATE jos_components SET `link` = "option=com_people" where `option` LIKE "com_people"');
    dbexec('UPDATE jos_components SET `link` = "option=com_dashboard" where `option` LIKE "com_dashboard"');
        
    dbexec('UPDATE jos_menu SET `componentid` = 37 where `id` = 2');
    dbexec('UPDATE jos_menu SET `componentid` = 38 where `id` = 4');
}

/**
 * Fix the permsions format from component:action:resource to component:resource:action
 */
function anahita_15()
{
    $rows = dbfetch("select id,permissions from jos_anahita_nodes where permissions <> '' AND permissions <> '[]'");
    if ( empty($rows) ) {
        return;   
    }
    $sql  = 'UPDATE jos_anahita_nodes SET permissions = CASE id ';   
    //$sql = 'SELECT permissions, CASE id';
    $ids = array();
    foreach($rows as $row) 
    {
        $ids[]       = $row['id'];
        $persmissions = $row['permissions'];
        $persmissions = json_decode(str_replace(':publish',':add',$persmissions), true);
        
        foreach($persmissions as $key => $value)
        {
            $regx    = '/(\w+):(\w+):(\w+)/';
            $matches = array();
            if ( preg_match($regx, $key, $matches) )
            {
                $component = $matches[1];
                $action    = $matches[2];
                $resource  = KInflector::singularize($matches[3]);
                if ( $action == 'access' || $action == 'add' || $action == 'edit' )
                {
                    unset($persmissions[$key]);
                    $key  = "$component:$resource:$action";
                    $persmissions[$key] = $value;
                }
                $matches = array();
                preg_match($regx, $key, $matches);
                $component = $matches[1];                
                $resource  = KInflector::singularize($matches[2]);
                $action    = $matches[3];
                //remove any access permission
                if ( $action == 'access' ) {
                    unset($persmissions[$key]);
                }
            }
        }
        $persmissions = json_encode($persmissions);
        $sql   .= ' WHEN '.$row['id'].' THEN \''.$persmissions.'\'';
    }
    $sql .= ' END ';
    //$sql .= ' AS new_per';
    //$sql .= ' FROM jos_anahita_nodes ';
    $sql .= ' WHERE id IN ('.implode(',',$ids).')';
    dbexec($sql);
}

function anahita_16()
{
	dbexec("DELETE FROM jos_plugins WHERE element = 'gantry'");
	dbexec("DELETE FROM jos_components WHERE `option`='com_gantry'");
}

function anahita_17()
{
    dbexec("UPDATE jos_anahita_nodes SET mimetype = NULL WHERE type LIKE 'ComMediumDomainEntityMedium%'");
    dbexec("UPDATE jos_anahita_nodes SET mimetype = NULL, filename = IF(filename NOT LIKE '%.jpg%',CONCAT(filename,'.jpg'), filename) WHERE type LIKE 'ComActorsDomainEntityActor%'AND filename <> ''");
}

//story migration
function anahita_18()
{
    //bug fix. delete any edge associated with stories that are not story_add, private_message
    dbexec("delete e.* from jos_anahita_edges as e inner join jos_anahita_nodes as n on n.id = e.node_b_id where node_b_type like 'com:stories.domain.entity.story' and (n.name != 'story_add' and n.name != 'private_message') ");
    dbexec("UPDATE jos_anahita_edges as e,jos_anahita_nodes as n SET e.node_b_type = 'com:posts.domain.entity.post' WHERE n.id = e.node_b_id and node_b_type like 'com:stories.domain.entity.story'");
    
    $set = array(
      '`type` = "ComMediumDomainEntityMedium,ComPostsDomainEntityPost,com:posts.domain.entity.post"' ,
      '`name` = ""',
      '`alias` = ""',
      '`component` = "com_posts"',
    );
    $set   = implode($set,',');
    $query = 'UPDATE jos_anahita_nodes SET '.$set." where type like 'ComMediumDomainEntityMedium,ComStoriesDomainEntityStory,com:stories.domain.entity.story' and (name = 'story_add' or name = 'private_message')";
    dbexec($query);
    
    //create the stories for posts
    $query = "insert into jos_anahita_nodes(type,component,name,owner_id,owner_type, story_subject_id, story_object_type, story_object_id, story_target_id,meta,created_on,created_by,modified_on,modified_by) select 'ComStoriesDomainEntityStory,com:stories.domain.entity.story' as type,'com_posts','post_add',owner_id,owner_type,story_subject_id, 'com:posts.domain.entity.post' AS story_object_type,id AS story_object_id,story_target_id,meta,created_on,created_by,modified_on,modified_by from jos_anahita_nodes where type like 'ComMediumDomainEntityMedium,ComPostsDomainEntityPost,com:posts.domain.entity.post'";
    dbexec($query);
    
    //convert the notifications to point to the post
    $query = "update jos_anahita_nodes as nf, jos_anahita_nodes as post set nf.story_object_type = 'com:posts.domain.entity.post', nf.name = IF(nf.name='story_comment','post_comment',IF(nf.name='story_add' or nf.name='private_message','post_add',nf.name)) where post.id = nf.story_object_id and post.type like 'ComMediumDomainEntityMedium,ComPostsDomainEntityPost,com:posts.domain.entity.post' and nf.story_object_type like 'com:stories.domain.entity.story' and nf.type like 'ComNotificationsDomainEntityNotification,com:notifications.domain.entity.notification'";
    dbexec($query);
    
    //set the story data in the posts to null
    $query = "update jos_anahita_nodes set story_subject_id = null,story_object_type=null,story_object_id=null,story_target_id=null where type like 'ComMediumDomainEntityMedium,ComPostsDomainEntityPost,com:posts.domain.entity.post'";
    dbexec($query);
        
    //convert the parent_type of comments
    $query = "update jos_anahita_nodes as comment, jos_anahita_nodes as post set comment.type = 'ComBaseDomainEntityComment,com:posts.domain.entity.comment', comment.parent_type = 'com:posts.domain.entity.post' where post.id = comment.parent_id and comment.type like 'ComBaseDomainEntityComment,%' and comment.parent_type like 'com:stories.domain.entity.story'";
    dbexec($query);
    
    //delete any dangling comment 
    $query = "delete comment from jos_anahita_nodes as comment left join jos_anahita_nodes as post on post.id = comment.parent_id where comment.type like 'ComBaseDomainEntityComment,%' and post.id IS NULL";
    dbexec($query);
    
    //delete any remaining story edge. storeis shouldn't have any edge (since they are no longer subscribable,votable)
    $query = "delete edge.* from jos_anahita_edges as edge where node_b_type like 'com:stories.domain.entity.story'";
    dbexec($query);
   
    //somehow we have stories for commenting on a sotry we need to delete those
    $query = "delete edge.* from jos_anahita_edges as edge where node_a_id IN (select id from jos_anahita_nodes where story_object_type like 'com:stories.domain.entity.story') or node_b_id IN (select id from jos_anahita_nodes where story_object_type like 'com:stories.domain.entity.story')";
    dbexec($query);
    
    $query = "delete from jos_anahita_nodes where story_object_type like 'com:stories.domain.entity.story'";
    dbexec($query); 

    // story doesn't have any subscribable, commentable,describable, privatable, votable behavior
    // set the respective columns to null
    //last but not least set all the story type as non-medium    
    $query = "update jos_anahita_nodes set type = 'ComStoriesDomainEntityStory,com:stories.domain.entity.story', subscriber_count = null,subscriber_ids=null,comment_status=null,comment_count=null,alias=null,voter_up_ids=null,voter_down_ids=null,vote_up_count=null,vote_down_count=null,access=null,permissions=null where type like 'ComMediumDomainEntityMedium,ComStoriesDomainEntityStory,com:stories.domain.entity.story'";
    dbexec($query);
    
    //insert posts component    
    dbexec("INSERT INTO `jos_components` VALUES(null, 'Posts', 'option=com_posts', 0, 0, 'option=com_posts', 'Posts', 'com_posts', 0, 'js/ThemeOffice/component.png', 1, '', 1)");
    
    dbexec("UPDATE jos_anahita_nodes SET permissions = REPLACE(permissions,'com_stories:story','com_posts:post') WHERE type LIKE 'ComActorsDomainEntityActor%' ");
    
    //delete legacy plugins    
    dbexec("DELETE from jos_plugins WHERE CONCAT_WS('.',folder,element) IN ('system.legacy','system.cache','system.mtupgrade','system.mailer','content.emailcloak','content.loadmodule','content.pagenavigation','search.content','search.categories','search.sections','system.log')");
    dbexec("DELETE from jos_modules WHERE client_id = 1 AND module IN ('mod_rokquicklinks','mod_popular','mod_latest','mod_unread','mod_online','mod_logged','mod_footer','mod_status','mod_quickicon','mod_feed','mod_title','mod_toolbar')");
    
}