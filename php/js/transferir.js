document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("form-transferir");
  const mensagem = document.getElementById("mensagem");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const dados = {
      email: formData.get("email"),
      valor: parseFloat(formData.get("valor"))
    };

    mensagem.textContent = "Enviando...";
    mensagem.style.color = "black";

    try {
      const res = await fetch("/php/api/transferir.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
         credentials: "include",
        body: JSON.stringify(dados)
      });

      const json = await res.json();

      if (json.sucesso) {
        mensagem.style.color = "green";
        mensagem.textContent = json.mensagem;
        form.reset();
      } else {
        mensagem.style.color = "red";
        mensagem.textContent = json.mensagem;
      }
    } catch (error) {
      mensagem.style.color = "red";
      mensagem.textContent = "Erro na comunicação com o servidor.";
    }
  });
});
