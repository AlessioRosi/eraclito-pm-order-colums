/**
 * Admin JavaScript for Payment Method Order Column
 */
(function($) {
	'use strict';

	// Track if there are unsaved changes
	var hasUnsavedChanges = false;

	$(document).ready(function() {

		// Show unsaved changes notice
		function showUnsavedNotice() {
			if (!hasUnsavedChanges) {
				hasUnsavedChanges = true;

				// Check if notice already exists
				if ($('.er-pmoc-unsaved-notice').length === 0) {
					var notice = '<div class="notice notice-warning is-dismissible er-pmoc-unsaved-notice">' +
						'<p><strong>Attenzione:</strong> Hai modifiche non salvate. Ricorda di cliccare su "Salva Impostazioni" per applicare le modifiche.</p>' +
						'</div>';
					$('.wrap h1').after(notice);
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
				alert('Errore: Media Library non disponibile.');
				return;
			}

			// Create WordPress media uploader
			var mediaUploader = wp.media({
				title: 'Seleziona Icona',
				button: {
					text: 'Usa questa icona'
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
				valueSpan.html(attachment.filename);

				// Show reset button if not already visible
				var resetBtn = button.siblings('.er-pmoc-reset-icon');
				if (resetBtn.length === 0) {
					button.after(' <button type="button" class="button er-pmoc-reset-icon" data-method="' + methodId + '">Ripristina Predefinita</button>');
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
			valueSpan.html('<em>Uso icona predefinita</em>');

			// Remove reset button
			button.remove();

			// Show unsaved changes notice
			showUnsavedNotice();
		});

		// Warn user before leaving page with unsaved changes
		$(window).on('beforeunload', function() {
			if (hasUnsavedChanges) {
				return 'Hai modifiche non salvate. Sei sicuro di voler uscire?';
			}
		});

		// Remove unsaved changes flag when form is submitted
		$('form').on('submit', function() {
			hasUnsavedChanges = false;
		});

	});

})(jQuery);
