<?php
/*
Template Name: Prenumerationsval
Template Post Type: page
*/

get_header();
?>
<style>

	.iis-notify-checkboxes-div label {
		margin-left: 5px;
	}
	.iis-notify-checkboxes-div ul {
		list-style: none;
	}

	.iis-notify-unsubscribe {
		margin-top: 100px;
	}

	@media screen and (min-width: 56.875em) {
	    .iis-notify-email {
			max-width: 40%;
			float: left;
		}

		.iis-notify-submit {
			float: left;
		}
	}

</style>

<div class="content-area">
	<div class="site-main js-iis-notify">
		<p class="success entry-content" v-if="unsubscribed">
		    Din e-postadress är nu borttagen och du kommer inte att få fler meddelanden om nya poster på denna bloggen.
		</p>
		<div v-else>
			<form v-if="!success" v-on:submit.prevent="subscribe" class="entry-content">

				<div class="iis-notify-checkboxes-div">
					<h3>Välj vilken/vilka författare du vill prenumerera på</h3>
					<ul>
					<?php
					$req_user       = get_query_var( 'subscribe_options' );
					$req_author     = get_query_var( 'subscribe_author' );
					$curr_id        = '';
					$curr_email     = '';
					// Check all if new subscriber
					$checked        = 'checked';
					$submit_label   = 'Prenumerera';
					$email_disabled = '';
					$unsubscribe    = '';

					if ( $req_user ) {
						global $wpdb;
						$table     = $wpdb->base_prefix . 'wppen_subscribers';
						$curr_user = $wpdb->get_row( 'SELECT * FROM ' . $table . ' WHERE email_blog_id_md5 = "' . $req_user . '"', ARRAY_A );

						$curr_id      = $curr_user['id'];
						$curr_email   = $curr_user['email'];
						$curr_authors = unserialize( $curr_user['authors_array'] );

						if ( $curr_email ) {
							$submit_label   = 'Ändra din prenumeration';
							$email_disabled = 'disabled';
							$unsubscribe    = '<div v-if="!unsubscribed" class="iis-notify-unsubscribe">
													<hr>
													<p><button v-on:click.prevent="deleteSubscriber(' . $curr_id . ')">Avsluta min prenumeration</button></p>
												</div>';
						}
					}
					// list all available authors to choose from
					$args = array(
						'orderby' => 'display_name',
						'order'   => 'ASC',
						'who'     => 'authors',
						'fields'  => 'ID',
					 );

					$our_authors = get_users( $args );

					foreach ( $our_authors as $author_id ) {
						$author_data = get_userdata( $author_id );
						$author_name = $author_data->first_name . ' ' . $author_data->last_name;

						if ( '' === trim( $author_name ) ) {
							$author_name = $author_data->display_name;
						}
						if ( $curr_email ) {
							if ( in_array( $author_id, $curr_authors ) ) {
								$checked = 'checked';
							} else {
								$checked = '';
							}
						}
						// Prechoosen author
						if ( $req_author ) {
							if ( $req_author === $author_id ) {
								$checked = 'checked';
							} else {
								$checked = '';
							}
						}

						echo '<li><input v-model="subscriber.checkedAuthors" type="checkbox" id="' . $author_data->user_login .'" value="' . $author_id .'" ' . $checked . '><label for="' . $author_data->user_login .'">' . $author_name .'</label></li>';
					}
					?>
					</ul>
				</div>

			    <input class="iis-notify-email" name="email" type="email" placeholder="Din e-postadress" required v-model="subscriber.email" value="<?php echo $curr_email; ?>" <?php echo $email_disabled; ?>>
			    <input class="iis-notify-submit" type="submit" value="<?php echo $submit_label; ?>" :disabled="currentlySubscribing">
			</form>
		</div>
		<div class="iis-notify-messages entry-content">
			<p class="success" v-if="success">
			    Tack för att du prenumererar!
			</p>
			<div v-else>
				<p v-if="error">Något verkar inte korrekt med din epostadress. Kontrollera din adress och prova igen.</p>
			</div>

		</div>
		<?php echo $unsubscribe; ?>
	</div>
</div>


<?php
get_sidebar();
get_footer();
