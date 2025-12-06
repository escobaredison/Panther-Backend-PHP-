<?php

/**
 * <b>Descripcion:</b> Clase que <br/>Gestiona la seguridad de un usuario
 * <b>Caso de Uso:</b> PANTHER-Seguridad <br/>
 *
 * @author Josué Nicolás Pinzón Villamil <a href = "mailto:jpinzon@j4sysol.com">jpinzon@j4sysol.com</a>
 */
class UserAction implements IRequest
{

    /**
     * Constante de metodo Login
     * 
     * @var string
     */
    const LOGIN = "login";

    /**
     * {@inheritdoc}
     * @see IRequest::init()
     */
    public function init()
    {
    }

    /**
     * {@inheritdoc}
     * @see IRequest::get()
     */
    public function get()
    {
        $user = $_GET["user"] ?? null;

        if (!$user) {
            throw new ExcepcionApi(BAD_REQUEST, ST400, "Debe enviar el usuario");
        }

        // SELECT debe incluir filtro de usuarios activos
        $query = "SELECT id, username, email, role, created_at 
            FROM users 
            WHERE username = ? AND status = 1";

        $connection = Connection::getInstance()->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam(1, $user);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new ContentBody(OK, ST200, $result);
        }

        return new ContentBody(NOT_FOUND, ST404, "Usuario no encontrado o eliminado");
    }


    /**
     * {@inheritdoc}
     * @see IRequest::delete()
     */
    public function delete()
    {
        $id = $_GET["id"] ?? null;

        if (!$id) {
            throw new ExcepcionApi(BAD_REQUEST, ST400, "Debe enviar id");
        }

        $stmt = Connection::getInstance()->getConnection()
            ->prepare("DELETE FROM j4user WHERE id=?");
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return new ContentBody(OK, ST200, "Usuario eliminado");
        }

        throw new ExcepcionApi(BAD_REQUEST, ST400, "Error eliminando usuario");
    }

    /**
     * {@inheritdoc}
     * @see IRequest::put()
     */
    public function put($request)
    {
        try {
            $id = $request[0] ?? null;
            $data = JSONUtil::decodeJSON();

            $password = isset($data->password) ? self::encrytPassword($data->password) : null;
            $keyAPI = self::getKeyAPI();
            $roles = $data->roles ?? "USER";

            $stmt = Connection::getInstance()->getConnection()->prepare(UPDATE_USER);
            $stmt->bindParam(1, $password);
            $stmt->bindParam(2, $keyAPI);
            $stmt->bindParam(3, $roles);
            $stmt->bindParam(4, $id);

            if ($stmt->execute()) {
                return new ContentBody(OK, ST200, "Usuario actualizado");
            }

            throw new ExcepcionApi(BAD_REQUEST, ST400, "Error actualizando");
        } catch (Exception $e) {
            throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }


    /**
     * Metodo de logueo para un usuario
     *
     * @param unknown $request
     *            Datos de credenciales
     * @throws ExcepcionApi Lanza una excepcion si no encuetra ek metodo
     * @return ContentBody Retorna una respuesta de la solicitud
     */
    public function post($request)
    {
        if (empty($request[0])) {
            throw new ExcepcionApi(BAD_REQUEST, ST400, "Debe enviar la acción");
        }

        switch ($request[0]) {

            case "login":
                // 🚨 IMPORTANTE: NO LLAMAR NI A REQUEST::POST NI A INSERT
                return UserAction::login();

            case "register":
                // 🚨 Evitar que Panther haga insert automático
                return self::register();

            default:
                throw new ExcepcionApi(BAD_REQUEST, ST400, "Acción POST no válida");
        }
    }


    /**
     * Otorga los permisos a un usuario para que acceda a los recursos
     *
     * @return null o el id del usuario autorizado
     * @throws Exception
     */
    public static function authenticator()
    {
        $pathInfo = isset($_GET['PATH_INFO']) ? trim($_GET['PATH_INFO'], '/') : '';
        $segments = array_values(array_filter(explode('/', $pathInfo)));

        $resource = strtolower($segments[0] ?? '');
        $action = strtolower($segments[1] ?? '');

        // Login y Register NO requieren token
        if ($resource === 'users' && in_array($action, ['login', 'register'])) {
            return true;
        }

        // Para los demás servicios sí requiere token
        $heads = apache_request_headers();
        $body = JSONUtil::decodeJSON();

        if (isset($heads['Authorization'])) {
            $keyAPI = $heads['Authorization'];
        } elseif (isset($body->token)) {
            $keyAPI = $body->token;
        } else {
            throw new ExcepcionApi(BAD_REQUEST, ST400, "Se requiere Token para autenticar");
        }

        if (UserAction::validateKeyAPI($keyAPI)) {
            return true;
        } else {
            throw new ExcepcionApi(UNAUTHORIZED, ST401, "Token no válido");
        }
    }


    /**
     * Verifica en base de datos si las credenciales son correctas
     *
     * @throws ExcepcionApi Lanza una excepcion si encuetra un error
     * @return ContentBody Respesta de la solicitud
     */
    public static function login()
    {
        try {
            $userLogin = JSONUtil::decodeJSON();
            $instance = new self();
            $userBD = $instance->authenticate($userLogin->user, $userLogin->password);
            if ($userBD != NULL) {
                $user = new User();
                $user->user = $userBD['user'];
                $user->roles = $userBD['roles'];
                $user->keyAPI = $userBD['keyAPI'];
                $bodyAnswer = new ContentBody(OK, ST200, $user);
                return $bodyAnswer;
            } else {
                $bodyAnswer = new ContentBody(FORBIDDEN, ST403, noAutheticate);
                return $bodyAnswer;
            }
        } catch (Exception $e) {
            throw new ExcepcionApi(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }
    public static function register()
    {
        try {
            $data = JSONUtil::decodeJSON();

            $user = $data->user ?? '';
            $password = UserAction::encryptPassword($data->password);
            $roles = isset($data->roles) ? intval($data->roles) : 2;
            $keyAPI = self::getKeyAPI();

            $conn = Connection::getInstance()->getConnection();

            $stmt = $conn->prepare("INSERT INTO j4user(user,password,keyAPI,roles) VALUES(?,?,?,?)");
            $stmt->bindParam(1, $user, PDO::PARAM_STR);
            $stmt->bindParam(2, $password, PDO::PARAM_STR);
            $stmt->bindParam(3, $keyAPI, PDO::PARAM_STR);
            $stmt->bindParam(4, $roles, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return new ContentBody(OK, ST200, "Usuario registrado correctamente");
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Error BD: " . $errorInfo[2] . " (SQLSTATE: " . $errorInfo[0] . ")");
            }

        } catch (Exception $e) {
            // Mostrar error real para depuración
            return new ContentBody(INTERNAL_SERVER_ERROR, ST500, $e->getMessage());
        }
    }

    /**
     * Verifica en base de datos si las credenciales son correctas
     *
     * @param unknown $userA
     *            Usuario a verificar
     * @param unknown $passwordPlain
     *            Contraseña Plana
     * @return mixed|NULL respuesta de la verificación
     */
    public static function authenticate($userA, $passwordPlain)
    {
        $query = SELECT_USER;

        $statement = Connection::getInstance()->getConnection()->prepare($query);
        $statement->bindParam(1, $userA);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Usuario no existe
            return null;
        }

        if (password_verify($passwordPlain, $user["password"])) {
            return $user;
        } else {
            return null;
        }
    }


    /**
     * Protege la contraseña con un algoritmo de encriptado
     *
     * @param unknown $passwordPlain
     * @return string|NULL
     */
    public static function encryptPassword($passwordPlain)
    {
        if (!empty($passwordPlain))
            return password_hash($passwordPlain, PASSWORD_BCRYPT);
        else
            return null;
    }

    /**
     * Asigna de forma aleatoria una clave para la aplicación
     *
     * @return string
     */
    public static function getKeyAPI()
    {
        return md5(microtime() . rand());
    }

    /**
     * Comprueba la existencia de la clave para la api
     *
     * @param
     *            $keyAPI
     * @return bool true si existe o false en caso contrario
     */
    public static function validateKeyAPI($keyAPI)
    {
        $query = VERIFY_KEYAPI;
        $statement = Connection::getInstance()->getConnection()->prepare($query);
        $statement->bindParam(1, $keyAPI);
        $statement->execute();
        return $statement->fetchColumn(0) > 0;
    }


}