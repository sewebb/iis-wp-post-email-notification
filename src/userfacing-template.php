<?php
/*
Template Name: Prenumerationsval
Template Post Type: page
*/

get_header();
?>
<style>
	.iis-notify-email-general-area {
		position: relative;
		z-index: 3;
		max-width: 90%;
		min-height: 200px;
		min-height: 12.5rem;
		margin: 0 auto;
		background-color: inherit;
		padding: 20px;
		padding: 1.25rem;
		opacity: .97;
	}
	.iis-notify-checkboxes-div label {
		margin-left: 5px;
		margin-left: .125rem;
	}
	.iis-notify-checkboxes-div ul {
		list-style: none;
	}
	.iis-notify-checkboxes-div li {
		padding-bottom: 10px;
		padding-bottom: .25rem;
	}

	.iis-notify-unsubscribe {
		margin-top: 100px;
		margin-top: 6.25rem;
	}

	.iis-notify-email {
		height: 40px;
		height: 2.5rem;
		min-width: 40%;
	}

	.iis-notify-submit {
		display: inline-block;
		text-align: center;
		line-height: 1;
		cursor: pointer;
		vertical-align: middle;
		border: 1px solid transparent;
		padding: .85em 1em;
		margin: 0 0 16px 0;
		margin: 0 0 1rem 0;
		font-size: 14.4px;
		font-size: .9rem;
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
	<div class="site-main site-content js-iis-notify">
		<div class="iis-notify-email-general-area">
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
						$cooke_mail     = isset( $_COOKIE['wppen_v1_email'] ) ? $_COOKIE['wppen_v1_email'] : '';
						$curr_authors   = isset( $_COOKIE['wppen_v1_authors'] ) ? $_COOKIE['wppen_v1_authors'] : '';
						// transform to integer array, thats how we list the users authors if we use the db value, se below
						$curr_authors   = array_map('intval',  array_filter( explode( ',', $curr_authors ) ) );

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

							if ( $curr_email || isset( $curr_authors[0] ) ) {
								if ( in_array( $author_id, $curr_authors ) ) {
									$checked = 'checked';
								} else {
									$checked = '';
								}
							}
							// Prechoosen author - if cookie get involved, this makes sure at least this author is selected
							if ( $req_author ) {
								if ( $req_author === $author_id ) {
									$checked = 'checked';
								}
							}

							echo '<li><input v-model="subscriber.checkedAuthors" type="checkbox" id="' . $author_data->user_login .'" value="' . $author_id .'" ' . $checked . '><label for="' . $author_data->user_login .'">' . $author_name .'</label></li>';
						}
						?>
						</ul>
					</div>
					<?php
					// If not email from db, maybe we have it in a cookie
					if ( ! $curr_email && $cooke_mail ) {
						$curr_email = $cooke_mail;
					}
					?>

				    <input class="iis-notify-email" name="email" type="email" placeholder="Din e-postadress" required v-model="subscriber.email" value="<?php echo $curr_email; ?>" <?php echo $email_disabled; ?>>
				    <input class="iis-notify-submit" type="submit" value="<?php echo $submit_label; ?>" :disabled="currentlySubscribing">
				</form>
			</div>
			<div class="iis-notify-messages entry-content">
				<h3 class="success" v-if="success">
				    Tack för att du prenumererar!
				</3>
				<div v-else>
					<p v-if="error">Något verkar inte korrekt med din epostadress. Kontrollera din adress och prova igen.</p>
				</div>

			</div>
			<?php echo $unsubscribe; ?>
		</div>
	</div>
</div>


<?php
get_footer();
