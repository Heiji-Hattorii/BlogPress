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
        $confirmPassword = $_POST["confirmPassword"] ?? '';

        // Vérifier si les mots de passe correspondent
        if ($password !== $confirmPassword) {
            $error = "Les mots de passe ne correspondent pas !";
        } else {
            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Préparer et exécuter la requête d'insertion
            $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
            $stmt = $connexion->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();

            $success = "Inscription réussie !";
        }
    }
} catch (PDOException $message) {
    echo 'Il y a un problème ! ' . $message->getMessage();
}
?>

<!DOCTYPE html>
<head>
<title>Sign Up</title>
<style>
    body {
        margin: 0;
        padding: 0;
        height: 100vh;
        background-size: cover;
        background-position: left;
        font-family: Arial, sans-serif;
        color: #333;
    }
    .container {
        width: 450px;
        border-radius: 20px;
        justify-self: center;
        margin-top: 50px;
    }
    .content {
        margin: 15px 50px;
        position:relative;
    }
    .icone {
        position:absolute;
        width:18px;
        cursor: pointer;
        margin:6px 0 0 -30px;
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
    <div class="container">
        <form method="POST">
            <fieldset>
                <legend>Sign Up</legend>

                <!-- Afficher les messages d'erreur ou de succès -->
                <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
                <?php if (isset($success)) { echo "<p style='color:green;'>$success</p>"; } ?>

                <div class="content">
                    <input name="email" placeholder="Email" type="email" required>
                </div>
                <div class="content">
                    <input name="password" class="ps" placeholder="Password" type="password" required>
                    <img class="icone" src="./img/icons8-closed-eye-48.png" alt="">
                </div>
                <div class="content">
                    <input name="confirmPassword" class="psw" placeholder="Confirm Password" type="password" required>
                    <img class="icone" src="./img/icons8-closed-eye-48.png" alt="">
                </div>
                <div>
                    <button class="btn" type="submit">Sign Up</button>
                </div>
            </fieldset>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let btn = document.querySelector(".btn");
            btn.addEventListener("click", (e) => {
                let formulaire = document.getElementsByTagName("fieldset")[0];
                let ps = document.querySelector(".ps").value;
                let psw = document.querySelector(".psw").value;
                
                if ((ps !== psw) && (psw.trim() !== "")) {
                    e.preventDefault();
                    let remarque = document.createElement("h6");
                    remarque.style.color = "red";
                    remarque.textContent = "Les mots de passe ne sont pas identiques !";
                    formulaire.appendChild(remarque);
                }
            });

            let icone = document.querySelector(".icone");
            let iconee = document.querySelectorAll(".icone")[1];
            const src1 = "./img/icons8-eye-48.png";
            const src2 = "./img/icons8-closed-eye-48.png";

            icone.addEventListener("click", (e) => {
                let ps = document.querySelector(".ps");
                if (icone.getAttribute("src") === src1) {
                    icone.setAttribute("src", src2);
                    ps.setAttribute("type", "password");
                } else {
                    icone.setAttribute("src", src1);
                    ps.setAttribute("type", "text");
                }
            });

            iconee.addEventListener("click", (e) => {
                let psw = document.querySelector(".psw");
                if (iconee.getAttribute("src") === src1) {
                    iconee.setAttribute("src", src2);
                    psw.setAttribute("type", "password");
                } else {
                    iconee.setAttribute("src", src1);
                    psw.setAttribute("type", "text");
                }
            });
        });
    </script>
</body>
</html>
