<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruções - Projeto MIM</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style_intrucoes.css') }}">
</head>
<body class="dark-mode">
<div class="toggle-container">
    <button id="theme-toggle">
        <span id="toggle-icon">🌙</span>
        <span>Dark/Light Mode</span>
    </button>
</div>
<div class="container">
    <h1>Instruções para Obter o Token</h1>
    <div class="instruction-content">
        <p>Irão ser apresentados os passos necessários para a obtenção do token pessoal de acesso ao serviço externo
            “MIM”.</p>
        <ol>
            <li>Primeiramente, o utilizador deverá aceder à página e autenticar-se com os seus dados de
                acesso:
                <a href="https://ead.ipleiria.pt/2023-24/user/managetoken.php" target="_blank">https://ead.ipleiria.pt/2023-24/user/managetoken.php</a>
            </li>
            <li>Por fim, o utilizador deverá observar a coluna “Serviço” presente na tabela, e deverá copiar o valor da
                coluna “Chave” correspondente ao campo do Serviço que corresponde ao “MIM”.
            </li>
        </ol>
        <p>Após seguir estes passos, o utilizador deverá conseguir obter o seu token e deverá colar o token na
            caixa de texto presente na página do Projeto MIM.</p>
    </div>
</div>

<footer>
    <div>
        <p>Projeto nº232</p>
        <p>Orientadores: Marisa Maximiano, Ricardo Gomes e Vítor Távora</p>
        <p>Agradecimentos à Catarina Maximiano, integrante do CIP</p>
        <p>Projeto Informático realizado por: Bernardo Melo e Diogo Ferreira</p>
        <a href="https://github.com/diogo-es/mim.git" style="all: unset; " target="_blank">Link do repositório:
            https://github.com/diogo-es/mim.git</a>
    </div>
</footer>

<script>
    function toggleTheme() {
        const body = document.body;
        const icon = document.getElementById('toggle-icon');
        if (body.classList.contains('dark-mode')) {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            icon.textContent = '☀️';
            localStorage.setItem('theme', 'light-mode');
        } else {
            body.classList.remove('light-mode');
            body.classList.add('dark-mode');
            icon.textContent = '🌙';
            localStorage.setItem('theme', 'dark-mode');
        }
    }

    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

    window.addEventListener('load', function () {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.body.classList.add(savedTheme);
            document.getElementById('toggle-icon').textContent = savedTheme === 'dark-mode' ? '🌙' : '🌞';
        } else {
            document.body.classList.add('dark-mode');
            document.getElementById('toggle-icon').textContent = '🌙';
        }
    });
</script>
</body>
</html>
