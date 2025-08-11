{{-- resources/views/payment-result.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Result</title>

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>


<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header {{ request()->query('status') == 'success' ? 'bg-success text-white' : 'bg-danger text-white' }}">
        <h5 class="modal-title" id="paymentModalLabel">
          {{ request()->query('status') == 'success' ? 'Payment Successful' : 'Payment Failed' }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(request()->query('status') == 'success')
            <p>Thank you for your payment.</p>
            <p><strong>Transaction ID:</strong> {{ request()->query('token') }}</p>
            <p><strong>Amount Paid:</strong> {{ request()->query('amount') }} {{ request()->query('currency') }}</p>
        @else
            <p>Unfortunately, your payment could not be processed.</p>
            <p>Please try again or contact support.</p>
        @endif
      </div>
      <div class="modal-footer">
        <a href="{{ url('/') }}" class="btn btn-primary">Back to Home</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS bundle with Popper (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show the modal on page load
    document.addEventListener('DOMContentLoaded', function () {
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    });
</script>

</body>
</html>
