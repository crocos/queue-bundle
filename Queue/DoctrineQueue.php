<?php

namespace Crocos\QueueBundle\Queue;

use Crocos\QueueBundle\Exception\QueueException;
use Doctrine\DBAL\Driver\Connection as DriverConnection;

/**
 * DoctrineQueue.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class DoctrineQueue implements QueueInterface
{
    private $tableName;
    private $con;

    /**
     * Constructor.
     *
     * @param string $tableName
     * @param DriverConnection $con
     */
    public function __construct($tableName, DriverConnection $con)
    {
        $this->tableName = $tableName;
        $this->con = $con;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue($message, $priority = 10)
    {
        $query = "INSERT INTO {$this->tableName} (message, priority) VALUES (:message, :priority)";
        $stmt = $this->con->prepare($query);
        $stmt->execute(array(
            ':message'  => $message,
            ':priority' => $priority,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->con->beginTransaction();

        // prepare statements
        if (!isset($this->select) || !isset($this->delete)) {
            $sql = "SELECT id, message, priority FROM {$this->tableName} ORDER BY priority DESC, id LIMIT 1 FOR UPDATE";
            $this->select = $this->con->prepare($sql);

            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $this->delete = $this->con->prepare($sql);
        }

        try {
            $this->select->execute();

            if (false === ($row = $this->select->fetch(\PDO::FETCH_ASSOC))) {
                $this->con->rollback();

                return;
            }

            $this->delete->execute(array(':id' => $row['id']));

            $this->con->commit();
        } catch (\Exception $e) {
            $this->con->rollback();

            throw new QueueException(sprintf('Database error: %s: %s (id: %s)',
                $e->getCode(), $e->getMessage(), isset($row) ? $row['id'] : 'NULL'), 0, $e);
        }

        return $row['message'];
    }

    /**
     * {@inheritDoc}
     */
    public function reconnect()
    {
        unset($this->select, $this->delete);

        $this->con->close();
        $this->con->connect();
    }
}
