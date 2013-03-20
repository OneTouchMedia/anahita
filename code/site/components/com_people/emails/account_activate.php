
<?= @text('Hi')?> <?= $user->name ?>


<?= @text('COM-PEOPLE-PASSWORD-RESET-BODY')?>

<?= @route('view=person&token='.$user->activation) ?>
