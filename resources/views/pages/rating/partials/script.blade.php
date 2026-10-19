{{-- Star inputs, client-side check and AJAX submit for the rating pages. Needs $ratingFields (input names). --}}
<script>
$(document).ready(function(){
    // Show error alert if there are validation errors
    @if ($errors->any())
        document.getElementById('ratingError').classList.remove('d-none');
    @endif

    // Initialize each rating input
    $('.rating').rating({
        min: 0,
        max: 10,
        step: 1,
        size: 'sm'
    });

    // Update score display when rating changes
    $('.rating').on('change', function(e) {
        const value = $(this).val();
        $(this).closest('.rating-box').find('.rating-score').text(value + '/10');
    });

    // Handle modal close events
    const thankYouModal = document.getElementById('thankYouModal');
    thankYouModal.addEventListener('hidden.bs.modal', function () {
        window.location.href = "{{ route('welcome') }}";
    });

    // Form submission handling
    $('.rating-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        const errorElement = document.getElementById('ratingError');

        // Validate form data
        const comment = (formData.get('comment') || '').toString().trim();
        const requiredRatings = @json($ratingFields);
        if (requiredRatings.some(function (field) { return !formData.get(field); }) || !comment) {
            errorElement.innerHTML = "{{ __('guidings.rating_error') }}";
            errorElement.classList.remove('d-none');
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Hide error and show loading overlay
        errorElement.classList.add('d-none');
        if (window.PageLoader) {
            window.PageLoader.show();
        }

        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (window.PageLoader) {
                    window.PageLoader.hide();
                }
                if (response.success) {
                    // Show thank you modal
                    const modal = new bootstrap.Modal(document.getElementById('thankYouModal'));
                    modal.show();
                    
                    // Disable form after successful submission
                    form.find('input, textarea, button').prop('disabled', true);
                }
            },
            error: function(xhr) {
                if (window.PageLoader) {
                    window.PageLoader.hide();
                }
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0 list-unstyled">';
                    Object.values(errors).forEach(function(error) {
                        errorHtml += `<li>${error}</li>`;
                    });
                    errorHtml += '</ul>';
                    errorElement.innerHTML = errorHtml;
                    errorElement.classList.remove('d-none');
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    errorElement.innerHTML = "{{ __('guidings.rating_error') }}";
                    errorElement.classList.remove('d-none');
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
});
</script>
