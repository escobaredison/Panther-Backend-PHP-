<?php
class PersonQuery
{
    public static function getAll()
    {
        // Trae todas las personas activas
        return "SELECT id, name, lastName, phone, document_type_id, country_id, state_id, city_id 
                FROM person 
                WHERE dateDelete IS NULL";
    }

    public static function getById()
    {
        return "SELECT id, name, lastName, phone, document_type_id, country_id, state_id, city_id 
                FROM person 
                WHERE id=? AND dateDelete IS NULL";
    }

    public static function insert()
    {
        // Ya definida como constante INSERT_PERSON, pero puedes usarla aquí también
        return INSERT_PERSON;
    }

    public static function update()
    {
        // Ya definida como constante UPDATE_PERSON
        return UPDATE_PERSON;
    }

    public static function delete()
    {
        // Si quieres borrado lógico:
        return "UPDATE person SET dateDelete = NOW() WHERE id=?";
        // Si prefieres borrado físico:
        // return "DELETE FROM person WHERE id=?";
    }
}
