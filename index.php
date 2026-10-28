<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <link rel="manifest" crossorigin="use-credentials" href="manifest.json" />
    
    <!-- Add to Homescreen -->
    <link rel="stylesheet" href="css/add-to-homescreen.min.css" />
    <script src="js/add-to-homescreen.min.js"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="css/animate.min.css">

    <style>
        body {
            background-image: url(img/pwu_bg.jpg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md text-center">
        
        <div class="mb-8 animate__animated animate__fadeInDown">
            <img src="img/pwu_logo.png" alt="PWU Logo" class="h-24 w-auto mx-auto drop-shadow-lg">
        </div>
    

        <div class="glass-panel rounded-2xl p-8 shadow-2xl animate__animated animate__zoomIn">
            <p class="text-gray-200 text-lg mb-2 font-medium">Welcome to</p>
            <h1 class="text-4xl font-bold text-white mb-8 tracking-wide">PWU Points Tracker</h1>

            <div class="space-y-4">
                <a href="login_revamped.php" class="block w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition duration-300 transform hover:-translate-y-1 border border-white/20">
                    Login
                </a>
                
                <a href="signup.php" class="block w-full bg-white hover:bg-gray-100 text-pwu-maroon font-bold py-4 px-6 rounded-xl shadow-lg transition duration-300 transform hover:-translate-y-1">
                    Create Account
                </a>
            </div>
        </div>

<br>


        <p class="mt-8 text-white/60 text-sm animate__animated animate__fadeInUp">© Copyright Information Technology Society<br> Philippine Women's University</p>


        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.AddToHomeScreenInstance = window.AddToHomeScreen({
                appName: 'PWU Points App', // Name of the app.
                // Required.
                appNameDisplay: 'standalone', // If set to 'standalone' (the default), the app name will be diplayed
                // on it's own, beneath the "Install App" header. If set to 'inline', the
                // app name will be displayed on a single line like "Install MyApp"
                // Optional. Default 'standalone'
                appIconUrl: 'img/pwu_logo.png', // App icon link (square, at least 40 x 40 pixels).
                // Required.
                assetUrl: 'img/', // Link to directory of library image assets.

                maxModalDisplayCount: -1, // If set, the modal will only show this many times.
                // [Optional] Default: -1 (no limit).  (Debugging: Use this.clearModalDisplayCount() to reset the count)
                displayOptions: {
                    showMobile: true,
                    showDesktop: true
                }, // show on mobile/desktop [Optional] Default: show everywhere
                allowClose: true, // allow the user to close the modal by tapping outside of it [Optional. Default: true]
                showArrow: true, // show the bouncing arrow on the modal [Optional. Default: true] (highly recommend leaving at true as drastically affects install rates)
            });

            ret = window.AddToHomeScreenInstance.show('en'); // show "add-to-homescreen" instructions to user, or do nothing if already added to homescreen
            // [optional] language.  If left blank, then language is auto-decided from (1) URL param locale='..' (e.g. /?locale=es) (2) Browser language settings
        });
    </script>
</body>
</body>

</html>