<!doctype html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
    <main>
        <section aria-labelledby="branding-heading">
            <header>
                <p>PANDATA VOLLEY</p>
                <small>PRO VOLLEYBALL ANALYTICS</small>
            </header>

            <h1 id="branding-heading"><?= htmlspecialchars($heroTitle) ?></h1>
            <p>
                Pro Analysis System for professional volleyball teams.
            </p>
        </section>

        <section aria-labelledby="form-heading">
            <header>
                <small>AUTHORIZED ACCESS</small>
                <h2 id="form-heading">SIGN IN</h2>
            </header>

            <form action="/login" method="POST">
                <fieldset>
                    <legend>Select Role:</legend>
                    <label>
                        <input type="radio" name="role" value="admin"> Admin
                    </label>
                    <label>
                        <input type="radio" name="role" value="coach"> Coach
                    </label>
                    <label>
                        <input type="radio" name="role" value="analyst"> Analyst
                    </label>
                    <label>
                        <input type="radio" name="role" value="player"> Player
                    </label>
                </fieldset>

                <div>
                    <label for="email">Work Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        required
                    >
                </div>

                <div>
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        required
                    >

                    <a href="#forgot-password">Forgot password?</a>
                </div>

                <div>
                    <label>
                        <input type="checkbox" name="remember_session" value="1">
                        Remember session
                    </label>
                </div>

                <button type="submit">Sign In &rarr;</button>
            
            </form>

            <footer>
                <small>&copy; 2026 Pandata</small>
                <nav>
                    <a href="#privacy">Privacy Policy</a>
                </nav>
            </footer>
        </section>
    </main>
</body>
</html>