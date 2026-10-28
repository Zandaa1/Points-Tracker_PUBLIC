<?php
include("config.php");
session_start();
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($link, $_POST['confirm_password']);
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $sql_check = "SELECT * FROM users WHERE username = '$username'";
        $result_check = mysqli_query($link, $sql_check);

        if (mysqli_num_rows($result_check) > 0) {
            $error = "Username already exists!";
        } else {
            // Store the password as plain text (not recommended)
            // Store the password as plain text (not recommended for security reasons)
            $sql_insert = "INSERT INTO users (username, password, name, email) VALUES ('$username', '$password', '$name', '$email')";

            if (mysqli_query($link, $sql_insert)) {
                $success = "Account created successfully! You can now <a href='login_revamped.php'>login</a>.";
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PWU Points Tracker</title>
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
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.3);
            outline: none;
            border-color: #FFD700;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md animate__animated animate__fadeIn">
        
        <div class="text-center mb-8">
            <img src="img/pwu_logo.png" alt="PWU Logo" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg animate__animated animate__fadeInDown">
            <h1 class="text-3xl font-bold text-white tracking-wide">Create Account</h1>
            <p class="text-gray-200 text-sm mt-1">Join the PWU Points Tracker</p>
        </div>

        <div class="glass-panel rounded-2xl p-8 shadow-2xl">
            
            <?php if($error): ?>
                <div class="bg-red-500/80 border border-red-400 text-white px-4 py-3 rounded-lg mb-6 text-sm text-center animate__animated animate__shakeX">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="bg-green-500/80 border border-green-400 text-white px-4 py-3 rounded-lg mb-6 text-sm text-center animate__animated animate__bounceIn">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="name">Full Name</label>
                    <input type="text" name="name" id="name" required 
                        class="glass-input w-full py-3 px-4 rounded-xl transition duration-200"
                        placeholder="Enter your full name">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="email">Email Address</label>
                    <input type="email" name="email" id="email" required 
                        class="glass-input w-full py-3 px-4 rounded-xl transition duration-200"
                        placeholder="Enter your email">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="username">Username</label>
                    <input type="text" name="username" id="username" required 
                        class="glass-input w-full py-3 px-4 rounded-xl transition duration-200"
                        placeholder="Choose a username">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="password">Password</label>
                    <input type="password" name="password" id="password" required 
                        class="glass-input w-full py-3 px-4 rounded-xl transition duration-200"
                        placeholder="Create a password">
                </div>

                <div class="mb-8">
                    <label class="block text-gray-200 text-sm font-bold mb-2 ml-1" for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required 
                        class="glass-input w-full py-3 px-4 rounded-xl transition duration-200"
                        placeholder="Repeat your password">
                </div>

                <button type="submit" class="w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition duration-300 transform hover:-translate-y-1 border border-white/20">
                    Sign Up
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-300 text-sm">Already have an account? <a href="login_revamped.php" class="text-pwu-gold hover:text-yellow-300 font-semibold hover:underline">Login</a></p>
                <p class="mt-2"><a href="index.php" class="text-gray-400 hover:text-white text-xs">Back to Home</a></p>
            </div>
        </div>

    </div>
</body>

</html>