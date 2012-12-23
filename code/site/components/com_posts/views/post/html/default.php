<?php defined('KOOWA') or die ?>

<?php @commands('toolbar') ?>

<module position="sidebar-b" style="simple"></module>

<div class="an-story an-entity an-record an-removable">
    <div class="story-avatar">
        <?= @avatar($post->author) ?>
    </div>      
    <div class="story-container">
        <div class="story-body">
            <?= @content($post->body) ?>
        </div>
        <div class="story-comments">
            <?= @helper('ui.comments', $post) ?>  
        </div> 
    </div>
</div>