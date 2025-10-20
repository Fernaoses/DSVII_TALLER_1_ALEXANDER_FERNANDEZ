<?php
require_once "config_pdo.php";

$mensaje = "";

try {
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if($id > 0){
            $sql = "DELETE FROM usuarios WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            if(!$stmt){
                throw new Exception("Error al preparar la consulta.");
            }

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            if ($stmt->errorCode() !== '00000') {
                throw new Exception("Error en la consulta: " . $stmt->errorInfo()[2]);
            }

            if($stmt->rowCount() > 0){
                $mensaje = "Usuario eliminado con éxito.";
            } else {
                $mensaje = "No se pudo eliminar el usuario. Verifique el ID ingresado.";
            }

            $stmt = null;
        } else {
            $mensaje = "Debe proporcionar un ID válido.";
        }
    }
} catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
}

$pdo = null;
?>

<?php if(!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>ID</label><input type="number" name="id" min="1" required></div>
    <input type="submit" value="Eliminar Usuario">
</form>