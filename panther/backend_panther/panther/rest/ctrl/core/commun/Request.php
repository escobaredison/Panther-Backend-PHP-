<?php

/**
 * <b>Descripcion:</b> Clase que <br/>Gestiona las solicitudes tipo CRUD de un elemento
 * <b>Caso de Uso:</b> PANTHER-Seguridad <br/>
 *
 * @author Josué Nicolás Pinzón Villamil <a href = "mailto:jpinzon@j4sysol.com">jpinzon@j4sysol.com</a>
 */
abstract class Request
{

    /**
     * Nombre de la tabla del negocio
     *
     * @var string
     */
    protected static $nameTable = "table";

    /**
     * Comando para insertar en base de datos
     *
     * @var string
     */
    protected static $queryInsert = "insert";

    /**
     * Comando para insertar en base de datos
     *
     * @var string
     */
    protected static $queryUpdate = "update";

    /**
     * Función que permite insertar dinamicamente el nombre de la tabla
     */
    abstract protected static function init();

    /**
     * Inserta los parametros en el statement y realiza
     * validaciones previas antes de insertar
     *
     * @param unknown $object
     *            Objeto insertar o actualizar
     * @param unknown $statement
     *            Sentencia para ejecutar en base de datos
     */
    abstract protected function insertParameter($object, $statement);

    /**
     * Inserta los parametros en el statement y realiza
     * validaciones previas antes de insertar
     *
     * @param unknown $object
     *            Objeto insertar o actualizar
     * @param unknown $statement
     *            Sentencia para ejecutar en base de datos
     */
    abstract protected function updateParameter($object, $statement, $id);

    /**
     * Método que invoca a las funciones tipo get
     *
     * @param unknown $request
     * @return ContentBody
     */
    public static function get($request)
    {
        UserAction::authenticator();
        if (empty($request[0])) {
            return self::getRequest(null);
        } else {
            return self::getRequest($request);
        }
    }

    /**
     * Método que invoca a las funciones tipo get
     *
     * @param unknown $request
     * @return ContentBody
     */
    public function post($request)
    {
        try {
            $input = file_get_contents("php://input");
            $object = json_decode($input, true);

            if (!$object || !isset($object['user']) || !isset($object['password'])) {
                return new ContentBody(ERROR, ST400, ["message" => "Credenciales inválidas"]);
            }

            // Aquí validas contra tu tabla usuarios
            $pdo = Connection::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user=? AND dateDelete IS NULL");
            $stmt->bindParam(1, $object['user']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($object['password'], $user['password'])) {
                return new ContentBody(OK, ST200, [
                    "token" => $user['keyAPI'],
                    "roles" => $user['roles']
                ]);
            } else {
                return new ContentBody(ERROR, ST401, ["message" => "Usuario o contraseña incorrectos"]);
            }
        } catch (\Exception $e) {
            return new ContentBody(ERROR, ST500, ["message" => $e->getMessage()]);
        }
    }



    /**
     * Método que invoca a las funciones tipo put
     *
     * @param unknown $request
     * @throws ExcepcionApi
     * @return ContentBody
     */
    public function put($request)
    {
        UserAction::authenticator();
        $object = \JSONUtil::decodeJSON();
        $tempo = $this->updateRequest($object, $request[0]);

        if ($tempo > 0) {
            return $bodyAnswer = new ContentBody(OK, ST200, sucessful);
        } else {
            throw new ExcepcionApi(NO_CONTENT, ST204, error_notExist);
        }
    }

    public static function delete($request)
    {
        UserAction::authenticator();
        self::deleteRequest($request);
        return $bodyAnswer = new ContentBody(OK, ST200, sucessful);
    }

    /**
     * Ejecuta las peticiones tipo get, obteniendo
     *
     * @param unknown $id
     * @throws ExcepcionApi
     * @return ContentBody
     */
    private static function getRequest($id)
    {
        try {
            if (empty($id)) {
                $query = "SELECT * FROM " . self::$nameTable . " WHERE dateDelete IS NULL";
                $statement = Connection::getInstance()->getConnection()->prepare($query);
            } else {
                $query = "SELECT * FROM " . self::$nameTable . " WHERE id=?";
                $statement = Connection::getInstance()->getConnection()->prepare($query);
                $statement->bindParam(1, $id[0], PDO::PARAM_INT);
            }

            $statement->execute();
            $tempo = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($tempo) > 0) {
                return new ContentBody(OK, ST200, $tempo);
            } else {
                // Devuelve un JSON estándar en lugar de lanzar excepción
                return new ContentBody(NO_CONTENT, ST204, ["message" => "no_result"]);
            }
        } catch (Exception $e) {
            return new ContentBody(INTERNAL_SERVER_ERROR, ST500, ["message" => $e->getMessage()]);
        }
    }

    /**
     *
     * @param unknown $object
     * @throws ExcepcionApi
     * @return string
     */
    private function createRequest($object)
    {
        try {
            $pdo = Connection::getInstance()->getConnection();
            $query = self::$queryInsert;
            $statement = $pdo->prepare($query);

            // Aquí usamos el método de la subclase para bindParam
            $this->insertParameter($object, $statement);

            $statement->execute(); // ejecuta con los parámetros ya ligados
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }
    /**
     * Actualiza un recurso según su id
     *
     * @param float $id
     * @param unknown $object
     * @throws ExcepcionApi Lanza una excepcion si hay un error en la actualización
     * @return number Número de columna actualizada.
     */
    private function updateRequest($object, $id)
    {
        try {
            // Usar la propiedad estática correctamente
            $query = self::$queryUpdate;

            if (empty($query)) {
                throw new Exception("QueryUpdate no está definida. ¿Llamaste a init()?");
            }

            // Preparar la sentencia
            $statement = Connection::getInstance()->getConnection()->prepare($query);

            // Asignar los parámetros usando el método de la subclase
            $this->updateParameter($object, $statement, $id);

            // Ejecutar la sentencia
            $statement->execute();

            return $statement->rowCount();
        } catch (Exception $e) {
            throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }

    /**
     * Método para eliminar un elemento de forma suave
     *
     * @param unknown $id
     *            del elemento
     * @throws ExcepcionApi Lanza un error si hay problemas en la conexion
     * @return number Numero de filas Afeactadas
     */
    // private static function deleteRequest($id)
    // {
    //     try {

    //         if (empty($id[0])) {
    //             $query = "UPDATE " . self::$nameTable . " SET dateDelete = ? ";
    //             // Preparar sentencia
    //             $statement = Connection::getInstance()->getConnection()->prepare($query);
    //             $dateDelete = date('Y-m-d H:i:s');
    //             $statement->bindParam(1, $dateDelete, PDO::PARAM_STR);
    //         } else {
    //             $query = "UPDATE " . self::$nameTable . " SET dateDelete = ? WHERE id=?";
    //             // Preparar statement
    //             $statement = Connection::getInstance()->getConnection()->prepare($query);
    //             $dateDelete = date('Y-m-d H:i:s');
    //             $statement->bindParam(1, $dateDelete, PDO::PARAM_STR);
    //             $statement->bindParam(2, $id[0], PDO::PARAM_INT);
    //         }

    //         $statement->execute();

    //         return $statement->rowCount();
    //     } catch (Exception $e) {
    //         throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
    //     }
    // }

    private static function deleteRequest($id)
    {
        try {
            $pdo = Connection::getInstance()->getConnection();

            if (empty($id[0])) {
                throw new ExcepcionApi(BAD_REQUEST, ST400, "Debe indicar un ID para eliminar");
            }

            $query = "DELETE FROM " . self::$nameTable . " WHERE id=?";
            $statement = $pdo->prepare($query);
            $statement->bindParam(1, $id[0], PDO::PARAM_INT);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                return $statement->rowCount();
            } else {
                throw new ExcepcionApi(NO_CONTENT, ST204, "Registro no existe");
            }

        } catch (Exception $e) {
            throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }
}

