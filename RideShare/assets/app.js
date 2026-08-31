function togglePassword() {
    const input = document.getElementById("password");

    if (!input) return;

    input.type = input.type === "password" ? "text" : "password";
}

const themeBtn = document.getElementById("themeBtn");

if (themeBtn) {
    themeBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        themeBtn.textContent =
            document.body.classList.contains("dark-mode") ? "☾" : "☼";
    });
}
