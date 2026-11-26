# AGENTS.md

Ten plik jest przewodnikiem dla agentow AI pracujacych w tym repozytorium. Przed wprowadzeniem zmian przeczytaj go razem z `README.md` i sprawdz aktualny stan kodu. Nie zakladaj, ze opisana funkcja istnieje, dopoki nie potwierdzisz tego w plikach projektu.

## Cel projektu

To edukacyjna aplikacja webowa napisana w czystym PHP 8.3. Korzysta z lekkiej, samodzielnie zaimplementowanej architektury MVC z lekkim kontenerem DI i bez frameworka PHP. Aplikacja dziala w kontenerach Docker Compose (Nginx, PHP-FPM, PostgreSQL, Mailpit, pgAdmin).

Aktualnie aplikacja obsluguje:

- strone logowania jako strone glowna pod `/` (oraz `GET /login`),
- prawdziwe uwierzytelnianie oparte o PostgreSQL (`POST /login`, `POST /logout`, `GET /api/auth/me`) z `password_verify()` (bcrypt), weryfikacja aktywnosci konta i klubu, ochrona przed enumeracja kont (jednolity komunikat `401 Unauthorized`), tokenami CSRF (`CsrfTokenManager`), regeneracja ID sesji, opcjonalnym `remember_me` (`user_tokens` + `HttpOnly` cookie `selector:validator`), wpisami `audit_logs` oraz naglowkami `Cache-Control: no-store`,
- 5 dedykowanych sekcji URL z izolacja RBAC (kazdy kontroler egzekwuje dokladnie jedna role przez `AccessGuard`):
  - `/platform/*` -> `PlatformController` (`super_admin`)
  - `/admin/*` -> `AdminController` (`admin` / Club Admin)
  - `/coach/*` -> `CoachController` (`coach`)
  - `/analyst/*` -> `AnalystController` (`analyst`)
  - `/player/*` -> `PlayerController` (`player`)
- wspoldzielony responsywny pasek nawigacji (`public/views/partials/app-navbar.html.php`, `public/css/app-shell.css`, `public/js/app-nav.js`),
- obsluge bledow HTTP (`400`, `401`, `403`, `404`, `405`, `500`) w formacie HTML i JSON przez `ErrorResponder` oraz `public/views/errors/*.html.php`.

## Stack technologiczny

- PHP 8.3 z `declare(strict_types=1)` oraz `expose_php = Off`
- Composer i autoloading PSR-4 (`optimize-autoloader: true`)
- Nginx jako serwer HTTP (`server_tokens off`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`)
- PHP-FPM do wykonywania kodu PHP
- PostgreSQL 16 jako baza danych (12 tabel + widoki `v_club_overview`, `v_player_season_stats` w `database/init.sql`)
- Mailpit (lokalny serwer SMTP) oraz pgAdmin
- Docker oraz Docker Compose
- HTML5, CSS3 i czysty JS bez frameworka frontendowego

## Struktura katalogow

```text
.
├── database/
│   └── init.sql                     # schemat PostgreSQL, triggery, widoki i dane demo
├── docker/
│   ├── db/                          # obraz bazy danych PostgreSQL
│   ├── nginx/                       # obraz i konfiguracja Nginx (`server_tokens off`)
│   └── php/                         # obraz PHP-FPM, rozszerzenia i `pandata.ini` (`expose_php = Off`)
├── public/                          # jedyny publicznie dostepny katalog
│   ├── css/                         # style (`style.css`, `login.css`, `app-shell.css`)
│   ├── js/                          # skrypty (`theme.js`, `app-nav.js`)
│   ├── views/
│   │   ├── platform/                # widoki dla `super_admin`
│   │   ├── admin/                   # widoki dla `admin` (Club Admin)
│   │   ├── coach/                   # widoki dla `coach`
│   │   ├── analyst/                 # widoki dla `analyst`
│   │   ├── player/                  # widoki dla `player`
│   │   ├── shared/                  # wspoldzielone widoki podstron (`section.html.php`)
│   │   ├── errors/                  # szablony 400, 401, 403, 404, 405, 500 i `_error-card.html.php`
│   │   ├── partials/                # `app-navbar.html.php`, `workspace-banner.html.php`, `theme-toggle.html.php`
│   │   ├── layout.html.php          # glowny layout HTML
│   │   └── login.html.php           # strona logowania
│   └── index.php                    # front controller, kontener DI i definicje tras
├── src/
│   ├── Config/                      # `Env.php`, `Database.php`, `ContainerFactory.php`
│   ├── controllers/                 # `AppController`, `BaseRoleController`, `SecurityController`, 5 kontrolerow rol
│   ├── Enum/                        # `UserRole.php`
│   ├── Http/                        # `Request.php`, `ErrorResponder.php` i wyjatki HTTP
│   ├── Model/                       # `User`, `Club`, `MatchEntity`, `MatchEvent`, `MatchVideo`, `PlayerProfile`, `UserToken`
│   ├── Repository/                  # interfejsy i implementacje PDO (`PdoUserRepository`, `PdoTokenRepository`, `PdoAuditLogRepository`)
│   ├── Security/                    # `AuthService`, `AuthResult`, `AccessGuard`, `SessionManager`, `CsrfTokenManager`
│   ├── Container.php                # lekki kontener DI
│   └── Routing.php                  # router (GET/POST/PUT/PATCH/DELETE + parametry `{id}`)
├── .dockerignore
├── .env.example
├── composer.json
└── docker-compose.yaml
```

Nie umieszczaj logiki aplikacji ani danych wrazliwych w `public/`. Ten katalog jest document rootem Nginx i sluzy tylko jako punkt wejscia oraz miejsce na widoki i zasoby publiczne.

## Architektura MVC i DI

Projekt stosuje warstwowy przeplyw MVC z kontenerem DI (`ContainerFactory`):

```text
przegladarka
    -> Nginx
    -> public/index.php (Request + ContainerFactory + Routing)
    -> Controller (AccessGuard / CsrfTokenManager)
    -> Service / Repository (PDO -> PostgreSQL) / Model
    -> View (layout.html.php + szablon + partials)
    -> odpowiedz HTML lub JSON
```

### Front Controller i Kontener DI

`public/index.php` jest jedynym punktem wejscia aplikacji. Laduje autoloader Composera, tworzy `Request::fromGlobals()`, buduje kontener przez `ContainerFactory::create()`, rejestruje trasy, przekazuje `Request` do `Routing::dispatch()` i obsluguje nieprzechwycone wyjatki przez `ErrorResponder`.

Nowe trasy dodawaj w `public/index.php`:

```php
$routing->get('/example', ExampleController::class, 'index');
$routing->post('/example', ExampleController::class, 'store');
```

### Router

`src/Routing.php` laczy metode HTTP i sciezke URL z publiczna metoda kontrolera. Obsluguje metody `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, parametry `{name}` (dodatnie `int`) oraz `{name:regex}` (`string`), zwracajac `400`, `404` lub `405` przez `HttpException` i `ErrorResponder`. Nie dodawaj logiki biznesowej do routera.

### Controller

Kontrolery znajduja sie w `src/controllers/`, uzywaja przestrzeni nazw `App\Controller` i dziedzicza po `AppController` (kontrolery sekcji rol dziedzicza po `BaseRoleController`). Ich zadaniem jest:

- egzekwowanie autoryzacji (`AccessGuard`) i CSRF (`CsrfTokenManager`),
- odebranie i walidacja danych wejsciowych z `Request`,
- wywolanie serwisu lub repozytorium,
- przygotowanie danych dla widoku,
- wybranie szablonu przez `$this->render()` lub odpowiedzi JSON przez `$this->json()`.

Kontroler powinien byc cienki. Nie umieszczaj w nim surowych zapytan SQL, HTML ani stylow.

### Model i Repository

Encje domenowe znajduja sie w `src/Model/` (niemutowalne klasy `readonly` z `fromRow()`), a dostep do PostgreSQL w `src/Repository/` (interfejsy + klasy `Pdo*` korzystajace wyłącznie z prepared statements).

Dla nowych klas zgodnych z mapowaniem `App\` w Composerze preferuj katalogi i nazwy klas o zgodnej wielkosci liter, np.:

```text
src/Model/Project.php                -> App\Model\Project
src/Repository/ProjectRepository.php -> App\Repository\ProjectRepository
```

Po dodaniu lub przeniesieniu klas odswiez autoloader poleceniem `composer dump-autoload` (w `composer.json` aktywne jest `"optimize-autoloader": true`).

### View

Widoki znajduja sie w `public/views/` i maja nazwy `*.html.php`. `AppController::render()` przekazuje do nich dane jako zmienne przy uzyciu `extract(..., EXTR_SKIP)`.

Widoki odpowiadaja wylacznie za prezentacje. Dozwolone sa w nich proste instrukcje warunkowe i petle, ale nie logika biznesowa, dostep do bazy ani obsluga routingu.

Kazda wartosc pochodzaca od uzytkownika lub z bazy musi byc escapowana w kontekscie HTML:

```php
<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>
```

## Konwencje implementacji

- Zachowuj `declare(strict_types=1)` we wszystkich nowych plikach PHP.
- Uzywaj deklaracji typow parametrow, zwracanych wartosci i wlasciwosci.
- Nazwy klas zapisuj w `PascalCase`, a metod i zmiennych w `camelCase`.
- Nazwy techniczne w kodzie zapisuj po angielsku; tekst interfejsu moze byc po polsku lub angielsku zgodnie z istniejacym ekranem.
- Jeden kontroler powinien obslugiwac jeden spojny obszar aplikacji i egzekwowac swoja role przez `AccessGuard`.
- Dane przekazuj do widoku jawnie w tablicy drugiego argumentu `render()`.
- Nie uzywaj `$_GET`, `$_POST`, `$_COOKIE` ani `$_SERVER` w kontrolerach — korzystaj z `App\Http\Request`.
- Nie dodawaj frameworka ani duzej biblioteki bez wyraznej potrzeby i zgody uzytkownika.
- Sekrety umieszczaj w `.env`; nigdy nie commituj hasel, tokenow ani danych dostepowych.
- Nie edytuj `vendor/` recznie.
- Zachowuj zakres zadania i nie zmieniaj niezwiązanych plikow.

## Uruchamianie projektu

```bash
cp .env.example .env
docker compose up --build -d
```

Aplikacja jest domyslnie dostepna pod `http://localhost:8080`. Port mozna zmienic przez zmienna `APP_PORT`.

Przydatne polecenia:

```bash
docker compose ps
docker compose logs -f
docker compose exec php php -l src/Routing.php
docker compose exec php composer dump-autoload
docker compose down
```

Jesli katalog `vendor/` nie istnieje, zainstaluj zaleznosci/autoloader poleceniem:

```bash
docker compose run --rm php composer install
```

## Weryfikacja zmian

W projekcie nie ma obecnie skonfigurowanego zestawu testow automatycznych PHPUnit ani narzedzia do formatowania. Po zmianie:

- uruchom `php -l` dla kazdego zmienionego pliku PHP,
- uruchom `composer dump-autoload`, jezeli dodawales lub przenosiles klasy/namespace,
- uruchom kontenery (z `--build` przy zmianach w `docker/`) i sprawdz zmienione endpointy oraz naglowki odpowiedzi,
- sprawdz przypadek poprawny, bledne dane, bledy uprawnien (401/403) oraz nieistniejaca trase (404),
- upewnij sie, ze logi `docker compose logs` nie zawieraja nowych bledow,
- nie twierdz, ze testy przeszly, jezeli ich faktycznie nie uruchomiono.

## Zasady dla agentow AI

- Najpierw przeczytaj pliki bezposrednio zwiazane z zadaniem i potwierdz aktualna implementacje.
- Przed edycja sprawdz `git status` (jesli repozytorium ma `.git`); zachowaj zmiany uzytkownika i nie cofaj ich.
- Wprowadzaj najmniejsza spojna zmiane realizujaca wymaganie.
- Utrzymuj rozdzial odpowiedzialnosci MVC oraz izolacje RBAC/tenanta.
- Nie rozszerzaj zakresu zadania o refaktoryzacje, migracje lub nowe zaleznosci bez uzasadnienia.
- Jesli wymaganie jest niejednoznaczne, wybierz rozwiazanie zgodne z istniejacymi wzorcami; pytaj tylko wtedy, gdy wybor istotnie zmieni zachowanie aplikacji.
- Po zakonczeniu opisz zmienione pliki oraz wykonana weryfikacje.
