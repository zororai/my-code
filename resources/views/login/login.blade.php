<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\WebsiteSetting::get('school_name', 'Dzidzo Student Portal') }} - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .logo-container {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            background: #2d5016;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .logo-container img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .login-form {
            margin-top: 50px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
        }
        
        .form-group .toggle-password {
            position: absolute;
            right: 15px !important;
            left: auto !important;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .form-group .toggle-password:hover {
            color: #2d5016;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 45px 15px 45px;
            border: none;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            font-size: 16px;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .form-input::placeholder {
            color: #999;
            font-weight: 300;
        }
        
        .form-input:focus {
            background: rgba(0, 0, 0, 0.08);
            box-shadow: 0 0 0 2px rgba(45, 80, 22, 0.2);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            color: #666;
        }
        
        .remember-me input[type="checkbox"] {
            margin-right: 8px;
            accent-color: #2d5016;
        }
        
        .forgot-password {
            color: #999;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #2d5016;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: #2d5016;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .login-btn:hover {
            background: #1f3610;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(45, 80, 22, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 0 10px;
            }
            
            .logo-container {
                width: 70px;
                height: 70px;
                top: -35px;
            }
            
            .logo-container img {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset(\App\WebsiteSetting::get('site_logo', 'images/logo.png')) }}" alt="{{ \App\WebsiteSetting::get('school_name', 'Dzidzo Student Portal') }}">
        </div>
        
        <form class="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="email" name="email" class="form-input" placeholder="Email ID" required>
            </div>
            
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="form-input" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>