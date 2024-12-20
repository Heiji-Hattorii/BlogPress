<?php
$server = "localhost";
$user = "root";
$password = "";
$dbname = "blogpress";

try {
    $connexion = new PDO("mysql:host=$server;dbname=$dbname", $user, $password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $connexion->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) { 
            header("Location: dash.php");
            exit;
        } else {
            echo "Email ou mot de passe incorrect.";
        }
    }
} catch (PDOException $message) {
    echo 'Il y a un problème ! ' . $message->getMessage();
}       
?>

<!DOCTYPE html>
<head>
<title>Login</title>
<style>
            body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: grid;
            grid-template-columns: 100%;
            align-items: center;
            justify-content: center;
            /* background-image: url(img/abstract-digital-grid-black-background.jpg); */
            background-size: cover;
            background-position: left;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            width: 450px;
            border-radius: 20px;
            justify-self: center;
        }
        .content {
            margin: 15px 50px;
        }
        .btn {
            cursor: pointer;
            margin: 25px 50px 10px 50px;
            border-radius: 20px;
            border: none;
            height: 35px;
            width: 30%;
            background-color: rgb(41, 144, 13);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: rgb(34, 120, 11);
            transform: scale(1.05);
        }
        input {
            border-radius: 20px;
            border: 1.5px solid #ccc;
            height: 30px;
            width: 80%;
            padding-left: 15px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        input:hover {
            border-color: rgb(41, 144, 13);
        }
        fieldset {
            background: rgba(255, 255, 255, 0.549); 
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            border: 2px solid rgb(41, 144, 13);
            padding: 20px;
        }
        legend {
            font-size: 1.2rem;
            font-weight: bold;
            color: rgb(41, 144, 13);
        }
</style>
</head>
<body>
    <section class="container">
        <form method="post">
            <fieldset>
                <legend>Login</legend>
                <div class="content">
                <input name="email" placeholder="Email" type="email" required>
            </div>
            <div class="content">
                <input  name="password" placeholder="Password" type="password" required>
            </div>
            <div>
                <button class="btn" type="submit">Login</button>
            </div>
            </fieldset>
        </form>
    </section>
</body>
</html>