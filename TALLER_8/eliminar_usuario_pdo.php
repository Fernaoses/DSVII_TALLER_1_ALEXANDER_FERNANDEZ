<?php
require_once "config_pdo.php";

$mensaje = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if($id > 0){
        $sql = "DELETE FROM usuarios WHERE id = :id";

        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if($stmt->execute() && $stmt->rowCount() > 0){
                $mensaje = "Usuario eliminado con éxito.";
            } else {
                $mensaje = "No se pudo eliminar el usuario. Verifique el ID ingresado.";
            }

            $stmt = null;
        } else {
            $mensaje = "ERROR: No se pudo preparar la consulta.";
        }
    } else {
        $mensaje = "Debe proporcionar un ID válido.";
    }
}

$pdo = null;
?>

<?php if(!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>ID</label><input type="number" name="id" min="1" required></div>
    <input type="submit" value="Eliminar Usuario">
</form>