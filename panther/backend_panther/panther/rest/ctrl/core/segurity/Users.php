<?php
require_once __DIR__ . '/../../../cxn/Connection.php';
require_once __DIR__ . '/../../../view/ViewJSON.php';
require_once __DIR__ . '/UserAction.php';
class Users extends Request
{
    const NAME_TABLE = "j4user";

    private $view;
    private $db;

    public static function init()
    {
        parent::$nameTable = self::NAME_TABLE;
        parent::$queryInsert = INSERT_USER;
        parent::$queryUpdate = UPDATE_USER;
    }

    public function __construct()
    {
        $this->view = new ViewJSON();
        $this->db = Connection::getInstance()->getConnection();
    }

    // =======================================================
    // INSERTAR USUARIO (PARAMETROS)
    // =======================================================
    public function insertParameter($object, $statement)
    {
        $encryptedPass = UserAction::encryptPassword($object->password);
        $keyApi = UserAction::getKeyAPI();
        $roles = isset($object->roles) ? intval($object->roles) : 2;

        $statement->bindParam(1, $object->user);
        $statement->bindParam(2, $encryptedPass);
        $statement->bindParam(3, $keyApi);
        $statement->bindParam(4, $roles);
    }

    public function updateParameter($object, $statement, $id)
    {
        $encryptedPass = UserAction::encryptPassword($object->password);
        $keyApi = UserAction::getKeyAPI();
        $roles = isset($object->roles) ? intval($object->roles) : 2;

        $statement->bindParam(1, $encryptedPass);
        $statement->bindParam(2, $keyApi);
        $statement->bindParam(3, $roles);
        $statement->bindParam(4, $id);
    }

    // =======================================================
    // LOGIN
    // =======================================================
    public static function login()
    {
        try {
            $userLogin = json_decode(file_get_contents("php://input"));

            if (!$userLogin || !isset($userLogin->user) || !isset($userLogin->password)) {
                return new ContentBody(400, 400, "Debe enviar usuario y contraseña.");
            }

            $instance = new self();
            $userBD = UserAction::authenticate($userLogin->user, $userLogin->password);

            if ($userBD != null) {
                return new ContentBody(200, 200, [
                    "user" => $userBD['user'],
                    "roles" => $userBD['roles'],
                    "keyAPI" => $userBD['keyAPI']
                ]);
            } else {
                return new ContentBody(403, 403, "Usuario o contraseña incorrecta");
            }

        } catch (Exception $e) {
            return new ContentBody(500, 500, "Error: " . $e->getMessage());
        }
    }


    // =======================================================
    // REGISTRO
    // =======================================================
    public function register()
    {
        $body = json_decode(file_get_contents("php://input"));

        if (!$body || !isset($body->user) || !isset($body->password)) {
            $cb = new ContentBody(400, 400, "Debe enviar usuario y contraseña.");
            $this->view->viewPrint($cb);
        }

        $roles = isset($body->roles) ? intval($body->roles) : 2;

        $sqlCheck = "SELECT id FROM j4user WHERE user = :user AND dateDelete IS NULL LIMIT 1";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindParam(":user", $body->user);
        $stmtCheck->execute();

        if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            $cb = new ContentBody(409, 409, "El usuario ya existe.");
            $this->view->viewPrint($cb);
        }

        $body->password = UserAction::encryptPassword($body->password);
        $body->keyApi = UserAction::getKeyAPI();

        try {
            $sqlInsert = "INSERT INTO j4user(user,password,keyAPI,roles) VALUES(:user,:password,:keyApi,:roles)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->bindParam(":user", $body->user);
            $stmtInsert->bindParam(":password", $body->password);
            $stmtInsert->bindParam(":keyApi", $body->keyApi);
            $stmtInsert->bindParam(":roles", $roles);

            if ($stmtInsert->execute()) {
                $id = $this->db->lastInsertId();
                $cb = new ContentBody(201, 201, [
                    "message" => "Usuario registrado correctamente",
                    "id" => $id
                ]);
                $this->view->viewPrint($cb);
            } else {
                $errorInfo = $stmtInsert->errorInfo();
                $cb = new ContentBody(500, 500, "Error BD: " . $errorInfo[2]);
                $this->view->viewPrint($cb);
            }

        } catch (Exception $e) {
            $cb = new ContentBody(500, 500, "Error al registrar: " . $e->getMessage());
            $this->view->viewPrint($cb);
        }
    }

}
