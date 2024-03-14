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

# Screenshots

![Screenshot from 2024-03-14 15-14-50](https://github.com/ashwini-jadhav8796/tic-tac-toe/assets/159520532/aebe065f-a5a1-41e2-a80d-884a5227baff)
![Screenshot from 2024-03-14 15-14-59](https://github.com/ashwini-jadhav8796/tic-tac-toe/assets/159520532/051021fd-815b-4165-b983-cbe724d5ab72)
![Screenshot from 2024-03-14 15-15-22](https://github.com/ashwini-jadhav8796/tic-tac-toe/assets/159520532/6dc3ff38-2377-450e-9d50-e4e747371407)
