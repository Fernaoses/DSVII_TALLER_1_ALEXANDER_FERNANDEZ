<?php
require_once "config_mysqli.php";

$mensaje = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conn, $_POST['nombre']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';

    if($id > 0 && !empty($nombre) && !empty($email)){
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssi", $nombre, $email, $id);

            if(mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0){
                $mensaje = "Usuario actualizado con éxito.";
            } else {
                $mensaje = "No se pudo actualizar el usuario. Verifique el ID ingresado.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "ERROR: No se pudo preparar la consulta. " . mysqli_error($conn);
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

mysqli_close($conn);
?>

<?php if(!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>ID</label><input type="number" name="id" min="1" required></div>
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Actualizar Usuario">
</form>