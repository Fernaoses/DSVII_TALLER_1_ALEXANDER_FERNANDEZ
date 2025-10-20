<?php
require_once "config_mysqli.php";

$mensaje = "";

try {
    $stmt = null;

    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conn, $_POST['nombre']) : '';
        $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';

        if($id > 0 && !empty($nombre) && !empty($email)){
            $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "ssi", $nombre, $email, $id);

            if(!mysqli_stmt_execute($stmt)){
                throw new Exception("Error en la consulta: " . mysqli_error($conn));
            }

            if(mysqli_stmt_affected_rows($stmt) > 0){
                $mensaje = "Usuario actualizado con éxito.";
            } else {
                $mensaje = "No se pudo actualizar el usuario. Verifique el ID ingresado.";
            }
        } else {
            $mensaje = "Todos los campos son obligatorios.";
        }
    }
} catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
} finally {
    if($stmt){
        mysqli_stmt_close($stmt);
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