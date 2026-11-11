# PanData Volley (WDPAI Project)

Aplikacja do analizy i telemetrii siatkarskiej w PHP 8.3 uruchamiana w kontenerach Nginx i PHP-FPM. Projekt semestralny z przedmiotu WdPAI.

## Funkcje

| Obszar | Co robi |
|---|---|
| Strona startowa / Logowanie | Wyświetla formularz logowania pod `/` (oraz `/login`). |
| Środowisko | Uruchamia Nginx, PHP-FPM, PostgreSQL, Mailpit oraz pgAdmin przez Docker Compose. |

## Tech stack

| Warstwa | Technologie |
|---|---|
| Backend | PHP 8.3 |
| Serwer HTTP | Nginx |
| Baza danych | PostgreSQL |
| Konteneryzacja | Docker, Docker Compose |
| Frontend | Semantyczny HTML5, CSS3 (Grid, Flexbox, Custom Properties) |

## Szybki start

```bash
docker compose up --build -d
```

Aplikacja: **http://localhost:8080**

## Zasady developmentu

- Nazwy techniczne zapisujemy po angielsku.
- Sekrety przechowujemy w `.env`, którego nie commitujemy.
- Każdy commit opisuje jedną logiczną zmianę.

## Struktura projektu

```text
.
├── docker/
│   ├── db/
│   ├── nginx/
│   └── php/
├── public/
│   ├── css/
│   ├── views/
│   └── index.php
├── src/
│   ├── controllers/
│   └── Routing.php
├── composer.json
├── docker-compose.yaml
└── README.md
```

## Autorzy

- Kacper Koczera — PanData Volley
