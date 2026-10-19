```javascript
const form = document.getElementById("registrationForm");

form.addEventListener("submit", function (event) {
    const name = document.getElementById("name").value.trim();
    const roll = document.getElementById("roll").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;

    const error = document.getElementById("errorMessage");

    error.textContent = "";

    if (!name || !roll || !email || !password || !confirmPassword) {
        event.preventDefault();
        error.textContent = "Please fill in all required fields.";
        return;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        event.preventDefault();
        error.textContent = "Enter a valid email address.";
        return;
    }

    const strongPassword =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    if (!strongPassword.test(password)) {
        event.preventDefault();
        error.textContent =
            "Password must contain 8 characters, uppercase, lowercase, number and symbol.";
        return;
    }

    if (password !== confirmPassword) {
        event.preventDefault();
        error.textContent = "Passwords do not match.";
    }
});
```
