<?php

namespace SolarSystem;

use Exception;
use PDO;
use Psr\Log\LoggerInterface;


class Import
{
    private $logger;
    private $db;

    public function __construct(LoggerInterface $logger, PDO $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Determines what is being asked to be updated
     *
     * @param  array $post $_POST data passed
     *
     * @return boolean       true?
     */
    public function run($post)
    {
        // Checks if $_POST was empty or not
        if (empty($post)) {
            $this->logger->error("Empty update attempted, or no POST data was given");
            return false;
        }

        // Converts the post data to an object for easier handling
        $post = (object)$post;

        // If not, find out what the update type was
        switch ($post->update_type) {
            case 'temperature':
                $this->temperature($post);
                break;

            default:
                $this->logger->error("$post->uuid tried to update $post->update_type, but wasn't valid");
                return false;
                break;
        }
        return true;
    }


    private function temperature($post)
    {
        try {

            if (!$this->checkAuth($post->uuid, $post->key)) {
                $this->logger->error("$post->uuid tried to update, but the key didn't match");
                exit();
            }

            $this->db->beginTransaction();
            $results = $this->importTemperature($post);
            $this->db->commit();

            $this->logger->info("Temp Update for $post->uuid's $post->chip_name");
            $this->logger->info("Temp$post->temp_number | Input: $post->temp_input");
            $this->logger->info("Temp$post->temp_number | Max: $post->temp_max");
            $this->logger->info("Temp$post->temp_number | Crit: $post->temp_crit");
            return true;

        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->logger->error($exception->getMessage());
            return false;
        }
    }

    private function checkAuth($uuid, $key)
    {
        $query = $this->db->prepare("
            SELECT p.key
            FROM planets p
            WHERE uuid = :uuid;
        ");
        $query->execute([
            ":uuid" => "$uuid"
        ]);
        $count = $query->rowCount();

        if ($count === 0) {
            $this->logger->error("$uuid was not found in the list of planets");
            throw new Exception("$uuid was not found in the list of planets", 1);
        }

        $result = $query->fetchAll();

        if ($result[0]->key !== $key) {
            return false;
        }

        return true;
    }

    private function importTemperature($post)
    {
        $query = $this->db->prepare("
            INSERT INTO planet_temperatures (
                uuid,
                chip_name,
                temp_number,
                display_name,
                last_update,
                adapter,
                temp_input,
                temp_max,
                temp_crit
            ) VALUES (
                ?,?,?,?,NOW(),?,?,?,?
            ) ON DUPLICATE KEY UPDATE
                last_update = VALUES(last_update),
                temp_input = VALUES(temp_input),
                temp_max = VALUES(temp_max),
                temp_crit = VALUES(temp_crit)
        ");
        $query->execute([
            $post->uuid,
            $post->chip_name,
            $post->temp_number,
            $post->display_name,
            $post->adapter,
            $post->temp_input,
            $post->temp_max,
            $post->temp_crit
        ]);

        return $this->db->errorInfo();
    }
}