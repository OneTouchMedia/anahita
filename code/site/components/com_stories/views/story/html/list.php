<?php defined('KOOWA') or die ?>
<div class="story-list-item an-entity an-record an-removable">
    <div class="story-avatar">
        <?= is_array($subject) ? @avatar(array_shift($subject)) : @avatar($subject)?>
    </div>      
    <div class="story-container"> 
        <h4 class="story-title"><?= $title ?></h4>
        <?php if ( !empty($body) ) : ?>
        <div class="story-body">
            <?= $body ?>
        </div>
        <?php endif; ?>
        
        <div class="story-timestamp">
            <?= @date($timestamp) ?>
        </div>
        <?php
           //if the comment command exists that means
           //we can comment. If we can comment then
           //that means story has a commentable object
        ?>
        <?php $can_comment = $commands->offsetExists('comment') ?>
        <?= @helper('ui.commands', $commands)?>
        
        <div id="<?= 'story-comments-'.$item->id?>" class="story-comments">
            <?php if ( !empty($comments) || $can_comment ) : ?>
            <?= @helper('ui.comments', $story->object, array('comments'=>$comments, 'can_comment'=>$can_comment, 'pagination'=>false, 'show_guest_prompt'=>false, 'truncate_body'=>array('consider_html'=>true, 'read_more'=>true))) ?>
            <?php endif;?>
            <?php if( !empty($comments) && $can_comment ): ?>
            <div class="comment-overtext-box">  
                <span class="action-comment-overtext" storyid="<?=$item->id?>">
                    <?= @text('COM-STORIES-ADD-A-COMMENT') ?>
                </span>
            </div>
            <?php endif; ?>
        </div>      
    </div>
</div>