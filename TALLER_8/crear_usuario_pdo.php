<?php
require_once "config_pdo.php";

try {
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];

        $sql = "INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)";

        $stmt = $pdo->prepare($sql);
        if(!$stmt){
            throw new Exception("Error al preparar la consulta.");
        }

        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        $stmt->execute();
        if ($stmt->errorCode() !== '00000') {
            throw new Exception("Error en la consulta: " . $stmt->errorInfo()[2]);
        }

        echo "Usuario creado con éxito.";

        unset($stmt);
    }
} catch (Exception $e) {
    echo "Se produjo un error: " . $e->getMessage();
}

unset($pdo);
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Crear Usuario">
</form>