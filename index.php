<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="wave-container">
        <!-- Eerste SVG -->
        <svg class="wave" viewBox="0 0 1000 150" preserveAspectRatio="none">
            <path class="wave-path" d="M0,100 Q125,50 250,100 T500,100 T750,100 T1000,100 V150 H0 Z">
            </path>
        </svg>
        <!-- Tweede SVG, identiek aan de eerste -->
        <svg class="wave" viewBox="0 0 1000 150" preserveAspectRatio="none">
            <path class="wave-path" d="M0,100 Q125,50 250,100 T500,100 T750,100 T1000,100 V150 H0 Z">
            </path>
        </svg>
    </div>
    <div class="container">
        <h1>Web FTP Server</h1>
        <nav>
            <ul>
                <li>
                    <form action="login.php" method="get">
                        <button type="submit">Login</button>
                    </form>
                </li>
                <li>
                    <form action="register.php" method="get">
                        <button type="submit">Register</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</body>

</html>