<?php

namespace Services;

/**
 * SessionStorage
 *
 */
class SessionStorage implements \Nette\Http\ISessionStorage
{
  /**
     * Holds the database connection
     * @var \dibiConnection
     */
    private $conn = null;

    public  function open($savePath, $sessionName) {
        if (is_null($this->conn)) {
            $this->conn = \dibi::connect(array('driver' => 'sqlite', 'database' => $savePath));
         //   \dibi::activate(MainConnectionName);
        };
    }

    public  function read($id) {
        if (is_null($this->conn)) {
            throw new \Nette\InvalidStateException('The connection to database for session storage is not open!');
        };

        $query = '
            SELECT
                [data]
            FROM [session]
            WHERE
                [id] = %s';
        try {
            $result = $this->conn->query($query, $id);
            return $result->fetchSingle();
        } catch (\Exception $e) {

            $this->conn->query('CREATE TABLE [session] ([id] varchar(32) not null primary key, [timestamp] timestamp not null, [data] text)');
            $this->conn->query('CREATE INDEX [session_by_timestamp] ON [session] ([timestamp])');

            return '';
        };
    }

    public  function write($id, $data) {
        if (is_null($this->conn)) {
            throw new \Nette\InvalidStateException('The connection to database for session storage is not open!');
        };

        $this->conn->begin();
        $this->conn->query('DELETE FROM [session] WHERE [id] = %s', $id);
        $this->conn->query('INSERT INTO [session] VALUES(%s, %s, %s)', $id, time(), $data);
        $this->conn->commit();
    }

    public  function destroy($id) {
        if (is_null($this->conn)) {
            throw new \Nette\InvalidStateException('The connection to database for session storage is not open!');
        };

        $this->conn->query('DELETE FROM [session] WHERE [id] = %s', $id);
    }

    public  function clean($max) {
        if (is_null($this->conn)) {
            throw new \Nette\InvalidStateException('The connection to database for session storage is not open!');
        };

        $old = (time() - $max);
        $this->conn->query('DELETE FROM [session] WHERE [timestamp] < %s', $old);
    }

    public  function close() {
        $this->conn = null;
    }

        public function remove($id) {

        }

}