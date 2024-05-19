<?php

class Room {
    public $type;
    public $treasurePoints;
    public $monsterStrength;
    public $visited;

    public function __construct($type, $treasurePoints = 0, $monsterStrength = 0) {
        $this->type = $type;
        $this->treasurePoints = $treasurePoints;
        $this->monsterStrength = $monsterStrength;
        $this->visited = false;
    }
}

class Dungeon {
    public $rooms;
    public $startRoom;
    public $exitRoom;

    public function __construct($rooms, $startRoom, $exitRoom) {
        $this->rooms = $rooms;
        $this->startRoom = $startRoom;
        $this->exitRoom = $exitRoom;
    }
}

class Player {
    public $currentRoom;
    public $score;

    public function __construct($startRoom) {
        $this->currentRoom = $startRoom;
        $this->score = 0;
    }

    public function move($room) {
        $this->currentRoom = $room;
        $this->interactWithRoom();
    }

    private function interactWithRoom() {
        if ($this->currentRoom->visited) {
            return;
        }

        switch ($this->currentRoom->type) {
            case 'treasure':
                $this->score += rand(1, $this->currentRoom->treasurePoints);
                break;
            case 'monster':
                while ($this->currentRoom->monsterStrength > 0) {
                    $playerStrength = rand(1, 20);
                    if ($playerStrength >= $this->currentRoom->monsterStrength) {
                        $this->score += $this->currentRoom->monsterStrength;
                        break;
                    } else {
                        $this->currentRoom->monsterStrength -= $playerStrength;
                    }
                }
                break;
            case 'empty':
            case 'visited':
                break;
        }

        $this->currentRoom->visited = true;
    }
}

// Загрузка подземелья из внешнего источника (например, JSON файла)
function loadDungeon($filePath) {
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);

    $rooms = [];
    foreach ($data['rooms'] as $roomData) {
        $rooms[] = new Room(
            $roomData['type'],
            $roomData['treasurePoints'] ?? 0,
            $roomData['monsterStrength'] ?? 0
        );
    }

    return new Dungeon($rooms, $data['startRoom'], $data['exitRoom']);
}

// Обработка входных данных
$dungeon = loadDungeon('dungeon.json');
$player = new Player($dungeon->rooms[$dungeon->startRoom]);

// Простая маршрутизация для управления перемещением игрока
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $direction = $_POST['direction'];
    $nextRoom = $dungeon->rooms[$direction]; // Предполагаем, что передаётся индекс комнаты
    $player->move($nextRoom);
}

// Вывод результата
echo json_encode([
    'currentRoom' => $player->currentRoom,
    'score' => $player->score,
    'dungeon' => $dungeon,
]);

?>
