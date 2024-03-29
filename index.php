<?php
// Database connection details
$host = 'localhost';
$database = 'tictactoe';
$user = 'root';
$password = '';

// Create database connection
$conn = new mysqli($host, $user, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Constants for game over status
define('GAME_NOT_OVER', 0);
define('GAME_OVER', 1);

// Initialize the game state if it doesn't exist
$gameState = retrieveGameState($conn);
if (!$gameState) {
    initializeGameState($conn);
    $gameState = retrieveGameState($conn);
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if(isset($_POST['reset'])){
        resetGame($conn);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    elseif (isset($_POST['row']) && isset($_POST['col'])) {
        
        $row = (int)$_POST['row'];
        $col = (int)$_POST['col'];
        $message = processMove($gameState['board_state'], $gameState['current_player'], $row, $col, $conn);
        $gameState = retrieveGameState($conn);
    } 
}

// Display the game board
function displayBoard($board) {
    echo '<table class="table table-bordered text-center">';
    for ($i = 0; $i < 3; $i++) {
        echo '<tr>';
        for ($j = 0; $j < 3; $j++) {
            echo '<td class="cell" id="' . $i . '-' . $j . '">
                ' . $board[$i][$j] . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

// Check if the game is a draw
function isDraw($board) {
    foreach ($board as $row) {
        foreach ($row as $cell) {
            if ($cell === ' ') {
                return false;
            }
        }
    }
    return true;
}

// Process player move
function processMove($board, $currentPlayer, $row, $col, $conn) {
    if ($board[$row][$col] !== ' ') {
        return "Invalid move!";
    }

    $board[$row][$col] = $currentPlayer;
    $gameState = retrieveGameState($conn);
    $gameOver = $gameState['game_over'];

    if (checkWinner($board, $currentPlayer)) {
        $winner = $currentPlayer;
        if ($gameOver == GAME_NOT_OVER) {
            $gameOver = GAME_OVER;
            updateGameState($board, $currentPlayer, $gameOver, $conn);
            storeGameResult($board, $winner, $gameOver, $conn);   
        }
        return 'Player ' . $winner . ' wins!';
    } elseif (isDraw($board)) {
        if ($gameOver == GAME_NOT_OVER) {
            $gameOver = GAME_OVER;
            updateGameState($board, $currentPlayer, $gameOver, $conn);
            storeGameResult($board, 'Draw', $gameOver, $conn);   
        }
        return 'It\'s a draw!';
    } else {
        $currentPlayer = ($currentPlayer === 'X') ? 'O' : 'X';
        updateGameState($board, $currentPlayer, $gameOver, $conn);
        return 'Player ' . $currentPlayer . '\'s turn';
    }
}

// Initialize game state
function initializeGameState($conn) {
    $board = array_fill(0, 3, array_fill(0, 3, ' '));
    $currentPlayer = 'X';
    $gameOver = GAME_NOT_OVER;
    $boardState = json_encode($board);
    $sql = "INSERT INTO game_state (board_state, current_player, game_over) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $boardState, $currentPlayer, $gameOver);
    $stmt->execute();
    $stmt->close();
}

// Retrieve game state from the database
function retrieveGameState($conn) {
    $sql = "SELECT board_state, current_player, game_over FROM game_state ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return array('board_state' => json_decode($row['board_state'], true), 'current_player' => $row['current_player'], 'game_over' => $row['game_over']);
    } else {
        return null;
    }
}

// Update game state in the database
function updateGameState($board, $currentPlayer, $gameOver, $conn) {
    $boardState = json_encode($board);
    $sql = "UPDATE game_state SET board_state=?, current_player=?, game_over=? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $boardState, $currentPlayer, $gameOver);
    $stmt->execute();
    $stmt->close();
}

// Store game result in the database
function storeGameResult($board, $winner, $gameOver, $conn) {
    $boardState = json_encode($board);
    $outcome = ($winner === 'Draw') ? 'Draw' : 'Winner: Player ' . $winner;
    $sql = "INSERT INTO game_results (board_state, outcome) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $boardState, $outcome);
    $stmt->execute();
    $stmt->close();
}

//Check for winner
function checkWinner($board, $player) {
    // Check rows
    for ($i = 0; $i < 3; $i++) {
        if ($board[$i][0] === $player && $board[$i][1] === $player && $board[$i][2] === $player) {
            return true;
        }
    }
    // Check columns
    for ($j = 0; $j < 3; $j++) {
        if ($board[0][$j] === $player && $board[1][$j] === $player && $board[2][$j] === $player) {
            return true;
        }
    }
    // Check diagonals
    if (($board[0][0] === $player && $board[1][1] === $player && $board[2][2] === $player) ||
        ($board[0][2] === $player && $board[1][1] === $player && $board[2][0] === $player)) {
        return true;
    }
    return false;
}

function resetGame($conn) {
    $sql = "DELETE FROM game_state";
    if ($conn->query($sql) !== TRUE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $sql = "DELETE FROM game_results";
    if ($conn->query($sql) !== TRUE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    initializeGameState($conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tic Tac Toe</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
  <style>
    .cell {
      width: 100px;
      height: 100px;
      font-size: 3em;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <h1 class="text-center mb-4">Tic Tac Toe</h1>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="alert alert-info">
        <p id="playerTurn"><?php echo $message; ?></p>
      </div>
      <form method="post">
        <input type="hidden" id="row" name="row">
        <input type="hidden" id="col" name="col">
        <?php displayBoard($gameState['board_state']); ?>
            <?php if ($gameState['game_over']) { ?>
                <button type="submit" name="reset" value='reset' class="btn btn-primary btn-block">Reset Game</button>
            <?php }?>
      </form>
    </div>
  </div>
</div>

<script>

document.querySelectorAll('.cell').forEach(cell => {
    cell.addEventListener('click', function() {
        var id = this.id.split('-');
        document.getElementById('row').value = id[0];
        document.getElementById('col').value = id[1];
        this.closest('form').submit();
    });
});
</script>

</body>
</html>