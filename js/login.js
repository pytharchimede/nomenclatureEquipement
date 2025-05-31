document
  .getElementById("loginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    const btn = document.getElementById("loginBtn");
    const spinner = document.getElementById("spinner");
    const msg = document.getElementById("message");
    msg.style.display = "none";
    btn.disabled = true;
    spinner.style.display = "inline-block";

    const formData = new FormData(this);

    try {
      const response = await fetch("request/login_request.php", {
        method: "POST",
        body: formData,
      });
      const data = await response.json();

      if (data.success) {
        msg.className = "alert alert-success";
        msg.textContent = data.message || "Connexion réussie, redirection...";
        msg.style.display = "block";
        setTimeout(() => {
          window.location.href = "dashboard.php";
        }, 1200);
      } else {
        msg.className = "alert alert-error";
        msg.textContent = data.message || "Erreur de connexion.";
        msg.style.display = "block";
      }
    } catch (err) {
      msg.className = "alert alert-error";
      msg.textContent = "Erreur serveur. Veuillez réessayer.";
      msg.style.display = "block";
    }
    btn.disabled = false;
    spinner.style.display = "none";
  });
