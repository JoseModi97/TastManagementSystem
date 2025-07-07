$(document).ready(function() {
    var $modal = $('#ajaxModal');
    var $modalBody = $modal.find('.modal-body');
    var $modalTitle = $modal.find('.modal-title');
    var $modalFooter = $modal.find('.modal-footer'); // Added for potential dynamic buttons

    // Function to open modal and load content
    function openModal(url, title) {
        $modalTitle.text(title || 'Details'); // Set title or a default
        $modalBody.html('Loading...'); // Show loading text
        // Clear previous dynamic footer buttons if any, keep the static close button
        $modalFooter.find('.dynamic-button').remove();
        $modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $modalBody.html(response);
                // If response contains specific instructions for footer buttons, handle here
                // For example, if form loaded has data-footer-buttons attribute
                var $loadedContent = $(response);
                if ($loadedContent.data('footer-save-label')) {
                    var saveButton = $('<button type="button" class="btn btn-primary dynamic-button" id="modalSaveButton">' + $loadedContent.data('footer-save-label') + '</button>');
                    $modalFooter.append(saveButton);
                }
            },
            error: function() {
                $modalBody.html('<div class="alert alert-danger">Error loading content.</div>');
            }
        });
    }

    // Event delegation for ajax-modal-link
    $(document).on('click', '.ajax-modal-link', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).data('modal-title');
        openModal(url, title);
    });

    // Event delegation for form submission inside modal
    // Ensure form has 'ajax-modal-form' class or target specifically
    $(document).on('submit', '#ajaxModal form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var url = $form.attr('action');
        var method = $form.attr('method');
        var data = new FormData(this); // Use FormData for file uploads

        // Add a save button to the footer if not present, or use a specific button if form has one
        var $saveButton = $modalFooter.find('#modalSaveButton');
        if ($saveButton.length === 0) {
             $saveButton = $modalFooter.find('button[type="submit"], .btn-primary'); // Fallback
        }
        var originalButtonText = $saveButton.html();
        $saveButton.html('Saving...').prop('disabled', true);


        $.ajax({
            url: url,
            type: method || 'POST',
            data: data,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            dataType: 'json', // Expect JSON response from server
            success: function(response) {
                if (response.success) {
                    $modal.modal('hide');
                    if (response.message) {
                        // TODO: Implement a more robust notification system
                        alert(response.message); // Placeholder
                    }
                    if (response.pjaxReload) {
                        $.pjax.reload({container: response.pjaxReload, async:false});
                    }
                } else {
                    if (response.content) {
                        // Validation error, re-render form
                        $modalBody.html(response.content);
                    } else if (response.message) {
                        // Other error
                        $modalBody.prepend('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                }
            },
            error: function() {
                // General AJAX error
                 $modalBody.prepend('<div class="alert alert-danger">Error submitting form. Please try again.</div>');
            },
            complete: function() {
                $saveButton.html(originalButtonText).prop('disabled', false);
            }
        });
    });

    // Handle click on modalSaveButton if it was dynamically added
    // This is important if the submit button is in the footer and not part of the <form> tag directly
    $(document).on('click', '#modalSaveButton', function() {
        $('#ajaxModal form').submit(); // Trigger the form submission
    });


    // Optional: Clear modal content on hide to prevent stale data
    $modal.on('hidden.bs.modal', function () {
        $modalBody.html('Loading...');
        $modalTitle.text('Modal');
        $modalFooter.find('.dynamic-button').remove(); // Clean up dynamic buttons
    });

});
