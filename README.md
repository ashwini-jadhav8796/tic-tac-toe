# tic-tac-toe

# Requirements
Sql queries to create tables:

CREATE TABLE game_state (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_state TEXT NOT NULL,
    current_player CHAR(1) NOT NULL,
    game_over BOOLEAN NOT NULL
);

CREATE TABLE game_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_state TEXT NOT NULL,
    outcome VARCHAR(255) NOT NULL
);
