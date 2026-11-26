/**
 * Admin JavaScript for Payment Method Order Column
 */
(function($) {
	'use strict';

	// Track if there are unsaved changes
	var hasUnsavedChanges = false;
	var i18n = window.erPmocI18n || {};
	var $notice;

	$(document).ready(function() {

		// Show unsaved changes notice
		function showUnsavedNotice() {
			if (!hasUnsavedChanges) {
				hasUnsavedChanges = true;

				// Check if notice already exists
				if ($('.er-pmoc-unsaved-notice').length === 0) {
					var notice = '<div class="notice notice-warning is-dismissible er-pmoc-unsaved-notice">' +
						'<p></p>' +
						'</div>';
					$notice = $(notice);
					$notice.find('p').text(i18n.unsavedNotice);
					$('.wp-header-end').after($notice);
				}
			}
		}

		// Handle upload icon button click
		$(document).on('click', '.er-pmoc-upload-icon', function(e) {
			e.preventDefault();

			var button = $(this);
			var methodId = button.data('method');
			var inputField = $('#er-pmoc-icon-' + methodId);
			var previewImg = $('.er-pmoc-icon-preview[data-method="' + methodId + '"]');
			var valueSpan = button.closest('tr').find('.er-pmoc-icon-value');

			// Check if wp.media is available
			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				window.alert(i18n.mediaUnavailable);
				return;
			}

			// Create WordPress media uploader
			var mediaUploader = wp.media({
				title: i18n.selectIcon,
				button: {
					text: i18n.useIcon
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			// When an image is selected
			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();

				// Update hidden input with URL
				inputField.val(attachment.url);

				// Update preview image
				previewImg.attr('src', attachment.url);

				// Update value span
				valueSpan.text(attachment.filename);

				// Show reset button if not already visible
				var resetBtn = button.siblings('.er-pmoc-reset-icon');
				if (resetBtn.length === 0) {
					button.after($('<button type="button" class="button er-pmoc-reset-icon"></button>').attr('data-method', methodId).text(i18n.reset));
				}

				// Show unsaved changes notice
				showUnsavedNotice();
			});

			// Open the media uploader
			mediaUploader.open();
		});

		// Handle reset icon button click
		$(document).on('click', '.er-pmoc-reset-icon', function(e) {
			e.preventDefault();

			var button = $(this);
			var methodId = button.data('method');
			var inputField = $('#er-pmoc-icon-' + methodId);
			var previewImg = $('.er-pmoc-icon-preview[data-method="' + methodId + '"]');
			var valueSpan = button.closest('tr').find('.er-pmoc-icon-value');

			// Get default icon URL from data attribute
			var defaultIconUrl = previewImg.data('default-url');

			// Clear the input value
			inputField.val('');

			// Update preview image to default
			if (defaultIconUrl) {
				previewImg.attr('src', defaultIconUrl);
			}

			// Update value span
			valueSpan.empty().append($('<em></em>').text(i18n.usingDefault));

			// Remove reset button
			button.remove();

			// Show unsaved changes notice
			showUnsavedNotice();
		});

		// Warn user before leaving page with unsaved changes
		$(window).on('beforeunload', function() {
			if (hasUnsavedChanges) {
				return i18n.unsavedLeave;
			}
		});

		// Remove unsaved changes flag when form is submitted
		$('form').on('submit', function() {
			hasUnsavedChanges = false;
		});

	});

})(jQuery);
