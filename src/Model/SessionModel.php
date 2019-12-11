<?php
namespace Src\Model;


use Src\Controller\Response;
use Src\System\SessionException;

class SessionModel {
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function find($username)
    {
        $sql = "SELECT id, username, email, password, is_active, login_attempts FROM user WHERE username = :username OR email = :email";
        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':username', $username, \PDO::PARAM_STR);
            $query->bindParam(':email', $username, \PDO::PARAM_STR);
            $query->execute();
            return (object) [
                'rows_affected' => $query->rowCount(),
                'user' => $query->fetch(\PDO::FETCH_OBJ)
            ];
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    public function incrementLoginAttempts($user_id)
    {
        $sql = "UPDATE user SET login_attempts = login_attempts + 1 WHERE id = :id";
        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $user_id, \PDO::PARAM_INT);
            $query->execute();
            return (object) [
                'rows_affected' => $query->rowCount()
            ];
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    public function createSession($user_id, $access_token, $access_token_expiry_seconds, $refresh_token, $refresh_token_expiry_seconds)
    {
        try {
            $this->db->beginTransaction();

            // Reset the login attempts
            $sql = "UPDATE user SET login_attempts = 0 WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $user_id, \PDO::PARAM_INT);
            $query->execute();

            // Create the new database session
            $sql = "INSERT INTO user_session
                        (user_id, access_token, access_token_expiry, refresh_token, refresh_token_expiry) 
                    VALUES 
                        (:user_id, :access_token, date_add(NOW(), INTERVAL :access_token_expiry_seconds SECOND), :refresh_token, date_add(NOW(), INTERVAL :refresh_token_expiry_seconds SECOND))
            ";
            $query = $this->db->prepare($sql);
            $query->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $query->bindParam(':access_token', $access_token, \PDO::PARAM_STR);
            $query->bindParam(':access_token_expiry_seconds', $access_token_expiry_seconds, \PDO::PARAM_INT);
            $query->bindParam(':refresh_token', $refresh_token, \PDO::PARAM_STR);
            $query->bindParam(':refresh_token_expiry_seconds', $refresh_token_expiry_seconds, \PDO::PARAM_INT);
            $query->execute();

            // Get the session ID
            $sessionID = $this->db->lastInsertId();

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse(["There was an issue logging in, please try again"], 500);
        }

        $sql = "SELECT id AS session_id, user_id, access_token, access_token_expiry, refresh_token, refresh_token_expiry 
                FROM user_session
                WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $sessionID, \PDO::PARAM_INT);
        $query->execute();
        return (object) [
            'rows_affected' => 1,
            'user' => $query->fetch(\PDO::FETCH_OBJ)
        ];
    }

}