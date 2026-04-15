<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Log In</title>
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body class="LogInPage-Tala">

        <!-- Header -->
        <header>
            <h2>
                <span class="brand">Kiddo</span>Bites
            </h2>
        </header>

        <main>
            <div class="auth-card">
                <h1>Log In</h1>
                <img src="images/chefLogo.png" alt="" class="form-mascot">
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-box">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="login_handler.php"> 
                    <p>
                        <label for="loginEmail">Email Address</label><br>
                        <input type="email" id="loginEmail" placeholder="example@email.com" name="emailAddress" required>
                    </p>

                    <p>
                        <label for="loginPassword">Password</label><br>
                        <input type="password" id="loginPassword" placeholder="******" name="password" required>
                    </p> 

                    <p class="signup-link">
                        Don’t have an account? <a href="signup.php">Sign up</a>
                    </p> <br>

                    <p>
                        <button type="submit" formaction="login_handler.php">Log In</button>
                    </p>

                </form>
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <p>© 2026 KiddoBites — Healthy Yummies for Tiny Tummies</p>
        </footer>

    </body>
</html>
