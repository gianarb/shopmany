<?php
namespace App\Service;

use App\Model\Item;
use \PDO;

class ItemService {

    private $pdo;

    public function __construct($hostname, $username, $password, $dbname) {
        $this->pdo = new PDO ("mysql:host=$hostname;port=3306;dbname=$dbname", $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * list returns all the items
     */
    public function list() {
        $q = $this->pdo->query("SELECT * FROM item");
        $items = [];
        while ($row = $q->fetch()) {
            $i = new Item();
            $i->id = $row[0];
            $i->name = $row[1];
            $i->description = $row[2];
            $i->price = $row[3];
            $items[] = $i;
        }
        return $items;
    }
}
