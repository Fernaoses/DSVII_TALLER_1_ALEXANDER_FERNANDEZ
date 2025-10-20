<?php
require_once "config_mysqli.php";

$mensaje = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if($id > 0){
        $sql = "DELETE FROM usuarios WHERE id = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $id);

            if(mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0){
                $mensaje = "Usuario eliminado con éxito.";
            } else {
                $mensaje = "No se pudo eliminar el usuario. Verifique el ID ingresado.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "ERROR: No se pudo preparar la consulta. " . mysqli_error($conn);
        }
    } else {
        $mensaje = "Debe proporcionar un ID válido.";
    }
}

mysqli_close($conn);
?>

<?php if(!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>ID</label><input type="number" name="id" min="1" required></div>
    <input type="submit" value="Eliminar Usuario">
</form>