<?php echo $before_widget; ?>

<?php if ($title) : ?>
    <?php echo $before_title; ?>
    <?php echo $title; ?>
    <?php echo $after_title; ?>
<?php endif; ?>

<?php if ($text) : ?>
    <p><?php echo $text; ?></p>
<?php endif; ?>

<form v-if="!success" v-on:submit.prevent="subscribe">
    <input name="email" type="email" placeholder="e-postadress" v-model="subscriber.email">
    <input type="submit" value="Prenumerera" :disabled="currentlySubscribing">
</form>

<p class="success" v-if="success">
    Tack för att du prenumererar!
</p>
<div v-else>
	<p v-if="error">Något verkar inte korrekt med din epostadress. Kontrollera din adress och prova igen.</p>
</div>

<p class="currentlySubscribing" v-if="currentlySubscribing" :hidden="success">Vi lägger till din e-postadress</p>

<?php echo $after_widget; ?>
