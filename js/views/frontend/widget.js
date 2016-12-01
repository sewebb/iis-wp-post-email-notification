var Vue = require('vue-install');

module.exports = {

	el: '.wp_post_email_notification',

	data: {
		url:                  ajaxurl + '?action=wppen_v1_',
		currentlySubscribing: false,
		success:              false,
		error:                false,
		subscriber:           {
			email: ''
		}
	},

	methods: {

		subscribe: function () {

			if (!this.currentlySubscribing) {
				this.$set('currentlySubscribing', true);
				this.$http.post(this.url + 'subscribe_post', this.subscriber).then(function (response) {
					this.$set('currentlySubscribing', true);
					this.$set('success', true);
					this.$set('subscriber.email', "");
				}, function(response) {
					this.$set('currentlySubscribing', false);
					if ( false === response.ok ) {
						this.$set('error', true);
					}
					// this.handleError(response);
				});
			}
		}

	}

}
;

Vue.ready(module.exports);
