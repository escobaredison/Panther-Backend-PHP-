<?php
/**
 * <b>Descripcion:</b> Clase que <br/> contiene las consultas de la aplicación
 * <b>Caso de Uso:</b> PANTHER-Seguridad <br/>
 *
 * @author Josué Nicolás Pinzón Villamil <a href = "mailto:jpinzon@j4sysol.com">jpinzon@j4sysol.com</a>
 */

/**
 * Constante de consultas base de datos
 */
define("INSERT_USER", "INSERT INTO j4user(user,password,keyAPI,roles) VALUES(?,?,?,?);");
define("UPDATE_USER", "UPDATE j4user SET password=?, keyAPI=?, roles=? WHERE id=?;");
define("SELECT_USER", "SELECT id, user, password, keyAPI, roles FROM j4user WHERE user = ?;");
define("VERIFY_KEYAPI", "SELECT COUNT(user) FROM j4user WHERE keyAPI=?");

define("INSERT_ROL", "INSERT INTO j4rol(name, description) VALUES (?,?);");
define("UPDATE_ROL", "UPDATE j4rol SET name=?, description=? WHERE id=?;");

class SecurityQuery
{
    private $connection;

    public function __construct()
    {
        $cx = new LoginMysql();
        $this->connection = $cx->getConnection();
    }

    /**
     * Consulta usuario por nombre
     */
    public function getUserByUsername($username)
    {
        $sql = SELECT_USER;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica KeyAPI
     */
    public function verifyKeyAPI($keyApi)
    {
        $stmt = $this->connection->prepare(VERIFY_KEYAPI);
        $stmt->bindParam(1, $keyApi);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>