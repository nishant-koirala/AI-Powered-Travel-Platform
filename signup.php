<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Signup</title>
   <style>
      

      .signup-form {
         max-width: 500px;
         margin: 50px auto;
         padding: 20px;
         background: #fff;
         border-radius: 5px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .signup-form h1 {
         margin-bottom: 20px;
         font-size: 24px;
         text-align: center;
         color: #333;
      }

      .signup-form label {
         display: block;
         margin-bottom: 10px;
         font-weight: bold;
         color: #555;
      }

      .signup-form input {
         width: 100%;
         padding: 10px;
         margin-bottom: 20px;
         border: 1px solid #ddd;
         border-radius: 5px;
         font-size: 16px;
      }

      .signup-form button {
         width: 100%;
         padding: 10px;
         background: #333;
         border: none;
         border-radius: 5px;
         color: #fff;
         font-size: 16px;
         cursor: pointer;
         transition: background 0.3s;
      }

      .signup-form button:hover {
         background: #555;
      }

      .signup-form .link {
         text-align: center;
         margin-top: 20px;
      }

      .signup-form .link a {
         color: #333;
         text-decoration: none;
         font-weight: bold;
      }
      input[type="email"] {
    text-transform: none; /* Make sure this is set to none */
}
   </style>
</head>
<body>

<?php include 'component/navbar_links.php'; ?>


<div class="signup-form">
    <h1>Sign Up</h1>
    <form action="signup_process.php" method="post">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9+\-\s()]{7,20}">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required>

        <button type="submit">Sign Up</button>
    </form>
    <div class="link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>
<?php if (isset($_SESSION['error'])): ?>
    <script>
        alert("<?php echo addslashes($_SESSION['error']); ?>");
    </script>
    <?php unset($_SESSION['error']); // Clear the error after displaying it ?>
<?php endif; ?>
<?php include 'component/footer.php'; ?>

</body>
</html>
