const form = document.getElementById('signupForm');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const errorMessage = document.getElementById('errorMessage');

form.addEventListener('submit', function (event) {
    event.preventDefault(); // Stop page reload

    // Validate Passwords
    if (password.value !== confirmPassword.value) {
        password.classList.add('is-invalid');
        confirmPassword.classList.add('is-invalid');
        errorMessage.textContent = 'Passwords do not match.';
        errorMessage.style.display = 'block';
        return;
    }

    // Submit Form via AJAX
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Initialize and trigger Top Toast Pop-up Notification
        const toastElement = document.getElementById('successToast');
        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 3000 });
        toast.show();

        // Clear input fields
        form.reset();

        // Redirect to login after 2 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
    })
    .catch(error => {
        errorMessage.textContent = 'Something went wrong. Please try again.';
        errorMessage.style.display = 'block';
    });
});

// Clear red borders and error text on typing
[password, confirmPassword].forEach(input => {
    input.addEventListener('input', function () {
        password.classList.remove('is-invalid');
        confirmPassword.classList.remove('is-invalid');
        errorMessage.style.display = 'none';
    });
});