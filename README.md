# Multiplayer Trivia Game API

A Laravel-based backend service for managing and playing trivia games. This API supports creating games, managing questions, and providing real-time gameplay for multiple players. It handles game rounds, user scores, leaderboards, and more.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
  - [Managing](#managing)
  - [Gameplay](#gameplay)
- [How Does It Work?](#how-does-it-work)
- [Installation](#installation)
  - [Requirements](#requirements)
  - [Setup](#setup)
- [API Documentation](#api-documentation)
- [Usage Guide](#usage-guide)
- [Technologies Used](#technologies-used)

## Overview

The Multiplayer Trivia Game API is designed to:

- Allow users to create and join trivia games.
- Provide tools to manage categories, questions, and game-specific settings.
- Enable real-time gameplay where players can fetch and answer questions.
- Track scores and display leaderboards for completed or in-progress games.
- Offer robust API endpoints for seamless integration with frontend clients.

### Managing

- **Categories:**
  - Create, update, and delete trivia categories.
- **Games:**
  - List Games (includes parameters for filtering)
  - Get details for a specific game
  - Create games with customizable settings like maximum players and question limits.
  - Delete games in that is pending.
- **Questions:**
  - Fetch random questions from an external API.
  - Submit a new questions to the game pool
  - Delete a question as long as its not used in a game
  - List all the Questions on a game (as long as you are part of the game)

### How Does It Work?

1. **Register and Login:**
  - Users must first register an account and log in to access the game features.
2. **Create a Game:**
  - Once logged in, users can create a game, specifying settings like the maximum number of players and the number of questions.
3. **Join a Game:**
  - Other users can view a list of available games and join any pending game.
  - You must also join to participate in your own game
4. **Start the Game:**
  - The game creator (owner) starts the game, locking in the player list.
  - Questions are randomly assigned to the game from the pool.
5. **Answer Questions:**
  - Players fetch one question at a time and submit their answers.
  - Immediate feedback is provided on whether the answer is correct or not.
6. **Game Completion:**
  - When all questions in the game have been answered, the game is marked as completed automatically.
  - Scores are calculated, and the leaderboard is displayed.
7. **View Scores:**
  - Players can view their scores and the leaderboard during or after the game.
  - Completed games also show the correct answers for review.

You MUST fetch a question first before submitting an answer. This "assigns" the question to you. If you have a NOT submitted an answer within 5 minutes of fetching, it will be available for other players. This mean that you will not complete any of the same questions as other players.

#### How to WIN!!

The winner is the person with the most correctly answered questions. If there is a tie, the it it based on your accuracy percentage. So be fast, because theres a limited amount of questions in each game. 


## Installation

### Requirements

- PHP 8.2 or higher
- Composer
- Laravel 11
- SqLite (or any other database supported by Laravel)

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/frankfava/trivia-game-api.git
   cd trivia-game-api
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up the environment file:**
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with your database credentials and other configurations.

4. **Create an APP_KEY:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations and seed the database:**
   ```bash
   php artisan migrate
   ```
   - You may optional run the database seeder. Not recommended if you are planning to Fetch trivia questions from the external API.
        ```bash
        php artisan db:seed
        ```

6. **Setup Passport Keys:**
   ```bash
   php artisan passport:keys
   ```

7. **Create a Personal Access Client for Passport:**
   ```bash
   php artisan passport:client --personal -n
   ```
   - Add the values to the env file.
   ```
    PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
    PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=
    ```

8. **Fetch trivia questions:**
   - Run the artisan command to populate the questions table:
        ```bash
        php artisan fetch:trivia-questions
        ```
   - By default, this command fetches 1,000 random questions from an external trivia API.

9. **Start the server or setup with Laravel Herd:**
   ```bash
   php artisan serve
   ```
   - The API will be accessible at `http://127.0.0.1:8000` or at your designated HERD url.

The project is now ready for use!



## Testing

Run the test suite using PHPUnit:

```bash
php artisan test
```


## API Documentation

### Overview

This API provides endpoints to manage and play trivia games. Below are the categorized endpoints:

#### Authentication

- **Register:** Register a new Email
  - `POST /api/register`
  - Parameters: `name`, `email`, `password`, `password_confirmation`
  - Use the `token` from the response in the `Authorization` header for all subsequent API calls
  ```bash
  curl -X POST [_BASE_URL_]/api/register \
       -H "Content-Type: application/json" \
       -H "Accept-Type: application/json" \
       -d '{"name": "John Doe", "email": "john@example.com", "password": "password", "password_confirmation": "password"}'
  ```

- **Login:** Login with an existing user
  - `POST /api/login`
  - Parameters: `email`, `password`
  - Use the `token` from the response in the `Authorization` header for all subsequent API calls
  ```bash
  curl -X POST [_BASE_URL_]/api/login \
       -H "Content-Type: application/json" \
       -H "Accept-Type: application/json" \
       -d '{"email": "john@example.com", "password": "password"}'
  ```

#### General

- **PING:** Test the API connection
  - `GET /api/ping`

- **Current User:** Get authenticated user details
  - `GET /api/user`

#### Game Management

- **Create Game:**
  - `POST /api/games`
  - Parameters:
    - `name` : Required. Name of Game.
    - `max_players` : Number of players allowed. Min: 1, Max : 50. Default : 5
    - `number_of_questions` : Number of Question to add to game. Min: 1, Max : 50. Default : 20
    - `show_correct_answers` : Show the correct answer after any play submits an answer. Default : false
  ```bash
  curl -X POST [_BASE_URL_]/api/games \
      -H "Authorization: Bearer your-access-token" \
      -H "Content-Type: application/json" \
      -d '{"name": "Trivia Night", "max_players": 5, "question_count": 10}'
  ```

- **List Games:** List all games  
  - `GET /api/games`
  - Parameters:
    - `is_owner` : Optional. Setting to true will only show games where authenticated user is the owner.
    - `is_player` : Optional. Setting to true will only show games where authenticated user is a player.
    - `can_join` : Optional. Setting to true will only show games where the authenticated user can join.
    - `is_owner` : Optional. Setting to true will only show game where authenticated user is the owner.
    - `per_page` : Number of games to show per_page. Default : 10
    - `page` : Page number to show. Default : 1

- **Get Game:** Fetch details of a specific game
  - `GET /api/games/{game}`

- **Join Game:** Join a game  
  - `POST /api/games/{game}/join`
  - Parameters: `None`
  - Can only Join Pending Games
  - Can only join until max_players is reached.

- **Start Game:** Start a game
  - `POST /api/games/{game}/start`
  - Parameters: `None`
  - Only Pending games can be start.
  - The game must have questions added to it, to start

- **Cancel Game:** Cancel a game
  - `DELETE /api/games/{game}/cancel`
  - Parameters: `None`
  - Only Pending or In Progress games can be completed.

- **Resume Game:** Resume a game
  - `DELETE /api/games/{game}/resume`
  - Parameters: `None`
  - Only Cancelled games can be resumed.
  - If the game has questions, it will be changed to In Progress, otherwise to Pending.

- **Delete Game:** Delete a specific gam
  - `DELETE /api/games/{game}`

- **List Questions for a Game:** List all questions attached to a game
  - `GET /api/games/{game}/questions`
  - Parameters:
    - `per_page` : Number of items to show per_page. Default : 10
    - `page` : Page number to show. Default : 1



#### Question Submission

- **Fetch Question:** Fetch the next question in the game  
  - `GET /api/games/{game}/questions/next`

- **Submit Answer:** Submit an answer for a question
  - `POST /api/games/{game}/questions/{question}/answer`
  - Parameters: 
    - `answer`: Must exactly match the correct_answer on the question to count as correct. 


#### Leaderboard and Scoring

- **Game Leaderboard:** Get the leaderboard for a specific game 
  - `GET /api/games/{game}/leaderboard`
    ```bash
    curl -X GET [_BASE_URL_]/api/games/1/leaderboard \
        -H "Authorization: Bearer your-access-token"
    ```

- **My Score:** Get the authenticated user's score for this game
  - `GET /api/games/{game}/myscore`
    ```bash
    curl -X GET [_BASE_URL_]/api/games/1/myscore \
        -H "Authorization: Bearer your-access-token"
    ```

### Question Management

- **Create Question:** Add a new question 
  - `POST /api/questions`
  - Parameters:
    - `type` : Required. Options: multiple | boolean
    - `difficulty` : Options: easy| medium | hard. Default. Medium
    - `category` : Required. Category Name
    - `question` : Required. The Question.
    - `correct_answer` : Correct answer as a string.
    - `incorrect_answers` : Array of incorrect options. Must have at least one.

- **Delete Question:** Delete a specific question  
  - `DELETE /api/questions/{question}`

### Category Management

- **List Categories:** List all categories
  - `GET /api/categories`
  - Parameters:
    - `per_page` : Number of items to show per_page. Default : 10
    - `page` : Page number to show. Default : 1

- **Get Category:** Get details of a specific category
  - `GET /api/categories/{category}`

- **Delete Category:** Delete a specific category  
  - `DELETE /api/categories/{category}`



## Developer Notes

- Built using Laravel 11, to display basic Laravel skills.
- Uses SqLite by default
- Uses Laravel Passport for authentication
- Can fetch questions from an External API using an artisan command
- Different Laravel Features used
    - Eloquent Scopes and Relationships
    - PHP Unit tests for all routes
    - Form Requests
    - Policies for controller authorisation
    - JSON Resources of Responses
    - Repsonses built using Responsable Contract
    - Enum Classes
    - Event Dispatch and Listener when game is completed