
<?= @text('Hi')?> <?= $user->name ?>


<?= @text('COM-PEOPLE-PASSWORD-RESET-BODY')?>


<?= @route('view=people&reset_password=1&token='.$user->activation) ?>




