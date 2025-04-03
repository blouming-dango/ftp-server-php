<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <!-- Wave container voor de achtergrond -->
    <div class="wave-container">
        <!-- Eerste SVG -->
        <svg class="wave" viewBox="0 0 1000 200" preserveAspectRatio="none">
            <path class="wave-path" d="M0,150 Q125,100 250,150 T500,150 T750,150 T1000,150 V200 H0 Z">
            </path>
        </svg>
        <!-- Tweede SVG, identiek aan de eerste -->
        <svg class="wave" viewBox="0 0 1000 200" preserveAspectRatio="none">
            <path class="wave-path" d="M0,150 Q125,100 250,150 T500,150 T750,150 T1000,150 V200 H0 Z">
            </path>
        </svg>
    </div>

    <!-- Tweede laag voor diepte (optioneel) -->
    <div class="wave-container" style="bottom: 20px;">
        <svg class="wave" viewBox="0 0 1000 200" preserveAspectRatio="none">
            <path class="wave-path" d="M0,160 Q150,110 300,160 T600,160 T900,160 T1000,160 V200 H0 Z">
            </path>
        </svg>
        <svg class="wave" viewBox="0 0 1000 200" preserveAspectRatio="none">
            <path class="wave-path" d="M0,160 Q150,110 300,160 T600,160 T900,160 T1000,160 V200 H0 Z">
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