<div id="wp-ps-options" class="wrap">

	<h1>E-post om ny bloggpost - Inställningar</h1>

	<h2>E-post som skickas när din blogg publicerar ett nytt inlägg</h2>

	<p>Platshållare du kan använda: <code>@@blog.name</code>, <code>@@post.title</code>, <code>@@post.author.name</code>,
		<code>@@post.link</code></p>

	<form v-on:submit.prevent="updateOptions">
		<table class="form-table">
			<tbody>
				<tr v-if="fetchingOptions">
					<td colspan="2"><i>Vi hämtar inställningar för e-postmeddelande</i></td>
				</tr>
				<tr>
					<th><label for="emailSubject">E-post: Ämne</label></th>
					<td><input id="emailSubject" type="text" v-model="options.emailSubject" class="regular-text"/></td>
				</tr>
				<tr>
					<th><label for="emailBody">E-post: Innehåll</label></th>
					<td>
						<textarea id="emailBody" v-model="options.emailBody" class="large-text" cols="50" rows="10"></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input id="numberOfMailsSendPerRequest" type="hidden" v-model="options.numberOfMailsSendPerRequest" value="5">
						<button name="submit" class="button button-primary" type="submit">Spara</button>
						<div v-if="updatingOptions" class="spinner is-active" style="float: none;"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<hr>

	<h2>Prenumeranter</h2>

	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th style="width: 20px;">ID</th>
				<th class="column-primary">E-postadress</th>
				<th>Prenumerantens admin-länk</th>
				<th style="width: 20px;"></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="subscriber in subscribers">
				<td>{{ subscriber.id }}</td>
				<td>{{ subscriber.email }}</td>
				<td><a href="/prenumerationsval/?subscribe_options={{ subscriber.email_blog_id_md5 }}" target="blank">{{ subscriber.email_blog_id_md5 }}</a></td>
				<td><span v-on:click="deleteSubscriber(subscriber.id)" class="dashicons dashicons-trash"></span></td>
			</tr>
			<tr v-if="fetchingSubscribers">
				<td colspan="4"><i>Vi hämtar prenumeranter</i></td>
			</tr>
			<tr v-else>
				<td v-if="subscribers.length == 0" colspan="4"><i>Just nu finns det inga prenumeranter</i></td>
			</tr>
		</tbody>
	</table>

	<h3>Lägg till prenumeranter manuellt</h3>

	<form v-on:submit.prevent="addNewSubscriber">
	<style>
		.iis-notify ul {
			list-style: none;
		}
		.iis-notify li {
			display: inline-block;
			margin-right: 20px;
		}
	</style>
		<table class="form-table iis-notify">
			<tbody>
				<tr>
					<td colspan="2">
						<h3>Välj vilken/vilka författare prenumeranten ska prenumerera på</h3>
						<ul>
						<?php
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

							echo '<li><input v-model="newSubscriber.checkedAuthors" type="checkbox" id="' . $author_data->user_login .'" value="' . $author_id .'" checked><label for="' . $author_data->user_login .'">' . $author_name .'</label></li>';
						}
						?>
						</ul>
					</td>
				</tr>
				<tr>
					<th><label for="add-email">E-post</label></th>
					<td>
						<input id="add-email" name="email" v-model="newSubscriber.email" class="regular-text" type="email" required>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<button name="submit" class="button button-primary" type="submit">Lägg till</button>
						<div v-if="updatingSubscribers" class="spinner is-active" style="float: none;"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>

<?php // I think we should hide this for the users ?>
<!--
	<hr/>

	<h2>Pågående e-postutskick</h2>

	<p>Det här är listan över "jobb" som just nu skickar e-post eller som väntar på att andra jobb ska bli klara. E-post skickas till 5 mottagare i taget</p>

	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th style="width: 20px;">ID</th>
				<th class="column-primary">Inläggs-ID</th>
				<th>Antal skickade</th>
				<th>Nästa 5 skickas (GMT)</th>
				<th>Skapad (GMT)</th>
				<th style="width: 20px;"></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="job in jobs">
				<td>{{ job.id }}</td>
				<td>{{ job.post_id }}</td>
				<td>{{ job.offset }}</td>
				<td>{{ job.next_round_gmt }}</td>
				<td>{{ job.created_gmt }}</td>
				<td><span v-on:click="deleteJob(job.id)" class="dashicons dashicons-trash"></span></td>
			</tr>
			<tr v-if="fetchingJobs">
				<td colspan="6"><i>Vi letar efter pågående jobb</i></td>
			</tr>
			<tr v-else>
				<td v-if="jobs.length == 0" colspan="6"><i>Just nu finns det inga e-post i kö</i></td>
			</tr>

		</tbody>
	</table>
-->
</div>
