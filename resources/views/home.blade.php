<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto MIM</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="dark-mode">
<div class="toggle-container">
    <button id="theme-toggle">
        <span id="toggle-icon">🌙</span>
        <span>Dark/Light Mode</span>
    </button>
</div>
<h1>Projeto MIM</h1>
<p>Seja bem-vindo ao Projeto MIM!</p>
<div class="botao-container">
    <ul>
        <li><a href="{{ route('cursos') }}">Cursos</a></li>
        <li><a href="{{ route('unidades-curriculares', ['cod_curso' => 9119]) }}">Unidades Curriculares</a></li>
        <li><a href="{{ route('unidade-curricular', ['cod_curso' => 9119, 'uc_shortname' => '9119201_TESTG1D_S1']) }}">Unidade
                Curricular</a></li>
        <li><a href="{{ route('grupos', ['cod_curso' => 9999, 'uc_shortname' => 'EAD_2023_24_304_S1']) }}">Grupos de uma
                UC</a>
        </li>
        <li><a href="{{ route('acessos', ['cod_curso' => 9999, 'uc_shortname' => 'EAD_2023_24_304_S1']) }}">Acessos de
                uma UC</a>
        </li>

        <li><a href="{{ route('acessos-todos-users-curso', ['cod_curso' => 9999]) }}">Acessos Users de um curso</a>
        </li>

        <!-- Enpoints que não estão a ser usados
        <li><a href="{{ route('ucs-estudante', ['email_estudante' => 'ued2016+09@gmail.com']) }}">Unidades Curriculares
                de um Estudante</a></li>
        <li><a href="{{ route('grupos-estudante', ['email_estudante' => 'ued2016+09@gmail.com']) }}">Grupos de um
                Estudante</a></li>
        <li><a href="{{ route('acessos-uc-estudante', ['email_estudante' => 'ued2016+09@gmail.com']) }}">Últimos acessos
                de um Estudante</a></li>
        <li>
            <a href="{{ route('acessos-uc-estudante-v2', ['uc_shortname' => 'EAD_2023_24_304_S1', 'nr_estudante' => 'ued2016+09@gmail.com']) }}">acessos
                V2</a></li>
        -->
    </ul>
</div>
<br>
<br>
<h2>Token</h2>
<div class="input-container">
    <form action="{{ route('set-token') }}" method="POST">
        @csrf
        <input type="text" id="token" name="token" required class="clear-input">
        <button type="submit" class="submit-button">Submeter</button>
    </form>
    <form action="{{ route('clear-token') }}" method="POST" style="margin-left: 10px;">
        @csrf
        <button type="submit" class="clear-button">Remover Token</button>
    </form>
</div>
<div class="instructions-container">
    <a href="{{ route('instrucoes') }}" class="instructions-button">Instruções para obter o Token</a>
</div>
<br>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<footer>
    <div>
        <p>Projeto Informático nº232</p>
        <p>Orientadores: Marisa Maximiano, Ricardo Gomes e Vitor Távora</p>
        <p>Agradecimentos à Catarina Maximiano, integrante do CIP</p>
        <p>Projeto realizado por: Bernardo Melo e Diogo Ferreira</p>
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
