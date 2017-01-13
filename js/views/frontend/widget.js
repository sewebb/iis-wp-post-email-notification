var Vue = require('vue-install');

module.exports = {

	el: '.js-iis-notify',

	data: {
		url:                  ajaxurl + '?action=wppen_v1_',
		currentlySubscribing: false,
		success:              false,
		unsubscribed:         false,
		error:                false,
		subscriber:           {
			email:     '',
			checkedAuthors: []
		}
	},

	methods: {

		subscribe: function () {

			if (!this.currentlySubscribing) {
				this.$set('currentlySubscribing', true);
				this.$http.post(this.url + 'subscribe_post', this.subscriber).then(function (response) {
					this.$set('currentlySubscribing', true);
					this.$set('success', true);
					this.$cookie.set('wppen_v1_authors', this.subscriber.checkedAuthors, 270);
					this.$cookie.set('wppen_v1_email', this.subscriber.email, 270);
					this.$set('subscriber.email', "");
					this.$set('subscriber.checkedAuthors', []);

				}, function(response) {

					this.$set('currentlySubscribing', false);
					if ( false === response.ok ) {
						this.$set('error', true);
					}
					// this.handleError(response);
				});
			}
		},

		deleteSubscriber: function (id) {

            var data = {
                id: id
            };

            this.$http.post(this.url + 'subscribe_delete', data).then(function (response) {
                this.$set('unsubscribed', true);
                this.$set('success', false);
            }, this.handleError);
        },

	}

}
;

Vue.ready(module.exports);

