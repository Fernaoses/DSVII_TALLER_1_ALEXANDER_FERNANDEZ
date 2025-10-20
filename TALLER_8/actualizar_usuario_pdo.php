<?php
require_once "config_pdo.php";

$mensaje = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if($id > 0 && !empty($nombre) && !empty($email)){
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id";

        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if($stmt->execute() && $stmt->rowCount() > 0){
                $mensaje = "Usuario actualizado con éxito.";
            } else {
                $mensaje = "No se pudo actualizar el usuario. Verifique el ID ingresado.";
            }

            $stmt = null;
        } else {
            $mensaje = "ERROR: No se pudo preparar la consulta.";
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

$pdo = null;
?>

<?php if(!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>ID</label><input type="number" name="id" min="1" required></div>
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Actualizar Usuario">
</form>