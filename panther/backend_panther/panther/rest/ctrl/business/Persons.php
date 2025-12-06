<?php
require_once 'model/business/Person.php';
require_once 'querys/business/PersonQuery.php';
require_once 'view/ViewJSON.php';
require_once 'cxn/Connection.php';

class Persons
{
    private $view;

    public function init()
    {
        $this->view = new ViewJSON();
    }

    // ====================================================
    // GET → LISTAR PERSONAS
    // ====================================================
    public function get($request)
    {
        try {
            $conn = Connection::getInstance()->getConnection();

            if (!empty($request[0])) {
                $stmt = $conn->prepare(PersonQuery::getById());
                $stmt->execute([$request[0]]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conn->query(PersonQuery::getAll());
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "data" => $data
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // POST → CREAR PERSONA
    // ====================================================
    public function post($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);

            if (!$body || empty($body["name"]) || empty($body["lastName"])) {
                throw new Exception("Faltan datos obligatorios: name y lastName");
            }

            $conn = Connection::getInstance()->getConnection();
            $stmt = $conn->prepare(PersonQuery::insert());
            $stmt->execute([
                $body["name"],
                $body["lastName"],
                $body["phone"],
                $body["document_type_id"],
                $body["country_id"],
                $body["state_id"],
                $body["city_id"]
            ]);

            return $this->view->response([
                "state" => "OK",
                "code" => 201,
                "message" => "Persona creada correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // PUT → EDITAR PERSONA
    // ====================================================
    public function put($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);
            $id = $body["id"] ?? ($request[0] ?? null);

            if (!$id) {
                throw new Exception("ID requerido");
            }

            $conn = Connection::getInstance()->getConnection();

            // Verificar existencia
            $check = $conn->prepare("SELECT id FROM person WHERE id = ?");
            $check->execute([$id]);

            if ($check->rowCount() === 0) {
                throw new Exception("La persona no existe");
            }

            // Actualizar
            $stmt = $conn->prepare(PersonQuery::update());
            $stmt->execute([
                $body["name"],
                $body["lastName"],
                $body["phone"],
                $body["document_type_id"],
                $body["country_id"],
                $body["state_id"],
                $body["city_id"],
                $id
            ]);

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "message" => "Persona actualizada correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // DELETE → ELIMINAR PERSONA
    // ====================================================
    public function delete($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);
            $id = $body["id"] ?? ($request[0] ?? null);

            if (!$id) {
                throw new Exception("ID requerido");
            }

            $conn = Connection::getInstance()->getConnection();

            // Verificar existencia
            $check = $conn->prepare("SELECT id FROM person WHERE id = ?");
            $check->execute([$id]);

            if ($check->rowCount() === 0) {
                throw new Exception("La persona no existe");
            }

            // Eliminar (borrado lógico o físico según tu query)
            $stmt = $conn->prepare(PersonQuery::delete());
            $stmt->execute([$id]);

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "message" => "Persona eliminada correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }
}
