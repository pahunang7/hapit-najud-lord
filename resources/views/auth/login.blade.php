<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamHome Login</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:#eef2f6;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            overflow:hidden;
            position:relative;
        }

        /* BACKGROUND IMAGE */

        body::before{
            content:'';
            position:absolute;
            inset:0;
            background:
                linear-gradient(rgba(3,43,87,0.70),
                rgba(3,43,87,0.70)),
                url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1600&auto=format&fit=crop');
            background-size:cover;
            background-position:center;
            filter:blur(2px);
            transform:scale(1.03);
        }

        /* LOGIN CARD */

        .login-card{
            position:relative;
            z-index:2;
            width:420px;
            background:#ffffff;
            border-radius:20px;
            padding:40px;
            box-shadow:
                0 15px 40px rgba(0,0,0,0.18);
            animation:fadeIn 0.5s ease;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(20px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .logo-section{
            text-align:center;
            margin-bottom:30px;
        }

        .logo-icon{
            width:70px;
            height:70px;
            background:#032b57;
            border-radius:18px;
            display:flex;
            justify-content:center;
            align-items:center;
            margin:0 auto 18px;
            color:white;
            font-size:28px;
        }

        .logo-section h1{
            color:#032b57;
            font-size:32px;
            font-weight:700;
            margin-bottom:8px;
        }

        .logo-section p{
            color:#6b7280;
            font-size:15px;
        }

        /* ERROR */

        .error-box{
            background:#fee2e2;
            color:#b91c1c;
            border:1px solid #fecaca;
            padding:12px 14px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
        }

        /* FORM */

        .form-group{
            margin-bottom:22px;
        }

        .form-group label{
            display:block;
            margin-bottom:8px;
            font-size:14px;
            font-weight:600;
            color:#374151;
        }

        .input-wrapper{
            position:relative;
        }

        .input-wrapper i{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#6b7280;
        }

        .input-wrapper input{
            width:100%;
            padding:14px 14px 14px 45px;
            border:1px solid #d1d5db;
            border-radius:12px;
            font-size:15px;
            transition:0.2s ease;
            background:#f9fafb;
        }

        .input-wrapper input:focus{
            outline:none;
            border-color:#032b57;
            background:white;
            box-shadow:0 0 0 4px rgba(3,43,87,0.10);
        }

        /* BUTTON */

        .login-btn{
            width:100%;
            border:none;
            background:#032b57;
            color:white;
            padding:14px;
            border-radius:12px;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            transition:0.2s ease;
        }

        .login-btn:hover{
            background:#054080;
            transform:translateY(-1px);
        }

        .footer-text{
            text-align:center;
            margin-top:22px;
            color:#9ca3af;
            font-size:13px;
        }

    </style>
</head>
<body>

<div class="login-card">

    <div class="logo-section">
        <div class="logo-icon">
            <i class="fa-solid fa-house"></i>
        </div>

        <h1>DreamHome</h1>
        <p>Staff Portal Login</p>
    </div>

    @if ($errors->any())
        <div class="error-box">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label>NIN</label>

            <div class="input-wrapper">
                <i class="fa-solid fa-id-card"></i>

                <input
                    type="text"
                    name="nin"
                    placeholder="Enter your NIN"
                    value="{{ old('nin') }}"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>

            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>
        </div>

        <button type="submit" class="login-btn">
            Sign In
        </button>

    </form>

    <div class="footer-text">
        DreamHome Property Management System
    </div>

</div>

</body>
</html>