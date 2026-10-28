<?php
include("config.php");
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($link, $sql);
    $count = mysqli_num_rows($result);

    if ($count == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['login_user'] = $username;
        $_SESSION['role'] = $row['role'];
        $_SESSION['id'] = $row['id'];

        if ($row['role'] == 'admin' || $row['role'] == 'super_admin') {
            header("location: admin_dashboard.php");
        } else {
            header("location: student_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid login. Try again!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    
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
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md animate__animated animate__fadeIn">
        
        <div class="text-center mb-8">
            <img src="img/pwu_logo.png" alt="PWU Logo" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg animate__animated animate__fadeInDown">
            <h1 class="text-3xl font-bold text-white tracking-wide">Welcome Back!</h1>
            <p class="text-gray-200 text-sm mt-1">Sign in to continue</p>
        </div>

        <div class="glass-panel rounded-2xl p-8 shadow-2xl">
            
            <?php if($error): ?>
                <div class="bg-red-500/80 border border-red-400 text-white px-4 py-3 rounded-lg mb-6 text-sm text-center animate__animated animate__shakeX">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-6">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="username">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" id="username" required 
                            class="glass-input w-full py-3 pl-10 pr-4 rounded-xl transition duration-200"
                            placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="password">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="glass-input w-full py-3 pl-10 pr-4 rounded-xl transition duration-200"
                            placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition duration-300 transform hover:-translate-y-1 border border-white/20">
                    Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-300 text-sm">Don't have an account? <a href="signup.php" class="text-pwu-gold hover:text-yellow-300 font-semibold hover:underline">Sign up</a></p>
                <p class="text-gray-300 text-sm">Forgot your password? <a href="#" onclick="openHelpModal(); return false;" class="text-pwu-gold hover:text-yellow-300 font-semibold hover:underline">Get help</a></p>
                <p class="mt-2"><a href="index.php" class="text-gray-400 hover:text-white text-xs">Back to Home</a></p>
            </div>
        </div>

    </div>

    <!-- Help Modal -->
    <div id="helpModal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex items-center justify-center z-50 animate__animated animate__fadeIn">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 relative">
            <button onclick="closeHelpModal()" class="absolute top-4 right-4 text-pwu-maroon hover:text-red-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-center mb-4">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-pwu-maroon mb-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-pwu-maroon mb-2 font-sans">Password Reset Instructions</h2>
            </div>
            
            <div class="text-left space-y-4 text-gray-700 font-sans">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm leading-relaxed">
                        <strong class="block mb-2 text-pwu-maroon">How to Reset Your Password:</strong>
                        Go to the <strong>registration booth</strong> to request a password reset. You must provide proof of ownership, such as showing the same name as the registered account.
                    </p>
                </div>
                
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm leading-relaxed">
                        <strong class="block mb-2 text-yellow-700">After Reset:</strong>
                        Your password will be reset to <code class="bg-white px-2 py-1 rounded font-mono text-xs border">00000000</code> (8 zeros). 
                        <strong class="text-yellow-700">Please change it immediately</strong> after logging in for security purposes.
                    </p>
                </div>
            </div>

            <button onclick="closeHelpModal()" class="w-full mt-6 bg-pwu-maroon hover:bg-red-900 text-white font-bold py-3 px-4 rounded-lg transition font-sans">
                Got it
            </button>
        </div>
    </div>

    <script>
        function openHelpModal() {
            document.getElementById('helpModal').classList.remove('hidden');
        }

        function closeHelpModal() {
            document.getElementById('helpModal').classList.add('hidden');
        }
    </script>
</body>

</html>