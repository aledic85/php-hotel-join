
<?php

  include "databaseInfo.php";

  class Prenotazione {

    private $idPrenotazioni;
    private $stanzaId;
    private $configurazioneID;
    private $updatedAt;

    function __construct($idPrenotazioni, $stanzaId, $configurazioneID, $updatedAt) {

      $this->idPrenotazioni = $idPrenotazioni;
      $this->stanzaId = $stanzaId;
      $this->configurazioneID = $configurazioneID;
      $this->updatedAt = $updatedAt;
    }

     function getPrenotazioneId() {

       return $this->idPrenotazioni;
     }

     function getConfigurazioneID() {

       return $this->configurazioneID;
     }

     static function getPrenotazioneByMonth($conn) {

      $sql = "SELECT *
              FROM prenotazioni
              WHERE month(updated_at) = 5
              ORDER BY updated_at ASC";

      $result = $conn->query($sql);

      $prenotazioni = [];

      if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {

          $prenotazioni[] = new Prenotazione(
                                $row["id"],
                                $row["stanza_id"],
                                $row["configurazione_id"],
                                $row["updated_at"]);
        }
      } else {

        echo "0 results";
      }
      return $prenotazioni;
    }

    function printPrenotazioni() {

      echo "Prenotazione "
            . $this->idPrenotazioni . ": ";
    }
  }

  class Ospite {

    private $name;
    private $lastname;

    function __construct($name, $lastname) {

      $this->name = $name;
      $this->lastname = $lastname;
    }

    static function getOspiteById($conn, $prenotazioneID) {


      $sql = "SELECT ospiti.name, ospiti.lastname from prenotazioni_has_ospiti
              JOIN ospiti
              ON prenotazioni_has_ospiti.ospite_id = ospiti.id
              WHERE prenotazione_id = $prenotazioneID";

      $result = $conn->query($sql);
      $ospite = [];

      if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {

          $ospite[] = new Ospite(
                                $row["name"],
                                $row["lastname"]);
        }
      } else {

          echo "0 results";
        }
      return $ospite;
    }

    function printNameLastname() {

      echo $this->name
           ."-"
           . $this->lastname
           . "<br>";
    }
  }

  class Stanza {

    private $roomNumber;
    private $floor;
    private $beds;

    function __construct($roomNumber, $floor, $beds) {

      $this->roomNumber = $roomNumber;
      $this->floor = $floor;
      $this->beds = $beds;
    }

    static function getStanzeInfo($conn, $prenotazioneID) {

      $sql = "SELECT stanze.room_number, stanze.floor, stanze.beds
              FROM prenotazioni
              JOIN stanze
              ON prenotazioni.stanza_id = stanze.id
              WHERE prenotazioni.id = $prenotazioneID";

      $result = $conn->query($sql);
      $stanze = [];

      if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {

          $stanze[] = new Stanza(
                                $row["room_number"],
                                $row["floor"],
                                $row["beds"]);
        }
      } else {

          echo "0 results";
        }
      return $stanze;

    }

    function printStanzeInfo() {

      echo "Numero stanza: "
           . $this->roomNumber . "<br>"
           . "Piano: "
           . $this->floor ."<br>"
           . "Letti: "
           . $this->beds . "<br>";
    }
  }

  class Configurazione {

    private $title;
    private $description;

    function __construct($title, $description) {

      $this->title = $title;
      $this->description = $description;
    }

    static function getConfigurazioneInfo($conn, $configurazioneID) {

      $sql = "SELECT configurazioni.title, configurazioni.description
              FROM prenotazioni
              JOIN configurazioni
              ON prenotazioni.configurazione_id = configurazioni.id
              WHERE configurazioni.id = $configurazioneID
              GROUP BY configurazioni.title";

      $result = $conn->query($sql);
      $configurazioni = [];

      if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {

          $configurazioni[] = new Configurazione(
                                $row["title"],
                                $row["description"]);
        }
      } else {

          echo "0 results";
        }

      return $configurazioni;
    }

    function printConfigurazioneInfo() {

      echo "Configurazione: "
            . $this->title . "<br>"
            . "Descrizione: "
            . $this->description . "<br>" . "<br>";
    }
  }

  $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_errno) {

      echo ("Connection failed: " . $conn->connect_error);
      return;
    }

  $prenotazioni = Prenotazione::getPrenotazioneByMonth($conn);

  foreach ($prenotazioni as $prenotazione) {

    $prenotazioneID = $prenotazione->getPrenotazioneId();
    $configurazioneID = $prenotazione->getConfigurazioneID();
    $printPrenotazione = $prenotazione->printPrenotazioni();
    $ospiti = Ospite::getOspiteById($conn, $prenotazioneID);
    $stanze = Stanza::getStanzeInfo($conn, $prenotazioneID);
    $configurazioni = Configurazione::getConfigurazioneInfo($conn, $configurazioneID);

    foreach ($ospiti as $ospite) {

      $printOspite = $ospite->printNameLastname();

      foreach ($stanze as $stanza) {

        $printStanzaInfo = $stanza->printStanzeInfo();
      }

      foreach ($configurazioni as $configurazione) {

        $printConfigurazioneInfo = $configurazione->printConfigurazioneInfo();
      }
    }

  }
  $conn->close();
 ?>
