<?php $stylesheets = ['/css/projects.css']; ?>

<div class="dashboard">
    <nav class="dashboard__nav" aria-label="Główna nawigacja">
        <a class="dashboard__brand" href="/">Project Dashboard</a>

        <a class="dashboard__logout" href="/login">Wyloguj się</a>
    </nav>

    <div class="dashboard__content">
        <aside class="dashboard__sidebar">
            <h2>Menu</h2>

            <ul class="dashboard__menu">
                <li><a class="is-active" href="/projects" aria-current="page">Projekty</a></li>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Ustawienia</a></li>
            </ul>
        </aside>

        <main class="dashboard__main">
            <h1>Strona do prezentacji listy wszystkich projektów</h1>
            <!-- TODO: Lista projektów wyświetlona w pętli -->
        </main>
    </div>
</div>
